# Tasks for Today — IT0049 TSA4

A Laravel task management system with full CRUD, Breeze authentication, soft deletion through `is_archived`, and completion email notifications sent with Laravel's `Mail` facade.

## Run in VS Code

1. Extract the download. Open **tasks-for-today** in VS Code (the folder containing `artisan` and `composer.json`). If using GitHub, clone the repository and open its root folder instead.
2. Install **PHP 8.2+**, **Composer 2**, and add both to PATH. Restart VS Code after installing. Check `php -v` and `composer --version` in **Terminal → New Terminal**.
3. In that terminal, run:

```sh
php setup.php
php artisan serve
```

4. Open **http://127.0.0.1:8000**. You can browse the public pages immediately.
5. Log in with **demo@example.com** / **DemoPass123!**, or choose **Register** to create your own account.

Keep the server terminal running; Ctrl+C stops it. Apache, MySQL, and a queue worker are not required for the default SQLite/log-mail setup. The ZIP includes compiled CSS/JS, so Node.js is not needed to run that download. A GitHub clone does require the frontend build below because `public/build` is generated and ignored by Git.

`setup.php` installs Composer dependencies, creates `.env` and the SQLite file, generates a unique application key, migrates tables, and seeds local sample records. It preserves your key and records on repeated runs. An internet connection is needed for Composer. Do not run `migrate:fresh` on a database whose data you want to keep.

Required PHP extensions: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, PDO_SQLite, Session, Tokenizer, XML. ZIP or an unzip utility helps Composer extract packages. On Windows, PHP can come from XAMPP; add its PHP directory to PATH. Use `php --ini` to find the configuration used by your terminal, and enable `curl`, `fileinfo`, `mbstring`, `pdo_sqlite`, and `sqlite3` as needed.

### Frontend build and GitHub clones

Use Node.js 22 LTS:

```sh
npm ci
npm run build
```

For live frontend editing, use `npm run dev` in a second terminal alongside `php artisan serve`. Rebuild assets when finished. No external font service or CSS CDN is required.

## Pages and access

| URL | Access | Purpose |
| --- | --- | --- |
| `/` | Public | Welcome |
| `/tasks` | Public | Task list, search, status filter, pagination |
| `/tasks/{task}` | Public | Active task details |
| `/profile` | Public | Project profile |
| `/about` | Public | About the system |
| `/tasks/create` | Logged in | Create task |
| `/tasks/{task}/edit` | Logged in | Edit task |
| POST `/tasks` | Logged in | Save task |
| PUT/PATCH `/tasks/{task}` | Logged in | Update task and trigger completion mail |
| DELETE `/tasks/{task}` | Logged in | Set `is_archived = true` |
| `/account` | Logged in | Breeze personal profile, password, and account deletion |

Archived tasks remain in the database and return 404 from detail/edit/update routes. The public list excludes them. There is no hard-delete or restore task interface.

This is a shared task board: all registered members can manage all tasks. Task details are public as required by the assignment. The system does not define per-user ownership or admin roles.

## Task fields and validation

| Field | Validation |
| --- | --- |
| Title | Required, trimmed, string, maximum 150 characters |
| Description | Required, trimmed, string, maximum 5,000 characters |
| Due date | Required, valid `YYYY-MM-DD` date |
| Priority | Required: `low`, `medium`, or `high` |
| Status | Required: `pending`, `in_progress`, or `done` |

Dates in the past are permitted so overdue tasks can be maintained. `is_archived` is controlled by the archive action, not accepted from form input. Blade escapes displayed task data. Forms retain CSRF protection, and Breeze keeps its password hashing, validation, login throttling, and session handling.

The supplied TSA4 document references TSA2/TSA3/TFA7 without including those files. This standalone implementation uses the task fields and validation above. The public Profile is a project profile; private account settings are at `/account`. Adjust project/student information in `resources/views/public-profile.blade.php` if your instructor expects a personal or group profile. Laravel 12 is used for PHP 8.2 and Breeze compatibility; the Laravel 13 documentation references in the assignment describe the same core concepts.

## Completion email configuration

The default `.env` uses a safe log mailer:

```dotenv
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="Tasks for Today"
TASK_NOTIFICATION_RECIPIENT=notifications@example.com
```

Change `TASK_NOTIFICATION_RECIPIENT` in your local `.env` to the address you want to verify. With `MAIL_MAILER=log`, nothing is delivered to a real inbox. Laravel writes the recipient, subject, and HTML email into **storage/logs/laravel.log**. No worker is needed because sending is synchronous.

After editing `.env`, run:

```sh
php artisan optimize:clear
```

### Demonstrate the required flow

1. Log in and create a **Pending** task with a distinctive title, such as `TSA4 email demonstration`.
2. Edit it, select **Done**, and save. The page reports that a completion email was generated.
3. Open `storage/logs/laravel.log` in VS Code and search for `Subject: Task completed:`. Verify the recipient, title, description, due date, and status in the generated message.
4. Edit the same task's description while leaving its status **Done**. Save again. No second completion email should appear for this edit.
5. Archive the task. Confirm it disappears from the public list, but its database row still has `is_archived = 1`.

You can watch the log in a second terminal:

```powershell
# Windows PowerShell
Get-Content storage/logs/laravel.log -Wait -Tail 60
```

```sh
# macOS/Linux
tail -f storage/logs/laravel.log
```

The rule is **a transition from any non-done status to done during update**. Pending → Done and In progress → Done send an email. Done → Done does not. Done → Pending sends none; completing that reopened task later is a new transition and sends one new email. Creating or seeding a task that is already done does not run the update notification.

Implementation: `TaskController::update()` compares the saved status with the validated new status under a database transaction/row lock, then calls `Mail::to(...)->send(new TaskCompleted(...))`. A mail transport exception rolls back the task update; fix the transport before retrying. A synchronous SMTP send and a database commit are separate systems, so this teaching implementation does not promise exactly-once delivery across server failures.

### Optional safe SMTP sandbox

Use credentials from your own SMTP test service, such as a Mailtrap Email Sandbox. Put them in `.env`, never in source control:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=your-sandbox-smtp-host
MAIL_PORT=2525
MAIL_USERNAME=your-sandbox-username
MAIL_PASSWORD=your-sandbox-password
MAIL_FROM_ADDRESS=tasks@example.com
MAIL_FROM_NAME="Tasks for Today"
TASK_NOTIFICATION_RECIPIENT=your-test-recipient@example.com
```

Use the service's specified host, port, and TLS settings. Laravel's SMTP configuration reads `MAIL_SCHEME` (`smtp` or `smtps`, as required). Clear cached configuration after changes. The SMTP sandbox should capture test messages rather than deliver real mail.

## Source structure

| Requirement | Implementation |
| --- | --- |
| Resource routes and authenticated management | `routes/web.php` |
| CRUD, archive, and completion transition | `app/Http/Controllers/TaskController.php` |
| Shared validation | `app/Http/Requests/TaskRequest.php` |
| Eloquent model and active scope | `app/Models/Task.php` |
| Mailable | `app/Mail/TaskCompleted.php` |
| Email view | `resources/views/emails/task-completed.blade.php` |
| Recipient configuration | `config/tasks.php` |
| Breeze authentication | `routes/auth.php`, `app/Http/Controllers/Auth/`, `resources/views/auth/` |
| Database and sample data | `database/migrations/`, `database/seeders/DatabaseSeeder.php` |
| Task/access/email tests | `tests/Feature/TaskManagementTest.php` |

## Verification

```sh
php artisan test
php artisan route:list --path=tasks -v
```

Automated tests use a separate in-memory SQLite database. They cover public pages, all protected management actions, validated CRUD, archived-record retention/visibility, email transitions and duplicate suppression, HTML escaping, and Breeze authentication/account workflows. Tests do not erase local task data. The GitHub Actions workflow runs the suite and frontend build on pushes and pull requests.

## Troubleshooting

| Error or symptom | Fix |
| --- | --- |
| `php` / `composer` not recognized | Install the tool, add it to PATH, and restart VS Code. Windows: check `where.exe php`. macOS/Linux: check `which php`. |
| Unsupported PHP version | Use PHP 8.2+. Do not bypass Composer's platform requirements. |
| `could not find driver` | Enable PDO_SQLite in the CLI `php.ini` shown by `php --ini`, then restart the server. |
| Missing app key or database tables | Run `php setup.php` from the directory containing `artisan`. |
| Vite manifest missing or no styling | Run `npm ci` and `npm run build`. If a stopped Vite server left `public/hot`, delete that file. |
| No email in an inbox | The default log mailer writes to `storage/logs/laravel.log`; this is expected. |
| No new log entry on saving Done | Check that the task was previously pending/in progress and inspect validation errors. Done → Done intentionally sends nothing. |
| Email recipient configuration error | Set a valid `TASK_NOTIFICATION_RECIPIENT` in `.env` and run `php artisan optimize:clear`. |
| SMTP connection error / 500 on completing | Check sandbox host, port, credentials, and TLS, or switch to `MAIL_MAILER=log` and clear config. The task update rolls back when the transport fails. |
| `419 Page Expired` | Refresh the form, allow cookies, and consistently use the same hostname (`127.0.0.1` or `localhost`). Do not disable CSRF. |
| Port 8000 busy | Run `php artisan serve --port=8001`, then visit `http://127.0.0.1:8001`. |
| Permission error | Your local/server user needs write access to `storage`, `bootstrap/cache`, and the SQLite database directory. |
| Stale configuration | Run `php artisan optimize:clear`, then restart the server. |

For help, share the exact error, command, and a screenshot if useful. Do not share `.env`, passwords, application keys, or SMTP credentials.

## GitHub and hosted submission

Follow **[the GitHub submission guide](docs/GITHUB_SUBMISSION.md)**. Upload the raw project source with migrations and seeders, not just a ZIP. Do not commit `.env`, SQLite databases, `vendor`, `node_modules`, or logs. Keep `.env.example` and both lock files.

The assignment also requires a live application link. GitHub Pages serves static sites and cannot run Laravel/PHP. Use a Laravel-capable host and see **[deployment guidance](docs/DEPLOYMENT.md)**. A working local download does not create a hosted site or a GitHub repository.

Before submission, add your student/group name, section, professor, real GitHub URL, and actual hosted URL to your submission as required by your class.
