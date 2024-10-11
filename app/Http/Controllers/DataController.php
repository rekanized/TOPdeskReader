<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\DatabaseController;

class DataController extends Controller
{
    public function search(Request $request, DatabaseController $DatabaseController){
        // Use DatabaseController directly without assigning to $this
        $dbConnection = $DatabaseController->connect();

        if (!$dbConnection) {
            // Handle the case where the connection fails
            return response()->json(['error' => 'Database connection failed'], 500);
        }

        // Get the search value from the query string
        $searchValue = $request->query('searchvalue');

        try {
            // Prepare the SQL query
            $dbQuery = $dbConnection->prepare(
                "SELECT TOP 10 *
                FROM (
                    SELECT unid AS topdesk_id, naam AS id, korteomschrijving AS description, 'ticket' AS type FROM incident
                    UNION ALL
                    SELECT unid AS topdesk_id, [number] AS id, briefdescription AS description, 'change' AS type FROM [change]
                    UNION ALL
                    SELECT unid AS topdesk_id, [number] AS id, briefdescription AS description, 'changeactivity' AS type FROM changeactivity
                ) AS data
                WHERE id LIKE :searchvalue"
            );

            // Execute the query with the search value
            $dbQuery->execute([':searchvalue' => '%' . $searchValue . '%']);
            
            // Fetch all the results
            $allTickets = $dbQuery->fetchAll(\PDO::FETCH_ASSOC);

            // Return the search results in the view
            return view('api.search', ['allTickets' => $allTickets]);

        } catch (\PDOException $e) {
            // Log the error and return an error response
            \Log::error('Query failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while searching tickets.'], 500);
        }
    }

    public function show($id, DatabaseController $Database){
    
        $dbConnection = $Database->connect();
        
        try {
            // Prepare the SQL query
            $dbQuery = $dbConnection->prepare(
                "SELECT TOP 1 *
                FROM (
                    SELECT unid AS topdesk_id, naam AS id, korteomschrijving AS description, 'ticket' AS type FROM incident
                    UNION ALL
                    SELECT unid AS topdesk_id, [number] AS id, briefdescription AS description, 'change' AS type FROM [change]
                    UNION ALL
                    SELECT unid AS topdesk_id, [number] AS id, briefdescription AS description, 'changeactivity' AS type FROM changeactivity
                ) AS data
                WHERE id = :id"
            );

            // Execute the query with the search value
            $dbQuery->execute([':id' => $id]);
            
            // Fetch the results
            $ticket = $dbQuery->fetch(\PDO::FETCH_ASSOC);

            // Ensure $ticket is not false
            if ($ticket) {
                if ($ticket['type'] == 'ticket') {
                    $dbQuery = $dbConnection->prepare(
                        "SELECT TOP 1 * 
                        FROM incident
                        WHERE naam = :id"
                    );

                    // Execute the query with the search value
                    $dbQuery->execute([':id' => $id]);
                    
                    $ticket = $dbQuery->fetch(\PDO::FETCH_ASSOC);

                    // Check again if ticket is not false
                    if ($ticket) {
                        // Return the ticket results in the view
                        return view('tickets', ['ticket' => $ticket]);
                    }
                } else if ($ticket['type'] == 'change') {
                    // Handle change logic
                    return view('tickets', ['changes' => $change]);
                } else if ($ticket['type'] == 'changeactivity') {
                    // Handle change activity logic
                    return view('tickets', ['changeactivities' => $changeactivity]);
                }
            } else {
                return response()->json(['error' => 'No ticket found.'], 404);
            }
        } catch (\PDOException $e) {
            // Log the error and return an error response
            \Log::error('Query failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while searching tickets.'], 500);
        }
    }
}