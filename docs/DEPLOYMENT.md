# Host the Laravel application

Choose a service that runs PHP 8.2+ applications with a Laravel-compatible web server and a persistent database. GitHub Pages cannot run this application. Hosting accounts, provider choice, and public deployment are separate from the local project download.

## Build and runtime settings

- Build PHP dependencies with `composer install --no-dev --optimize-autoloader`.
- Build browser assets with Node.js 22: `npm ci && npm run build`.
- Configure the web server's document root as **public/**, never the repository root. `php artisan serve` is for local development.
- Provide runtime environment variables or a private `.env`. Set `APP_ENV=production`, `APP_DEBUG=false`, the real HTTPS `APP_URL`, and a unique, stable `APP_KEY` generated with `php artisan key:generate --show` in a secure terminal. Do not publish the generated key.
- Use `SESSION_SECURE_COOKIE=true` over HTTPS. Make `storage` and `bootstrap/cache` writable by the web process.
- Configure your database. MySQL/PostgreSQL is usually more convenient for hosted apps. For SQLite, set an absolute `DB_DATABASE` file path on a persistent writable disk; create the file before migrating. The database directory must also be writable. Ephemeral storage loses records when an instance restarts.
- Run `php artisan migrate --force`, `php artisan config:cache`, and `php artisan view:cache` during deployment. Do not run `migrate:fresh` on existing data.
- Use `MAIL_MAILER=log` for the assignment demonstration or configure a safe SMTP sandbox. Set a valid `TASK_NOTIFICATION_RECIPIENT`. With the log driver, verify messages using the hosting provider's private application logs or an SSH terminal. Do not expose a public log viewer.
- Completion notifications are synchronous, so they need no queue worker. Laravel sessions and cache use database tables supplied by the migrations.
- The seeder deliberately creates demo credentials only in local/testing environments. Register a new account on the hosted site.

## Before submitting the live link

Open the live URL in an incognito window and check all four public pages. Confirm creating/editing/archiving requires login. Register an account, complete a pending task, inspect the generated mail, edit it while still done to confirm no resend, and archive it. Verify that your deployed source matches the GitHub submission.

Review whether public registration and a public shared task list suit any future use beyond this assignment. They are the access rules specified by TSA4; this project does not implement private per-user tasks.
