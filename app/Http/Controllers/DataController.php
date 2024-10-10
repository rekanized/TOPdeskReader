<?php

namespace App\Http\Controllers;

use PDO as PDO;

class DataController extends Controller
{
    public function index(){

        // Connect to Database
        try {
            $conn = new PDO("sqlsrv:server=".env('MSSQL_SERVER').";Database=".env('MSSQL_DATABASE'), env('MSSQL_USERNAME'), env('MSSQL_PASSWORD'));
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        $allTickets = [];

        // Get all tickets
        $dbQuery = $conn->prepare("SELECT TOP 10 unid AS topdesk_id,naam AS id,'ticket' AS type FROM incident");
        $dbQuery->execute();
        array_push($allTickets,$dbQuery->fetchAll(PDO::FETCH_ASSOC));

        // Get all changes
        $dbQuery = $conn->prepare("SELECT TOP 10 unid AS topdesk_id,[number] AS id,'change' AS type FROM change");
        $dbQuery->execute();
        array_push($allTickets,$dbQuery->fetchAll(PDO::FETCH_ASSOC));

        // Get all changeactivities
        $dbQuery = $conn->prepare("SELECT TOP 10 unid AS topdesk_id,[number] AS id,'changeactivity' AS type FROM changeactivity");
        $dbQuery->execute();
        array_push($allTickets,$dbQuery->fetchAll(PDO::FETCH_ASSOC));

        return view('api.search', compact('allTickets'));
    }
}