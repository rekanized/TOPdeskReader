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
2. Install Microsoft's [PHP SQL Server drivers](https://github.com/microsoft/msphpsql/releases) version 5.13 or newer and the required Microsoft ODBC driver. Enable `pdo_sqlsrv` in the same PHP 8.5 runtime. On Windows, match the extension's architecture and thread safety to your PHP build. On Linux and macOS, follow [Microsoft's installation guide](https://github.com/microsoft/msphpsql/blob/dev/Linux-mac-install.md).
3. Check the runtime and install the locked PHP packages:

   ```sh
   php -v
   php -m
   composer install
   composer check-platform-reqs
   ```

4. Copy `.env.example` to `.env` and set `MSSQL_SERVER`, `MSSQL_DATABASE`, `MSSQL_USERNAME`, and `MSSQL_PASSWORD`. The app uses this SQL Server connection to read TOPdesk data. Laravel uses its separate SQLite connection for sessions and cache.
5. Generate the application key and create Laravel's SQLite tables:

   ```sh
   php artisan key:generate
   php artisan migrate
   ```

6. Run `php artisan serve`. For a web server deployment, point the document root at `public/`.

Run `php artisan topdesk:check` on the server to verify that the PHP driver connects to the configured database and can read the tables used by the reader. This command only issues `SELECT` queries. Then search for a known ticket and open its detail page to check the data and column-specific queries against your TOPdesk version. If you cache Laravel's configuration, run `php artisan config:cache` again after changing the MSSQL settings in `.env`.

Run `php artisan test` to check the application without a TOPdesk database. The pages use static assets in `public/` and require no asset build step.
