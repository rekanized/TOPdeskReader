<?php

namespace App\Http\Controllers;

use PDO as PDO;

class DatabaseController extends Controller
{
    public function connect(){
        try {
            $conn = new PDO(
                "sqlsrv:server=".env('MSSQL_SERVER') . ";Database=".env('MSSQL_DATABASE'),
                env('MSSQL_USERNAME'),
                env('MSSQL_PASSWORD')
            );
            // Set error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        } catch (\PDOException $e) {
            // Log the error for better error tracking
            \Log::error("Database connection failed: " . $e->getMessage());

            // Return null or false so that the calling code can handle the failure
            return null;
        }
    }
}