# VibeNest private profile (local review candidate)

**NOT_READY. This profile has not been built or run. No deploy badge is published.**
The current VibeNest Render adapter rejects the required `disk` field with
`render_persistent_disk_unsupported`. Keep that declaration: this application
must not start with ephemeral SQLite storage. A supported, reviewed native
pre-launch storage path is required before publishing or testing this profile.

## Source and container

The upstream Dockerfile, SQLite/MariaDB examples and GPL-3.0 LICENSE remain intact.
`vibenest/Dockerfile` is an additive COPY-only wrapper around publisher v1.1.3,
linux/amd64 digest
`sha256:33cb7cfc0944b25d04b57c299a1d3b9ca7cc666236d237af24232c4a1c909519`,
associated with upstream `088eaa3232f5d266e1e002c663797cdd2d8c77bb`.
No dependency installation, compilation, RUN instruction or build secret is needed.
The publisher's authoritative Composer classmap still covers every changed class:
all application overlays use existing class names and paths. The initializer is
an explicitly invoked PHP file, not a new autoloaded class.

The root Blueprint describes exactly one Docker web service. VibeNest's adapter
uses port 3000 for this Docker contract; the wrapper therefore listens on 3000 and
checks `/login` on that port. The unchanged upstream profile still listens on 80.
One FrankenPHP thread, no worker mode, no PHP-FPM process, Caddy admin disabled,
PHP limit 96 MB and opcache 32 MB are starting bounds, **not measured usage**.
Caddy's inherited admin-metrics healthcheck is replaced with a local login probe.
Configuration reference: https://frankenphp.dev/docs/config/ .

## Owner-supplied runtime environment

Before any launch, use the platform's existing sensitive environment storage for:

- `APP_URL`: the final HTTPS application origin, without credentials/query/fragment.
- `APP_KEY`: `base64:` plus a cryptographically random 32-byte key; keep it stable.
- `SERVAS_BOOTSTRAP_NAME`, `SERVAS_BOOTSTRAP_EMAIL`, `SERVAS_BOOTSTRAP_PASSWORD`:
  first account details; use a unique password of at least 16 bytes.

Do not put secret values in Git, URLs, command arguments, examples, screenshots or
receipts. There are no default credentials. The bootstrap fields are read directly
from ENV, never from the Laravel config cache. Remove the three bootstrap fields
from native secret storage after successful initial login, before the next normal
replacement. The entrypoint also unsets them before starting HTTP.

The entrypoint enforces production mode, debug off, registration off, automatic
title fetching off, SQLite, secure database-backed sessions, file cache and sync
queue. Supplying `DATABASE_URL`, `SESSION_CONNECTION`, `SESSION_DOMAIN`,
`APP_BASE_PATH` or an `/app/.env` file is rejected to avoid stale overrides.
The root `.env.example` now genuinely defaults to SQLite; the separate upstream
MariaDB example remains unchanged. MySQL drivers remain installed in the base.

## Initialization and reuse

A native persistent volume must be mounted at `/app/database/sqlite`. The initializer
requires an exact mountinfo entry and writable directory before touching any data.
It takes a nonblocking file lock, validates the key and HTTPS origin, and records
a key fingerprint on the persistent volume. It creates the SQLite file only when
absent. Application cache/log directories must also be writable; they are ephemeral
and contain no library records. No chmod 777, remote shell or provider-side command
is part of the plan.

Migrations run before the first account and before HTTP. Every exception or nonzero
migration/cache result aborts the process with a generic stage message. The
initializer uses the same upstream `CreateNewUser` action as `artisan user:create`,
inside a transaction, only when the user table is empty. Public registration never
opens. Existing users, password hashes, roles and sessions are not reset.

For managed reuse, keep the same volume and APP_KEY; migrations run normally, and
any existing user prevents bootstrap creation. A changed key is rejected before
migrations. An existing nonempty database without the profile's key marker is
rejected rather than silently adopted. Importing an older upstream volume needs
a separate reviewed migration procedure; this patch does not implement one.
If an empty database needs retry after failed initialization, supply corrected
bootstrap ENV and the same key through the native path. No data deletion is needed.

## Ownership and network behavior

All new per-object checks reuse `PermissionHelper::userIsOwnerOfModal`. Missing or
foreign objects yield 403 before writes; ordinary missing route bindings yield 404.
Related-group/tag IDs are checked before create/update, including group parents,
smart-group tag rules and public sharing. Bulk operations check all requested IDs
before any mutation. Existing web update FormRequests retain their owner checks;
API token ability and related-ID validation remain in place.

`WebpageData::getWebPageTitle` returns the URL truncated to 255 characters when
`SERVAS_FETCH_LINK_TITLES=false`, before URL parsing or any HTTP call. This shared
helper covers web create/update and API create. Explicit titles, links, import,
groups and search remain available. This disables metadata fetching; it is not a
new SSRF-safe fetch client. The profile forcibly keeps it disabled on every start.

## Future isolated acceptance (not executed)

Use only synthetic fixtures after coordinator slot assignment and current native
gates. No account or resource is authorized by this document.

1. Verify two existing synthetic users cannot delete each other's Link/Group/Tag,
   rename the other's tag, attach the other's group/tag, assign a foreign parent,
   use foreign smart-group tags, share the other's group or partially mutate a
   mixed-owner bulk batch. Verify unchanged rows/pivots after rejection. Verify the
   owner can perform the same operations. Cover cookie web and scoped API tokens.
2. With the flag off, instrument/mock the HTTP client in an isolated harness and
   assert zero outbound calls for empty-title web create, web update and API create.
   Use synthetic ordinary URLs, never internal destinations. Confirm explicit title
   preservation and the bounded URL fallback. Import a tiny folderless browser HTML.
3. Before HTTP, verify missing/invalid key, absent/read-only mount, failed migration,
   missing initial-owner ENV and conflicting DATABASE_URL all stop initialization.
   Repeated/replaced starts retain the owner password hash and library rows; changed
   APP_KEY and unmanaged old DB are refused. Verify concurrent initialization locks.
4. Verify HTTPS redirects, ingress forwarded-header handling, Secure/HttpOnly/
   SameSite cookies, CSRF rejection and registration denial. Upstream TrustProxies
   still trusts `*`; proxy isolation and header sanitization remain runtime checks.
5. Save/group a few links, search from a second client, then perform a normal gated
   container replacement and verify records, tags, groups and login. Record exact
   image/SHA, startup/idle/peak RAM, CPU and actual disk, plus native cleanup.

Static parser/diff checks cannot establish runtime or Free-tier compatibility.
