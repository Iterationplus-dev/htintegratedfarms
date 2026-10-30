#!/usr/bin/env bash
# Activates a release on the VPS. Run as the deploy user (called by .github/workflows/deploy.yml).
#
#   deploy-release.sh <git-sha> <path-to-release-archive>
#
# Layout under $DEPLOY_PATH (default /var/www/htfarms):
#   releases/<UTC timestamp>-<sha12>/   immutable code, one per deploy
#   shared/.env                          production environment (never in a release)
#   shared/storage/                      logs, cache, sessions, uploaded media (persists across releases)
#   current -> releases/<…>              switched atomically (rename of a symlink)
#   bin/                                 helper scripts (rollback.sh) refreshed on each deploy
#
# Migrations run BEFORE the switch, while the previous release is still serving traffic, so they
# must be backward compatible (expand/contract; see docs/DEPLOYMENT.md). If the post-switch health
# check fails, `current` is pointed back at the previous release. Migrations are not reversed.
set -Eeuo pipefail
umask 002 # shared storage is group-writable by www-data (PHP-FPM, queue workers)

SHA="${1:?usage: deploy-release.sh <sha> <archive>}"
ARCHIVE="${2:?usage: deploy-release.sh <sha> <archive>}"
BASE="${DEPLOY_PATH:-/var/www/htfarms}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-true}"
PHP_BIN="${PHP_BIN:-php}"
FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
HEALTH_URL="${HEALTH_URL:-}"

log() { printf '[deploy %s] %s\n' "$(date -u +%H:%M:%S)" "$*"; }
fail() { log "ERROR: $*"; exit 1; }

[[ "$SHA" =~ ^[0-9a-f]{40}$ ]] || fail "invalid commit SHA"
[[ -f "$ARCHIVE" ]] || fail "archive not found: $ARCHIVE"
[[ -f "$BASE/shared/.env" ]] || fail "missing $BASE/shared/.env (see docs/DEPLOYMENT.md)"
grep -Eq '^APP_KEY=base64:.{40,}' "$BASE/shared/.env" || fail "APP_KEY is not set in shared/.env"
grep -Eq '^APP_DEBUG=(false|0)\s*$' "$BASE/shared/.env" || fail "APP_DEBUG must be false in production/staging"

# Serialise deploys on the host too (GitHub concurrency already prevents overlaps per environment).
exec 9>"$BASE/.deploy.lock"
flock -n 9 || fail "another deployment is in progress"

RELEASE="$(date -u +%Y%m%d%H%M%S)-${SHA:0:12}"
DIR="$BASE/releases/$RELEASE"
PREVIOUS="$(readlink -f "$BASE/current" 2>/dev/null || true)"

log "extracting $RELEASE"
mkdir -p "$DIR" "$BASE/bin" "$BASE/shared/storage"
tar -xzf "$ARCHIVE" -C "$DIR"
[[ "$(cat "$DIR/REVISION")" == "$SHA" ]] || fail "REVISION in archive does not match $SHA"

cleanup_failed_release() {
    if [[ "$(readlink -f "$BASE/current" 2>/dev/null)" != "$DIR" ]]; then
        log "removing unfinished release $RELEASE"
        rm -rf "$DIR"
    fi
}
trap cleanup_failed_release ERR

log "linking shared .env and storage"
for sub in app/public framework/cache/data framework/sessions framework/views logs; do
    mkdir -p "$BASE/shared/storage/$sub"
done
rm -rf "$DIR/storage"
ln -sfn "$BASE/shared/storage" "$DIR/storage"
ln -sfn "$BASE/shared/.env" "$DIR/.env"
ln -sfn "$BASE/shared/storage/app/public" "$DIR/public/storage"
mkdir -p "$DIR/bootstrap/cache"

cd "$DIR"

if [[ "$RUN_MIGRATIONS" == "true" ]]; then
    log "running migrations (backward-compatible only)"
    "$PHP_BIN" artisan migrate --force --isolated --no-interaction
fi

log "caching config, routes, events, views, Filament components"
"$PHP_BIN" artisan optimize --no-interaction
"$PHP_BIN" artisan filament:optimize --no-interaction

switch_to() {
    ln -sfn "$1" "$BASE/current.next"
    mv -Tf "$BASE/current.next" "$BASE/current"
    # Restricted sudo rule: deploy may only reload PHP-FPM (deploy/sudoers/htfarms-deploy).
    sudo -n /usr/bin/systemctl reload "$FPM_SERVICE"
    "$PHP_BIN" "$BASE/current/artisan" queue:restart --no-interaction
}

log "switching current -> $RELEASE"
switch_to "$DIR"

healthy=true
if [[ -n "$HEALTH_URL" ]]; then
    healthy=false
    for attempt in $(seq 1 10); do
        if curl -fsS --max-time 10 "$HEALTH_URL" >/dev/null; then healthy=true; break; fi
        log "health check attempt $attempt failed; retrying"
        sleep 3
    done
fi

if [[ "$healthy" != true ]]; then
    if [[ -n "$PREVIOUS" && -d "$PREVIOUS" ]]; then
        log "health check failed; restoring $(basename "$PREVIOUS")"
        switch_to "$PREVIOUS"
    fi
    trap - ERR
    fail "release $RELEASE failed its health check (database changes, if any, were NOT reverted)"
fi
trap - ERR

log "installing helper scripts"
install -m 0755 "$DIR/deploy/remote/rollback.sh" "$BASE/bin/rollback.sh"
install -m 0755 "$DIR/deploy/remote/backup.sh" "$BASE/bin/backup.sh"

log "pruning old releases (keeping $KEEP_RELEASES)"
CURRENT_REAL="$(readlink -f "$BASE/current")"
ls -1dt "$BASE"/releases/*/ 2>/dev/null | sed 's:/$::' | tail -n +"$((KEEP_RELEASES + 1))" | while read -r old; do
    [[ "$old" == "$CURRENT_REAL" ]] || rm -rf "$old"
done
rm -f "$ARCHIVE" "$BASE/uploads/deploy-release.sh"

log "deployed $RELEASE"
