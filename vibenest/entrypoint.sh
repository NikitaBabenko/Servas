#!/bin/sh
set -eu
umask 077
cd /app
# Enforced profile values override stale owner ENV on reuse. Reject DATABASE_URL
# in bootstrap rather than silently pointing SQLite at an unrelated database.
export APP_ENV=production APP_DEBUG=false
export DB_CONNECTION=sqlite DB_DATABASE=/app/database/sqlite/database.sqlite DB_FOREIGN_KEYS=true
export SERVAS_ENABLE_REGISTRATION=false SERVAS_FETCH_LINK_TITLES=false
export SESSION_SECURE_COOKIE=true SESSION_DRIVER=database CACHE_DRIVER=file QUEUE_CONNECTION=sync
export GOMAXPROCS=1 APP_CONFIG_CACHE=/app/bootstrap/cache/config.php
php /opt/vibenest/bootstrap.php
# These secrets must not reach the HTTP process or config cache.
unset SERVAS_BOOTSTRAP_NAME SERVAS_BOOTSTRAP_EMAIL SERVAS_BOOTSTRAP_PASSWORD
exec frankenphp run --config /etc/caddy/Caddyfile
