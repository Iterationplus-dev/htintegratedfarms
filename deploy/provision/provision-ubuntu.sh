#!/usr/bin/env bash
# One-time VPS provisioning for Ubuntu 24.04 LTS. Run as root from a checkout of this repository:
#
#   sudo DOMAIN=htintegratedfarms.com ADMIN_EMAIL=ops@htintegratedfarms.com bash deploy/provision/provision-ubuntu.sh
#
# Idempotent: safe to re-run. It installs packages and config templates but does NOT create the
# production .env, database password or TLS certificate; those steps are printed at the end and
# described in docs/DEPLOYMENT.md.
set -Eeuo pipefail

DOMAIN="${DOMAIN:?set DOMAIN, e.g. htintegratedfarms.com}"
ADMIN_EMAIL="${ADMIN_EMAIL:?set ADMIN_EMAIL for the TLS certificate}"
APP_PATH="${APP_PATH:-/var/www/htfarms}"
DEPLOY_USER="${DEPLOY_USER:-deploy}"
PHP_VERSION="8.4"
REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

[[ $EUID -eq 0 ]] || { echo "Run as root"; exit 1; }

echo "== Packages"
apt-get update
apt-get install -y software-properties-common ca-certificates curl unzip gnupg
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y nginx mysql-server redis-server supervisor certbot python3-certbot-nginx ufw rsync \
    "php${PHP_VERSION}-fpm" "php${PHP_VERSION}-cli" "php${PHP_VERSION}-mysql" "php${PHP_VERSION}-redis" \
    "php${PHP_VERSION}-mbstring" "php${PHP_VERSION}-xml" "php${PHP_VERSION}-curl" "php${PHP_VERSION}-zip" \
    "php${PHP_VERSION}-gd" "php${PHP_VERSION}-intl" "php${PHP_VERSION}-bcmath" "php${PHP_VERSION}-opcache" \
    "php${PHP_VERSION}-sqlite3"

echo "== Firewall"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "== Deploy user (SSH key only, no password, no general sudo)"
id "$DEPLOY_USER" >/dev/null 2>&1 || adduser --disabled-password --gecos "" "$DEPLOY_USER"
usermod -aG www-data "$DEPLOY_USER"
install -d -m 700 -o "$DEPLOY_USER" -g "$DEPLOY_USER" "/home/$DEPLOY_USER/.ssh"
touch "/home/$DEPLOY_USER/.ssh/authorized_keys"
chown "$DEPLOY_USER:$DEPLOY_USER" "/home/$DEPLOY_USER/.ssh/authorized_keys"
chmod 600 "/home/$DEPLOY_USER/.ssh/authorized_keys"

echo "== Directory layout"
install -d -m 2775 -o "$DEPLOY_USER" -g www-data "$APP_PATH" "$APP_PATH/releases" "$APP_PATH/shared" "$APP_PATH/uploads" "$APP_PATH/bin"
install -d -m 2775 -o "$DEPLOY_USER" -g www-data "$APP_PATH/shared/storage"
for sub in app/public framework/cache/data framework/sessions framework/views logs; do
    install -d -m 2775 -o "$DEPLOY_USER" -g www-data "$APP_PATH/shared/storage/$sub"
done
install -d -m 700 -o "$DEPLOY_USER" -g "$DEPLOY_USER" /var/backups/htfarms
install -d /var/www/letsencrypt
install -m 0755 -o "$DEPLOY_USER" -g "$DEPLOY_USER" "$REPO_DIR/deploy/remote/rollback.sh" "$APP_PATH/bin/rollback.sh"
install -m 0755 -o "$DEPLOY_USER" -g "$DEPLOY_USER" "$REPO_DIR/deploy/remote/backup.sh" "$APP_PATH/bin/backup.sh"

echo "== PHP-FPM"
install -m 0644 "$REPO_DIR/deploy/php/htfarms-fpm-pool.conf" "/etc/php/${PHP_VERSION}/fpm/pool.d/htfarms.conf"
install -m 0644 "$REPO_DIR/deploy/php/99-htfarms.ini" "/etc/php/${PHP_VERSION}/fpm/conf.d/99-htfarms.ini"
systemctl enable --now "php${PHP_VERSION}-fpm"
systemctl reload "php${PHP_VERSION}-fpm"

echo "== Restricted sudo for deploys"
visudo -cf "$REPO_DIR/deploy/sudoers/htfarms-deploy"
install -m 0440 "$REPO_DIR/deploy/sudoers/htfarms-deploy" /etc/sudoers.d/htfarms-deploy

echo "== Redis (local only)"
sed -i 's/^#\?\s*bind .*/bind 127.0.0.1 ::1/' /etc/redis/redis.conf
systemctl enable --now redis-server

echo "== Supervisor queue workers"
install -m 0644 "$REPO_DIR/deploy/supervisor/htfarms-worker.conf" /etc/supervisor/conf.d/htfarms-worker.conf
systemctl enable --now supervisor

echo "== Scheduler and backup timers"
for unit in htfarms-scheduler.service htfarms-scheduler.timer htfarms-backup.service htfarms-backup.timer; do
    install -m 0644 "$REPO_DIR/deploy/systemd/$unit" "/etc/systemd/system/$unit"
done
systemctl daemon-reload
systemctl enable htfarms-scheduler.timer htfarms-backup.timer

echo "== Log rotation"
install -m 0644 "$REPO_DIR/deploy/logrotate/htfarms" /etc/logrotate.d/htfarms

echo "== Nginx (HTTP only until the certificate exists)"
sed "s/htintegratedfarms\.com/${DOMAIN}/g" "$REPO_DIR/deploy/nginx/htfarms.conf" > /etc/nginx/sites-available/htfarms.conf
rm -f /etc/nginx/sites-enabled/default

cat <<EOF

Provisioning complete. Remaining manual steps (docs/DEPLOYMENT.md §Server setup):
  1. Add the GitHub deploy public key to /home/${DEPLOY_USER}/.ssh/authorized_keys
  2. mysql_secure_installation; create database + user (see docs)
  3. Create ${APP_PATH}/shared/.env (chmod 640, owner ${DEPLOY_USER}:www-data) with a NEW APP_KEY
  4. certbot certonly --webroot -w /var/www/letsencrypt -d ${DOMAIN} -d www.${DOMAIN} -m ${ADMIN_EMAIL} --agree-tos
     (temporarily serve HTTP for the challenge), then: ln -s /etc/nginx/sites-available/htfarms.conf /etc/nginx/sites-enabled/ && nginx -t && systemctl reload nginx
  5. Run the GitHub "Deploy" workflow, then: sudo supervisorctl reread && sudo supervisorctl update && sudo systemctl start htfarms-scheduler.timer
  6. Create the first admin: cd ${APP_PATH}/current && php artisan app:create-user
EOF
