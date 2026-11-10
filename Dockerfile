# Publisher v1.1.3, upstream 088eaa3232f5d266e1e002c663797cdd2d8c77bb, linux/amd64.
# No RUN, dependency install, build-time secret, or source compilation.
FROM beromir/servas@sha256:33cb7cfc0944b25d04b57c299a1d3b9ca7cc666236d237af24232c4a1c909519
WORKDIR /app
COPY app/Helpers/PermissionHelper.php app/Helpers/WebpageData.php /app/app/Helpers/
COPY app/Http/Controllers/LinkController.php app/Http/Controllers/GroupController.php app/Http/Controllers/TagController.php app/Http/Controllers/PublicLinkController.php /app/app/Http/Controllers/
COPY app/Http/Controllers/ApiControllers/LinkController.php /app/app/Http/Controllers/ApiControllers/
COPY app/Services/BulkEditingService.php /app/app/Services/
COPY config/app.php /app/config/app.php
COPY vibenest/bootstrap.php vibenest/entrypoint.sh /opt/vibenest/
COPY vibenest/Caddyfile /etc/caddy/Caddyfile
COPY vibenest/php.ini /usr/local/etc/php/conf.d/zzzz-vibenest.ini
COPY LICENSE /app/LICENSE
ENV APP_ENV=production APP_DEBUG=false DB_CONNECTION=sqlite DB_DATABASE=/app/database/sqlite/database.sqlite DB_FOREIGN_KEYS=true SERVAS_ENABLE_REGISTRATION=false SERVAS_FETCH_LINK_TITLES=false SESSION_SECURE_COOKIE=true SESSION_DRIVER=database CACHE_DRIVER=file QUEUE_CONNECTION=sync GOMAXPROCS=1
EXPOSE 3000
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 CMD curl -fsS http://127.0.0.1:3000/login >/dev/null || exit 1
ENTRYPOINT ["/bin/sh", "/opt/vibenest/entrypoint.sh"]
