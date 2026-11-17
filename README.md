# Adaptive Client Manager

[![CI](https://github.com/explicitworkload/adaptive-client-manager/actions/workflows/ci.yml/badge.svg)](https://github.com/explicitworkload/adaptive-client-manager/actions/workflows/ci.yml)

A web app for managing clients, quotes, invoices and payments. It emails invoices and quotes as PDFs,
produces reports, and gives each client a login to view their own invoices and statement.

Adaptive Client Manager was originally created by Dead Pixel Studio on CakePHP 2. This version rebuilds it on CakePHP 5 for PHP 8.3+, with MySQL or MariaDB. See
[Credits](#credits).

- [What you need](#what-you-need)
- [Installing on a web host (DirectAdmin)](#installing-on-a-web-host-directadmin)
- [After installing](#after-installing)
- [Updating](#updating)
- [Troubleshooting](#troubleshooting)
- [Installing with SSH and Composer](#installing-with-ssh-and-composer)
- [Development](#development)
- [Credits](#credits)

## What you need

**On the web host**
- PHP 8.3 or newer (8.5 recommended), **at a patched release: 8.5.12+, 8.4.27+ or 8.3.36+**. Earlier
  releases are vulnerable to "PHP2Shell" (CVE-2026-109618 and CVE-2026-109619), which lets attackers
  take over a server without logging in, whatever app is running
  ([CISA advisory](https://www.cisa.gov/news-events/alerts/2026/10/21/critical-vulnerabilities-php-require-immediate-patching)).
  Required extensions: `intl`, `mbstring`, `pdo_mysql`, `gd`.
  `intl` is the one hosts most often leave off.
- A MySQL or MariaDB database. `install.sql` is tested on MariaDB 10.11 and uses standard MySQL syntax.
- Apache with `mod_rewrite`. That's the norm on DirectAdmin and cPanel; the included `.htaccess`
  files handle the URLs.

Nothing on your computer: download the ready-made zip from
[Releases](https://github.com/explicitworkload/adaptive-client-manager/releases). The web host doesn't
need Composer or SSH. (To build the zip yourself instead, you need PHP 8.3+ and
[Composer](https://getcomposer.org).)

## Installing on a web host (DirectAdmin)

### 1. Get the upload package

Download `adaptive-client-manager-<version>.zip` from the latest
[release](https://github.com/explicitworkload/adaptive-client-manager/releases/latest).

Or build it yourself on your computer:

```bash
git clone https://github.com/explicitworkload/adaptive-client-manager.git
cd adaptive-client-manager
composer install
bin/build-release.sh
```

That creates `build/acm5-release.zip`, the same as the release download: everything the site needs,
including the PHP libraries Composer downloads (the host can't fetch them without Composer).

**The same zip can be used for any number of sites:** each installation generates its own security key (`Security.salt`) on its first page load
and saves it into its `config/app_local.php`.

### 2. Set up PHP

In DirectAdmin, open the PHP version selector (it may be called **Select PHP Version** or
**PHP Settings**), choose PHP 8.5 (8.3 or 8.4 also work) and make sure `intl`, `mbstring`,
`pdo_mysql` and `gd` are ticked.

Then check the exact release: DirectAdmin shows it next to the version, or your host can tell you. It
must be **8.5.12+, 8.4.27+ or 8.3.36+** (see [What you need](#what-you-need)). If your host only offers
older releases, ask them to update PHP before putting the site online.

### 3. Create the database

1. In DirectAdmin, go to **MySQL Management → Create new Database**. Note the database name,
   username and password.
2. Open **phpMyAdmin**, select the new database, go to the **Import** tab and import
   [`config/schema/install.sql`](config/schema/install.sql). You can also paste the file into the
   **SQL** tab.

This creates all tables plus the default settings, email templates and a 0% tax rate. It refuses to
run on a database that already has these tables, so it can't overwrite an existing installation.

### 4. Upload the files

In DirectAdmin's **File Manager**:

1. Upload the zip to where the app should live:
   - straight into `public_html` → the app is at `https://yourdomain.com/`
   - into a subfolder, e.g. `public_html/acm/` → the app is at `https://yourdomain.com/acm/`
2. Select the zip and choose **Extract**. Then delete the zip.

The top-level `.htaccess` sends all requests into `webroot/`, so the rest of the app (including your
configuration) can't be opened from a browser.

### 5. Fill in the settings

Open `config/app_local.php` in File Manager's editor. Only the block at the top needs changing:

```php
$siteUrl = 'https://www.yourdomain.com';   // scheme and domain only, no subfolder

$database = [
    'host' => 'localhost',
    'username' => 'your_db_user',
    'password' => 'your_db_password',
    'database' => 'your_db_name',
    'encoding' => 'utf8mb4',
];
```

- `$siteUrl` is your domain, even if the app is in a subfolder (the subfolder is detected
  automatically). `www.` and the bare domain both work.
- Further down, set `'defaultTimezone'` to your timezone, e.g. `'Asia/Singapore'` or `'Europe/London'`
  (see the [list of timezones](https://www.php.net/manual/en/timezones.php)).
- Leave `'debug' => false`. Debug mode shows error details, including file paths and code, to anyone.
- Leave `'salt' => '__SALT__'` as it is. On the first page load it's replaced with a random key unique
  to this site. If `app_local.php` isn't writable, the key is kept in `tmp/security_salt` and a warning
  is logged; copy it into `app_local.php` then, so clearing `tmp/` doesn't change it.

### 6. Create the admin account

Open the site in your browser. On a new installation you'll see a **Setup** page: create the first
admin account there. You're signed in straight away and taken to the company settings.

The setup page only works while there are no user accounts. Once the first admin exists it returns
"not found", so nobody else can use it.

## After installing

Under **Settings**, signed in as the admin:

- **Config:** company name, address, email (used as the sender of invoice emails), currency symbol and
  payment information. **Edit Logo** uploads your logo (PNG).
- **Theme:** pick the main colour.
- **Users:** add staff and client logins.
- **Email:** edit the email templates for invoices and quotes. Clicking a `[SHORTCODE]` inserts it at
  the cursor.
- **Tax Rates:** add the rates you charge.

Then **send one invoice email to yourself** to confirm your host's mail works. The app sends with PHP's
`mail()`, which most shared hosts support. To use SMTP instead, set it under `EmailTransport` in
`config/app_local.php` (see the [CakePHP email docs](https://book.cakephp.org/5/en/core-libraries/email.html#configuring-transports)).

### Users and roles

| Role | Can do |
|---|---|
| Admin | Everything, including Settings, Users, Tax Rates and Email Templates |
| Manager | Clients, invoices, quotes, payments and reports; no Settings |
| Client | See their own client area, invoices, quotes and statement |

A client login is linked to its client by the client's name. Create the client first, then add the
login under **Users → Add → Client**. If you rename a client, its login and invoices follow
automatically.

## Updating

Download `adaptive-client-manager-<version>-update.zip` from the
[release](https://github.com/explicitworkload/adaptive-client-manager/releases/latest) (or build it
with `bin/build-release.sh --update`), then extract it over the installed folder in File Manager,
overwriting files. Update packages leave out `config/app_local.php`, your logo and the generated theme stylesheet,
so your settings, logo and colour are kept.

**Back up first:** in phpMyAdmin, export the database, and download the site folder.

## Troubleshooting

Errors are written to `logs/error.log`; check there first.

| Symptom | Fix |
|---|---|
| "Invalid Host header" / every page "not found" | `$siteUrl` in `config/app_local.php` doesn't match the address you open. Use your domain only, e.g. `https://yourdomain.com`. |
| Blank page, "Class … not found" or "Composer dependencies require a PHP version" | The PHP version is below 8.3 or a required extension (often `intl`) is missing. See step 2. |
| How do I check the PHP version? | The app doesn't reveal it to visitors (it removes PHP's `X-Powered-By` header). Check DirectAdmin's PHP version selector or ask your host. It must be 8.5.12+, 8.4.27+ or 8.3.36+. |
| `£`, `é`, `–` show as `Â£`, `Ã©`, `â€“` | The `'encoding'` setting doesn't match the database. Use `'utf8mb4'` for databases created with `install.sql` (see the note below for databases from the old app). |
| Pages look unstyled after an update | The theme stylesheet is rebuilt automatically, so `webroot/css/` must be writable by PHP. On DirectAdmin PHP usually runs as your own user, so the uploaded folder already is; otherwise set the folder to 755. |
| PDF download fails | The PDF font cache lives in `tmp/fonts/`; `tmp/` must be writable. Keep `resources/fonts/` intact. |
| An image or file gives "403 Forbidden" | The file is unreadable (set permissions to 644) or a hosting firewall is blocking it. |
| Forgotten password | Another admin can set a new one under Settings → Users → Edit. If no admin can log in: with SSH, run `bin/cake create_user`; without SSH, emptying the `users` table in phpMyAdmin brings back the Setup page, but every login then has to be recreated. |

**Coming from the old CakePHP 2 version** (same database): use `'encoding' => 'latin1'`, because the
old app stored text as UTF-8 bytes in latin1 tables. Run
[`config/schema/upgrade_existing_database.sql`](config/schema/upgrade_existing_database.sql) once in
phpMyAdmin; it widens the password column. Old passwords keep working and are upgraded to bcrypt on
each user's next login.

## Installing with SSH and Composer

On hosts with SSH you can skip the zip:

```bash
composer install --no-dev --optimize-autoloader
cp config/app_local.production.php config/app_local.php   # then fill in the top block
bin/cake migrations migrate                                # instead of importing install.sql
bin/cake create_user admin --email you@example.com         # or use the Setup page
```

Point the web server's document root at `webroot/`, or leave it at the project folder and let
`.htaccess` handle it.

## Development

```bash
composer install
DATABASE_URL="sqlite:///$PWD/tmp/dev.sqlite" bin/cake migrations migrate
DATABASE_URL="sqlite:///$PWD/tmp/dev.sqlite" bin/cake server     # http://localhost:8765
vendor/bin/phpunit                                               # test suite (SQLite, no MySQL needed)
```

On a fresh development database, open the site and use the Setup page, or run `bin/cake create_user`.

### Where things are

| Path | What |
|---|---|
| `src/Controller/` | Pages and access rules (`AppController` holds the role checks) |
| `src/Model/Table/` | Database tables, validation, invoice/quote total calculation |
| `src/Service/` | PDF generation, email shortcodes, theme stylesheet |
| `templates/` | Page templates; `templates/layout/` for the page frames |
| `config/Migrations/` | Database schema (the source for `config/schema/install.sql`) |
| `config/app_local.production.php` | Template for the live site's settings |
| `bin/build-release.sh` | Builds the upload and update packages |
| `webroot/vendor/` | Browser libraries, self-hosted and pinned |

Two rules for changes:
- **Theme:** edit `webroot/css/dp_template.css`, not `dead_pixel.css`. The latter is generated from
  the template and the colour saved under Settings → Theme, and rebuilt automatically when the
  template changes.
- **Schema:** after changing the migrations, regenerate `config/schema/install.sql` so new
  installations match. Run the migrations on an empty MySQL/MariaDB database, export it from
  phpMyAdmin (or `mysqldump --skip-add-drop-table`), and keep the header comments.

### Releasing a new version

The version lives in the [`VERSION`](VERSION) file and is shown in the app's header. To release:

1. Change `VERSION`, e.g. `2.0.0` → `2.1.0` ([semantic versioning](https://semver.org); a suffix such
   as `2.1.0-beta.1` makes a pre-release).
2. Commit and push to `main`.

The [Release workflow](.github/workflows/release.yml) then runs the tests, builds both zips, tags the
commit `v2.1.0` and publishes a GitHub Release with the zips and auto-generated notes. Pushes that
don't change `VERSION` don't create a release, and a version that already has a tag is skipped. Every
push and pull request also runs the tests ([CI workflow](.github/workflows/ci.yml)).

### Browser libraries

| Library | Version | Licence | Used for |
|---|---|---|---|
| jQuery | 4.0.0 | MIT | page scripts |
| DataTables | 3.1.5 | MIT | sortable, searchable tables (`webroot/js/datatables-setup.js`) |
| Chart.js | 4.6.1 | MIT | invoice charts (`webroot/js/charts.js`) |
| Jodit | 4.20.3 | MIT | email editor (`webroot/js/editor.js`) |

PDFs use [tFPDF](https://github.com/Setasign/tFPDF) with the DejaVu font (`resources/fonts/`).

## Credits

- **Adaptive Client Manager** was originally written by **Dead Pixel Studio**
  (deadpixelstudio.co.uk, no longer active), including the application, its design and the
  "Dead Pixel" theme. This project is a modernised continuation of their work, and the
  "Powered by Dead Pixel Studio" footer is kept in their honour.
- **Icon font** (`webroot/fonts/dps-admin.*`): built with [IcoMoon](https://icomoon.io) from the
  IcoMoon Free (CC BY-SA 3.0), Broccolidry and Iconic (CC BY-SA 3.0) icon sets; see
  `webroot/fonts/license.txt`.
- **Fonts**: Ostrich Sans and Titillium Text (web fonts in `webroot/fonts/`); DejaVu Sans Condensed
  for PDFs (`resources/fonts/unifont/DejaVu_LICENSE.txt`).
- Built with [CakePHP](https://cakephp.org), [tFPDF](https://github.com/Setasign/tFPDF),
  [jQuery](https://jquery.com), [DataTables](https://datatables.net),
  [Chart.js](https://www.chartjs.org) and [Jodit](https://xdsoft.net/jodit/), plus a patched copy
  of ZURB's jQuery Reveal plugin (`webroot/js/jquery.reveal.js`).
