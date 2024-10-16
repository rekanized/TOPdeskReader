## TOPdesk Reader (Read an old TOPdesk database)
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
![TOPdeskReader](https://github.com/user-attachments/assets/dc9de91d-9324-4a4d-9c53-97bd49408874)

## Installation
* Run ``composer install`` from the root directory
* Copy the ".env.example" file and name it ".env" and then edit the file and add your database info to the MSSQL parameters (bottom of the file)
* Run ``php artisan key:generate`` from the root directory (this generates your APP_KEY in the .env file)
* Run ``php artisan serve`` from the root directory to see if it runs correctly, then publish it in your favorite WebServer (Nginx/Apache) the root directory to have in the webserver config = '/public'
* Download the SQL Server Drivers
Download the appropriate drivers for your platform (Windows, Linux, or macOS) from Microsoft's official GitHub repository:
<b>Windows:</b><br>
    * Go to <a href="https://github.com/microsoft/msphpsql/releases">Microsoft Drivers for PHP for SQL Server.</a><br>
    * Download the latest release for your PHP version (make sure to match your PHP version, like PHP 7.4, 8.0, etc.).
<b>Linux/macOS:</b><br>
    * Follow <a href="https://docs.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac?view=sql-server-ver15">Microsoft's documentation</a> to install ODBC drivers and configure SQLSRV and PDO_SQLSRV extensions.
