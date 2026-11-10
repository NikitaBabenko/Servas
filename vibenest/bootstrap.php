<?php

// Only called before HTTP. No output may include ENV, exception text or Artisan output.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
umask(0077);
$stage = 'preflight';
$lock = null;
try {
    $dir = '/app/database/sqlite';
    $db = $dir . '/database.sqlite';
    $key = getenv('APP_KEY') ?: '';
    $decoded = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : false;
    if ($decoded === false || strlen($decoded) !== 32) {
        throw new RuntimeException('Invalid key');
    }
    $url = parse_url(getenv('APP_URL') ?: '');
    if (!$url || ($url['scheme'] ?? '') !== 'https' || empty($url['host'])
        || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])
        || !in_array($url['path'] ?? '', ['', '/'], true)) {
        throw new RuntimeException('Invalid URL');
    }
    // Laravel's SQLite connection honors DATABASE_URL; stale DB and session overrides
    // must never silently route this profile to another service.
    foreach (['DATABASE_URL', 'SESSION_CONNECTION', 'SESSION_DOMAIN', 'APP_BASE_PATH'] as $override) {
        if (getenv($override) !== false && getenv($override) !== '') {
            throw new RuntimeException('Conflicting environment');
        }
    }
    if (is_file('/app/.env') || !extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('Unsupported configuration');
    }
    $mounted = false;
    foreach (file('/proc/self/mountinfo', FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $fields = explode(' ', $line);
        if (($fields[4] ?? '') === $dir) {
            $mounted = true;
            break;
        }
    }
    if (!$mounted || !is_dir($dir) || !is_writable($dir) || is_link($db)) {
        throw new RuntimeException('Persistent mount required');
    }
    $stage = 'volume-lock';
    $lock = fopen($dir . '/.vibenest-bootstrap.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
        throw new RuntimeException('Concurrent initialization');
    }
    $stage = 'key-continuity';
    $fingerprint = $dir . '/.vibenest-app-key.sha256';
    $expected = hash('sha256', $key);
    if (is_file($fingerprint)) {
        if (!hash_equals($expected, trim(file_get_contents($fingerprint)))) {
            throw new RuntimeException('Changed key');
        }
    } else {
        // Do not silently adopt an existing upstream/reused database with an unknown key.
        if (file_exists($db) && filesize($db) > 0) {
            throw new RuntimeException('Unmanaged existing database');
        }
        $temp = $fingerprint . '.tmp';
        if (file_put_contents($temp, $expected . "\n") === false || !rename($temp, $fingerprint)) {
            throw new RuntimeException('Key marker unavailable');
        }
    }
    if (!file_exists($db) && !touch($db)) {
        throw new RuntimeException('Database unavailable');
    }
    foreach (['/app/storage/framework/cache/data', '/app/storage/framework/sessions',
        '/app/storage/framework/views', '/app/storage/logs', '/app/bootstrap/cache'] as $path) {
        if ((!is_dir($path) && !mkdir($path, 0700, true)) || !is_writable($path)) {
            throw new RuntimeException('Unwritable application storage');
        }
    }
    // Discard only derived config, never the SQLite file or user data.
    $cache = '/app/bootstrap/cache/config.php';
    if (is_file($cache) && !unlink($cache)) {
        throw new RuntimeException('Stale configuration');
    }
    $stage = 'framework';
    require '/app/vendor/autoload.php';
    $app = require '/app/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    if (config('app.debug') || config('app.fetch_link_titles')
        || config('database.default') !== 'sqlite'
        || config('database.connections.sqlite.database') !== $db
        || config('session.secure') !== true
        || in_array(Laravel\Fortify\Features::registration(), config('fortify.features'), true)) {
        throw new RuntimeException('Unsafe effective configuration');
    }
    $stage = 'migrations';
    if (Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]) !== 0) {
        throw new RuntimeException('Migration failed');
    }
    $stage = 'initial-owner';
    Illuminate\Support\Facades\DB::transaction(function () use ($app) {
        if (App\Models\User::query()->exists()) {
            return; // Reuse: never change a password, recreate or promote an existing user.
        }
        $password = getenv('SERVAS_BOOTSTRAP_PASSWORD') ?: '';
        if (strlen($password) < 16) {
            throw new RuntimeException('Initial password required');
        }
        $app->make(App\Actions\Fortify\CreateNewUser::class)->create([
            'name' => getenv('SERVAS_BOOTSTRAP_NAME') ?: '',
            'email' => getenv('SERVAS_BOOTSTRAP_EMAIL') ?: '',
            'password' => $password,
            'password_confirmation' => $password,
        ]);
    });
    $stage = 'caches';
    foreach (['config:cache', 'view:cache'] as $command) {
        if (Illuminate\Support\Facades\Artisan::call($command, ['--no-interaction' => true]) !== 0) {
            throw new RuntimeException('Cache failed');
        }
    }
    flock($lock, LOCK_UN);
    fclose($lock);
} catch (Throwable $error) {
    // Do not report($error), serialize requests, or print buffered command output.
    fwrite(STDERR, 'Servas initialization failed at ' . $stage . ". HTTP was not started.\n");
    exit(1);
}
