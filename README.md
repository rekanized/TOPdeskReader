## Feel free to buy me a coffee if you use this =)
https://buymeacoffee.com/rekanized

## Welcome to deskTOP (This is a TOPdesk Database Reader)
This project is created since our company has a old TOPdesk instance that we need to be able to read.<br>
This lets you read Tickets, Changes and ChangeActivites at the moment.<br>
<br>
You need to configure the database in the .env file, look at .env example.<br>
These lines needs to be configured (you need a On-Prem MSSQL database for TOPdesk for this to work)<br>
<br>
```
MSSQL_SERVER=""
MSSQL_DATABASE=""
MSSQL_USERNAME=""
MSSQL_PASSWORD=""
```
<br>
<br>
If you have any questions feel free to ask for assistance.

## Example Preview
![deskTOP](https://github.com/user-attachments/assets/8858dc49-ac20-4c51-b095-d50f18f69d47)


## Installation

1. Install PHP 8.5 and Composer. Enable `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_sqlite`, `sqlite3`, and the XML extensions in the PHP runtime used by the application.
2. Install [Microsoft ODBC Driver for SQL Server 17 or 18](https://learn.microsoft.com/sql/connect/odbc/linux-mac/installing-the-microsoft-odbc-driver-for-sql-server). The `msodbcsql18` or `msodbcsql17` system package is required by `pdo_sqlsrv`; `unixODBC-devel` alone does not provide it. On Linux, confirm that `odbcinst -q -d` lists `ODBC Driver 18 for SQL Server` or `ODBC Driver 17 for SQL Server`. On Linux and macOS, also install the PHP development and build tools described in [Microsoft's installation guide](https://learn.microsoft.com/sql/connect/php/installation-tutorial-linux-mac). Linux builds need the unixODBC development headers (`sql.h`): install `unixodbc-dev` on Debian/Ubuntu or Alpine, or `unixODBC-devel` on RHEL/Fedora. On macOS, install `unixodbc` with Homebrew. These system packages require an OS package manager and usually administrator access; Composer cannot install them.
3. Install the locked PHP packages and the Microsoft PDO SQL Server extension:

   ```sh
   composer install
   ```

   The Composer install script downloads a verified [PIE](https://github.com/php/pie) release, installs `microsoft/pdo_sqlsrv` 5.13.3 for the PHP 8.5 runtime running Composer, and verifies that a new PHP process can load the driver. The install fails if the driver cannot be enabled. Run Composer with the same PHP installation used to serve the app. Composer's `--no-scripts` option skips this setup.

   Restart PHP-FPM after the extension is enabled. CLI PHP starts fresh for each command, but existing PHP-FPM workers will not see a newly added ini file until the service restarts. On a typical RHEL installation, run `sudo systemctl restart php-fpm`; check the actual service name on your host.

   On Linux, PDO must load before `pdo_sqlsrv`. The installer enables the driver in `zz-pdo_sqlsrv.ini` in PHP's scanned ini directory and removes a misplaced entry from the main `php.ini`. It may ask for sudo access if those files are system-owned. If PHP still reports `undefined symbol: php_pdo_unregister_driver`, run `php --ini` and check that `zz-pdo_sqlsrv.ini` is readable (mode `0644`) and loads after the ini file enabling PDO.

4. Copy `.env.example` to `.env` and set `MSSQL_SERVER`, `MSSQL_DATABASE`, `MSSQL_USERNAME`, and `MSSQL_PASSWORD`. The app uses this SQL Server connection to read TOPdesk data. Laravel uses its separate SQLite connection for sessions and cache.
5. Generate the application key and create Laravel's SQLite tables:

   ```sh
   php artisan key:generate
   php artisan migrate
   ```

6. Run `php artisan serve`. For a web server deployment, point the document root at `public/`.

Run `php artisan topdesk:check` on the server to verify that the PHP driver connects to the configured database and can read the tables used by the reader. This command only issues `SELECT` queries. Then search for a known ticket and open its detail page to check the data and column-specific queries against your TOPdesk version. If you cache Laravel's configuration, run `php artisan config:cache` again after changing the MSSQL settings in `.env`.

If `php -m` lists `pdo_sqlsrv` but a web request logs `could not find driver`, check whether `php artisan topdesk:check` succeeds. A CLI success with a web failure means the web PHP runtime needs the extension enabled or restarted. Restart the PHP-FPM service (often `sudo systemctl restart php-fpm` on RHEL-based systems) or the Apache PHP process serving the app. The Laravel error log records that process's PHP SAPI, version, loaded and scanned ini files, and available PDO drivers for this error, without logging database credentials.

Run `php artisan test` to check the application without a TOPdesk database. The pages use static assets in `public/` and require no asset build step.

Laravel must be able to write to `storage` and `bootstrap/cache`. With this project's default database cache and sessions, it must also write to `database/database.sqlite` and the `database` directory, where SQLite creates journal files. Ensure both the deployment user running Artisan and the web PHP user have write access to these paths. If Artisan reports `Permission denied` or `attempt to write a readonly database`, correct their ownership or shared group permissions before retrying; avoid making them world-writable.
