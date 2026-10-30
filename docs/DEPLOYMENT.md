# Deployment and operations

Target: one Ubuntu 24.04 LTS VPS (2 vCPU / 4 GB RAM is comfortable) running Nginx, PHP 8.4-FPM, MySQL 8.4,
Redis, Supervisor and systemd timers. Staging can share the server under a separate path, database, PHP-FPM
pool and Nginx vhost.

```
/var/www/htfarms/
├── releases/20261030120000-3f2a9c1d0b4e/   immutable release (code + vendor + public/build)
├── current -> releases/20261030120000-…    switched atomically on each deploy
├── shared/.env                             production configuration (never in a release)
├── shared/storage/                         logs, cache, sessions, uploaded media
├── bin/rollback.sh, bin/backup.sh          helpers (refreshed by every deploy)
└── uploads/                                transient upload area for release archives
```

## 1. Server setup (once per server)

From a checkout of this repository on the server:

```bash
sudo DOMAIN=htintegratedfarms.com ADMIN_EMAIL=ops@htintegratedfarms.com bash deploy/provision/provision-ubuntu.sh
```

The script installs the packages (PHP 8.4 extensions: fpm, cli, mysql, redis, mbstring, xml, curl, zip,
gd, intl, bcmath, opcache, sqlite3), enables the firewall (SSH and HTTP/S only), creates the `deploy` user
(SSH key only, member of `www-data`), the directory layout with group-writable `shared/storage`, the PHP-FPM
pool (`deploy/php/`), the restricted sudoers rule (`deploy/sudoers/` lets the deploy user run only
`systemctl reload php8.4-fpm`), Supervisor workers, the scheduler and backup timers, logrotate, and the
Nginx vhost. It is safe to re-run. Then complete these steps by hand:

1. **Deploy key**: create an ed25519 key pair for GitHub
   (`ssh-keygen -t ed25519 -f htfarms-deploy -C github-deploy`). Append the `.pub` to
   `/home/deploy/.ssh/authorized_keys`, and store the private key as the `DEPLOY_SSH_KEY` environment secret.
2. **Database**:
   ```sql
   CREATE DATABASE htfarms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'htfarms'@'localhost' IDENTIFIED BY '<strong password>';
   GRANT ALL PRIVILEGES ON htfarms.* TO 'htfarms'@'localhost';
   ```
   For backups, create `/home/deploy/.my.cnf` (mode 600) with a `[mysqldump]` section holding the same
   user and password.
3. **Environment file**: create `/var/www/htfarms/shared/.env` from the skeleton in
   [ENVIRONMENT.md](ENVIRONMENT.md). Generate `APP_KEY` with `php artisan key:generate --show` on any
   checkout. `chown deploy:www-data .env && chmod 640 .env`.
4. **TLS**: issue the certificate with the HTTP-01 webroot (`/var/www/letsencrypt`), enable the vhost, then
   `nginx -t && systemctl reload nginx`. Certbot's systemd timer renews it automatically.
5. **First deploy**: run the GitHub **Deploy** workflow (section 3). Then:
   `sudo supervisorctl reread && sudo supervisorctl update && sudo systemctl start htfarms-scheduler.timer htfarms-backup.timer`.
6. **Initial content and first admin** (production does not seed dev accounts):
   ```bash
   cd /var/www/htfarms/current
   php artisan db:seed --class=Database\\Seeders\\ContentSeeder --force   # handoff content; idempotent
   php artisan app:create-user                                            # prompts for name, role, password
   ```
7. **Mail**: configure SPF and DKIM for the sending domain. Send yourself a test contact enquiry.

## 2. GitHub setup

See [ENVIRONMENT.md § GitHub configuration](ENVIRONMENT.md#github-configuration): the `staging` and
`production` environments, `DEPLOY_*` and `APP_URL` variables, `DEPLOY_KNOWN_HOSTS`, and the
`DEPLOY_SSH_KEY` secret. Protect `main` (require a PR and passing CI).

## 3. How a deploy works

`.github/workflows/deploy.yml` runs on every push to `staging` or `main`, or by hand (**Actions → Deploy →
Run workflow**, choosing the environment and whether to run migrations).

1. **Validate**: calls `ci.yml` (Pint, Larastan, `tsc`, build, ShellCheck, PHPUnit on MySQL, Playwright).
2. **Build**: `composer install --no-dev --optimize-autoloader` from the lockfile, `npm ci && npm run build`,
   `filament:assets`, then writes `REVISION` and packages `release-<sha>.tar.gz` plus a SHA-256 checksum
   (artifact kept for 30 days).
3. **Deploy** (GitHub Environment; a concurrency group per environment means deploys never overlap and are
   never cancelled midway):
   - Verifies the checksum and configures SSH with `StrictHostKeyChecking yes` against `DEPLOY_KNOWN_HOSTS`.
   - `scp`s the archive and `deploy/remote/deploy-release.sh`, then runs the script, which:
     - takes a host-level `flock` and checks that `.env` has an `APP_KEY` and `APP_DEBUG=false`;
     - extracts to `releases/<timestamp>-<sha12>/` and checks `REVISION`;
     - links `shared/.env`, `shared/storage` and `public/storage`;
     - runs `php artisan migrate --force --isolated` (if enabled) **while the previous release is still live**;
     - runs `php artisan optimize` and `filament:optimize`;
     - switches `current` atomically (`ln -sfn` + `mv -T`), reloads PHP-FPM and runs `queue:restart`;
     - checks `APP_URL/health`; **if it fails, `current` is switched back** and the job fails;
     - installs `bin/rollback.sh` and `bin/backup.sh`, then prunes to the newest 5 releases (`KEEP_RELEASES`).
   - Finally checks `APP_URL/health` from the runner.

`/health` returns `{"status":"ok","checks":{"database":"ok","cache":"ok"}}`, or HTTP 503 if anything fails.
Laravel's `/up` is also available as a liveness check.

## 4. Migration strategy (expand → migrate → contract)

Migrations run before traffic moves to the new code, and rollbacks switch code only. So **every migration
must work with both the previous and the new release**:

- **Allowed in one deploy**: new tables; new nullable columns or columns with defaults; new indexes (use
  online DDL for big tables); new enum values that old code simply ignores.
- **Needs two deploys**: renaming or dropping a column or table, making a column `NOT NULL`, or changing a
  column's type. Deploy 1 adds the new structure and code that writes to both (or stops reading the old one).
  Deploy 2, once deploy 1 is stable, removes the old structure.
- Never edit a migration that has already run in production. Write `down()` methods, but **do not rely on
  them in production**.
- For large data backfills, use a queued job or an artisan command, not the migration.
- Run `--isolated` (already in the script) so only one migrator runs at a time.

To deploy code without migrating, use **Run workflow** and untick *Run database migrations*.

## 5. Rollback

**Actions → Rollback → Run workflow**, choosing the environment and optionally a release name (empty means
the previous release). On the server:

```bash
/var/www/htfarms/bin/rollback.sh --list
/var/www/htfarms/bin/rollback.sh                      # previous release
/var/www/htfarms/bin/rollback.sh 20261030120000-3f2a9c1d0b4e
```

**Limitations:**
- Rollback switches **code only**. Schema changes and data written by the newer release stay. This is safe
  under the expand/contract rule above; if a release broke that rule, restore the database from backup
  (section 6) or write a forward-fix migration.
- You can only roll back to releases still on disk (the newest 5). Older commits must be redeployed: revert on
  `main`, or run **Deploy** on a branch at that commit.
- Config/route caches belong to each release, so rolling back restores the matching caches. Shared
  `.env` changes are **not** rolled back.
- Queued jobs serialised by the newer release may fail on older code. Check `php artisan queue:failed`
  after a rollback.

## 6. Backups and restore

`htfarms-backup.timer` runs `bin/backup.sh` nightly at 01:30. It writes `/var/backups/htfarms/<UTC stamp>/`
containing `database.sql.gz` (consistent `mysqldump --single-transaction`), `media.tar.gz` (uploaded images),
`env` and `SHA256SUMS`, keeps 14 days locally, and optionally copies off-site with rclone
(`BACKUP_RCLONE_REMOTE` in the service unit). **Configure the off-site copy**: a backup on the same disk does
not survive losing the server.

Restore (maintenance mode on, to stop writes):

```bash
cd /var/www/htfarms/current && php artisan down
B=/var/backups/htfarms/<stamp>; (cd $B && sha256sum -c SHA256SUMS)
gunzip -c $B/database.sql.gz | mysql htfarms
tar -xzf $B/media.tar.gz -C /var/www/htfarms/shared/storage/app
php artisan optimize:clear && php artisan optimize && php artisan up
```

Test a restore on staging at least once a quarter.

## 7. Logs and monitoring

| What | Where |
|---|---|
| Application | `shared/storage/logs/laravel-YYYY-MM-DD.log` (daily, 14 days) |
| Queue workers | `shared/storage/logs/worker.log` (logrotate weekly) |
| PHP-FPM pool | `/var/log/php8.4-fpm-htfarms.log` |
| Nginx | `/var/log/nginx/htfarms.{access,error}.log` |
| Scheduler heartbeat | `shared/storage/logs/scheduler-heartbeat` (updated every 5 min) |
| Health | `GET /health` (point an uptime monitor at it) |

Scheduled tasks: `model:prune` (expired quote drafts), `queue:prune-failed`, `queue:prune-batches`, weekly
`media-library:clean`.

## 8. Troubleshooting

| Symptom | Check |
|---|---|
| Deploy fails at "Configure SSH" | `DEPLOY_KNOWN_HOSTS` and `DEPLOY_SSH_KEY` set on the **environment** (not the repository)? |
| `Host key verification failed` | The server's host key changed; re-verify and update `DEPLOY_KNOWN_HOSTS`. Do not disable checking. |
| `sudo: a password is required` | `/etc/sudoers.d/htfarms-deploy` missing, or the PHP version in the rule doesn't match `PHP_FPM_SERVICE`. |
| Deploy rolled back after "health check failed" | `curl -v https://<host>/health`, `tail shared/storage/logs/laravel-*.log`. The cause is usually `.env` (DB/Redis credentials) or a migration. |
| 500 with a blank page | `php artisan about` in `current`; check permissions on `shared/storage` (group `www-data`, setgid, mode 2775). |
| Images show but new uploads have no WebP sizes | The queue worker isn't running: `sudo supervisorctl status`. `php artisan media-library:regenerate` rebuilds them. |
| Emails not arriving | `php artisan queue:failed`; check SMTP credentials and the mail logs; check SPF/DKIM. |
| Enquiry forms say "Too many submissions" for everyone | Behind a proxy/CDN without `TRUSTED_PROXIES`, every client shares the proxy's IP. |
| Images missing (404 under `/storage`) | `releases/<sha>/public/storage` must be a symlink to `shared/storage/app/public` (created by `deploy-release.sh`). |
| Admin login loops | `SESSION_SECURE_COOKIE=true` needs HTTPS. Check the system clock. |
| Stale content after a settings change | Settings and stats caches are flushed on save. Otherwise run `php artisan cache:clear`. |
