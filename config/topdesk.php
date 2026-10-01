<?php

return [
    'server' => env('MSSQL_SERVER', ''),
    'database' => env('MSSQL_DATABASE', ''),
    'username' => env('MSSQL_USERNAME', ''),
    'password' => env('MSSQL_PASSWORD', ''),
    'trust_server_certificate' => env('MSSQL_TRUST_SERVER_CERTIFICATE', true),
];
