#!/usr/bin/env bash
# Nightly backup: consistent MySQL dump + uploaded media + shared .env, with local retention and
# optional off-site copy via rclone. Run by deploy/systemd/htfarms-backup.timer as the deploy user.
#
# Database credentials are read from ~/.my.cnf of the user running this script (never from argv).
set -Eeuo pipefail

BASE="${DEPLOY_PATH:-/var/www/htfarms}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/htfarms}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"
DB_NAME="${DB_NAME:-htfarms}"
RCLONE_REMOTE="${BACKUP_RCLONE_REMOTE:-}" # e.g. "b2:htfarms-backups" (optional)

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
TARGET="$BACKUP_DIR/$STAMP"
umask 077
mkdir -p "$TARGET"

echo "[backup] database $DB_NAME"
mysqldump --single-transaction --quick --routines --triggers --no-tablespaces "$DB_NAME" | gzip -9 > "$TARGET/database.sql.gz"

echo "[backup] uploaded media"
tar -czf "$TARGET/media.tar.gz" -C "$BASE/shared/storage/app" public

echo "[backup] environment file"
cp "$BASE/shared/.env" "$TARGET/env"

( cd "$TARGET" && sha256sum database.sql.gz media.tar.gz env > SHA256SUMS )

if [[ -n "$RCLONE_REMOTE" ]]; then
    echo "[backup] copying off-site to $RCLONE_REMOTE/$STAMP"
    rclone copy "$TARGET" "$RCLONE_REMOTE/$STAMP" --quiet
fi

echo "[backup] pruning local backups older than $RETENTION_DAYS days"
find "$BACKUP_DIR" -mindepth 1 -maxdepth 1 -type d -mtime +"$RETENTION_DAYS" -exec rm -rf {} +

echo "[backup] done: $TARGET"
