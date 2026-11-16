<?php

// Run from the VS Code terminal: php setup.php
if (PHP_VERSION_ID < 80200) {
    fwrite(STDERR, "PHP 8.2 or newer is required.\n");
    exit(1);
}
chdir(__DIR__);
foreach (['ctype', 'curl', 'dom', 'fileinfo', 'mbstring', 'openssl', 'PDO', 'pdo_sqlite', 'session', 'tokenizer', 'xml'] as $ext) {
    if (! extension_loaded($ext)) {
        fwrite(STDERR, "Missing PHP extension: $ext. Enable it in the php.ini shown by php --ini.\n");
        exit(1);
    }
}
if (! file_exists('.env')) {
    copy('.env.example', '.env');
}
if (! file_exists('database/database.sqlite')) {
    touch('database/database.sqlite');
}
foreach (['composer install --prefer-dist --no-interaction', escapeshellarg(PHP_BINARY).' artisan key:generate --ansi', escapeshellarg(PHP_BINARY).' artisan migrate --seed --force'] as $command) {
    // Preserve an existing installation's key so encrypted data stays readable.
    if (str_contains($command, 'key:generate') && preg_match('/^APP_KEY=.+$/m', file_get_contents('.env'))) {
        continue;
    }
    passthru($command, $code);
    if ($code !== 0) {
        fwrite(STDERR, "Setup stopped. Fix the error above, then run php setup.php again.\n");
        exit($code);
    }
}
echo "\nReady! Run: php artisan serve\nOpen: http://127.0.0.1:8000\nDemo login: demo@example.com / DemoPass123!\n";
