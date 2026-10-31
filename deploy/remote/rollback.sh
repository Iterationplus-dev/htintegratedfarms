#!/usr/bin/env bash
# Point `current` at an earlier release (code only — database migrations are NOT reversed).
#
#   rollback.sh                 # previous release
#   rollback.sh <release-name>  # specific release, e.g. 20261030120000-abc1234def56
#   rollback.sh --list          # show available releases
set -Eeuo pipefail

BASE="${DEPLOY_PATH:-/var/www/htfarms}"
PHP_BIN="${PHP_BIN:-php8.5}"
FPM_SERVICE="${PHP_FPM_SERVICE:-php8.5-fpm}"
HEALTH_URL="${HEALTH_URL:-}"
TARGET="${1:-}"

log() { printf '[rollback] %s\n' "$*"; }

exec 9>"$BASE/.deploy.lock"
flock -n 9 || { log "a deployment is in progress"; exit 1; }

CURRENT="$(readlink -f "$BASE/current")"
mapfile -t RELEASES < <(ls -1dt "$BASE"/releases/*/ | sed 's:/$::')

if [[ "$TARGET" == "--list" ]]; then
    for r in "${RELEASES[@]}"; do
        [[ "$r" == "$CURRENT" ]] && echo "* $(basename "$r") (current)" || echo "  $(basename "$r")"
    done
    exit 0
fi

if [[ -z "$TARGET" ]]; then
    for i in "${!RELEASES[@]}"; do
        if [[ "${RELEASES[$i]}" == "$CURRENT" ]]; then
            TARGET="$(basename "${RELEASES[$((i + 1))]:-}")"
            break
        fi
    done
fi

[[ "$TARGET" =~ ^[0-9]{14}-[0-9a-f]{7,40}$ ]] || { log "no valid previous release found (use --list)"; exit 1; }
DIR="$BASE/releases/$TARGET"
[[ -d "$DIR" ]] || { log "release $TARGET does not exist"; exit 1; }
[[ "$DIR" != "$CURRENT" ]] || { log "$TARGET is already current"; exit 0; }

log "switching current -> $TARGET"
ln -sfn "$DIR" "$BASE/current.next"
mv -Tf "$BASE/current.next" "$BASE/current"
sudo -n /usr/bin/systemctl reload "$FPM_SERVICE"
"$PHP_BIN" "$BASE/current/artisan" queue:restart --no-interaction

if [[ -n "$HEALTH_URL" ]]; then
    curl -fsS --retry 5 --retry-delay 3 --retry-all-errors --max-time 10 "$HEALTH_URL" >/dev/null \
        || { log "WARNING: health check failed after rollback"; exit 1; }
fi

log "rolled back to $TARGET (from $(basename "$CURRENT")). Database schema unchanged."
