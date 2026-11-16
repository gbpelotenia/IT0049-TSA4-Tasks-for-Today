# Submit TSA4 on GitHub

## Publish through VS Code

1. Extract the project and open the `tasks-for-today` folder in VS Code. Open the folder containing `artisan` and `composer.json`, not its parent.
2. Install Git from https://git-scm.com/downloads if needed, and restart VS Code. Sign in to your own GitHub account when prompted.
3. Run and test the project using the README. Fill in your project/student details if your instructor requires them.
4. Open **Source Control** in the left sidebar. Select **Initialize Repository**. If VS Code already shows a repository for this folder, use that one.
5. Review the changes. Source files, `README.md`, `.env.example`, `composer.lock`, `package-lock.json`, migrations, seeders, and tests belong in the commit. `.env`, database files, dependency folders, and logs should be absent because `.gitignore` excludes them.
6. Type this commit message and click **Commit**. If asked to stage changes, choose **Yes**:

```text
Complete TSA4 task CRUD authentication archiving and email notifications
```

7. Click **Publish Branch** (or **Publish to GitHub**). Authorize VS Code in the browser if prompted.
8. Name the repository **IT0049-TSA4-Tasks-for-Today**. Choose **Public** if your instructor needs access without an invitation; otherwise choose Private and invite your instructor through GitHub repository settings.
9. Open the published repository on GitHub. It should show `app`, `database`, `resources`, `routes`, the README, and both lock files directly at the repository root. Do not upload only the ZIP or nest the entire project inside another folder.
10. Confirm the **Actions** tab shows the Laravel checks passing. The workflow installs dependencies, builds frontend assets, and runs tests.
11. Copy the repository URL from your browser. It will have the form `https://github.com/YOUR-USERNAME/IT0049-TSA4-Tasks-for-Today`. Replace the username with your actual account; do not submit a placeholder URL.

If your Git identity is missing, follow VS Code's prompt or set it in a terminal:

```sh
git config --global user.name "Your Name"
git config --global user.email "Your GitHub verified or GitHub noreply email"
```

If you see `Repository not found` or `Permission denied`, verify that VS Code is signed in to the account that owns the repository. Use browser-based sign-in; never paste a GitHub password or access token into the project or a public chat.

## Verify your submission

- The repository contains raw source, migrations, and the seeder.
- The README explains setup, testing, and mail configuration.
- The repository is accessible to the instructor.
- The app runs from a fresh clone with `php setup.php`, `npm ci`, `npm run build`, and `php artisan serve`.
- You can demonstrate logged-out access to Welcome, Task List, Profile, and About.
- Guests are redirected from Create/Edit/Archive actions.
- A pending → done update produces one log email; editing an already-done task does not produce another.
- Archiving hides a task without deleting its row.
- You provide a separate working hosted-app URL. A GitHub repository URL is not a live Laravel application URL.

## Optional terminal route

Create an empty repository named `IT0049-TSA4-Tasks-for-Today` on https://github.com/new without adding a README or `.gitignore`. Then run these commands inside the project folder, replacing YOUR-USERNAME:

```sh
git init
git add .
git status
git commit -m "Complete TSA4 task CRUD authentication archiving and email notifications"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/IT0049-TSA4-Tasks-for-Today.git
git push -u origin main
```

Review `git status` before committing; `.env` must not appear. If `origin` already exists, inspect it with `git remote -v` and use the correct existing remote instead of adding another. If the remote already has files, use VS Code to clone that repository into a new folder and copy this project source into the clone. Do not force-push over existing work.
