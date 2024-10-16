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
        $customerValue = $request->query('customerfilter');
        $typeValue = $request->query('typefilter');

        try {

            $searchParameters = [];
            if ($customerValue != "all"){
                $customerFilter = "customerid = :searchcustomer";
                $searchParameters[':searchcustomer'] = $customerValue;
            }
            if ($typeValue == "all"){
                $typeFilter = "id LIKE :searchid OR description LIKE :searchdescription OR person LIKE :searchperson";
                $searchParameters[':searchid'] = '%' . $searchValue . '%';
                $searchParameters[':searchdescription'] = '%' . $searchValue . '%';
                $searchParameters[':searchperson'] = '%' . $searchValue . '%';
            }
            else if ($typeValue == "person"){
                $typeFilter = "person LIKE :searchperson";
                $searchParameters[':searchperson'] = '%' . $searchValue . '%';
            }
            else if ($typeValue == "ticketid"){
                $typeFilter = "id LIKE :searchid";
                $searchParameters[':searchid'] = '%' . $searchValue . '%';
            }
            else if ($typeValue == "briefdescription"){
                $typeFilter = "briefdescription LIKE :searchdescription";
                $searchParameters[':searchdescription'] = '%' . $searchValue . '%';
            }

            $whereFilter = "WHERE $typeFilter";
            if (isset($customerFilter)){
                $whereFilter = "WHERE ".join(" AND ",[$customerFilter,'('.$typeFilter.')']);
            }

            // Prepare the SQL query
            $dbQuery = $dbConnection->prepare(
                "SELECT TOP 1000 *
                FROM (
                    SELECT unid AS topdesk_id, naam AS id, aanmeldervestigingid AS customerid, korteomschrijving AS description, status, aanmeldernaam AS person, 'ticket' AS type FROM incident
                    UNION ALL
                    SELECT unid AS topdesk_id, [number] AS id, aanmeldervestigingid AS customerid, briefdescription AS description, status, aanmeldernaam AS person, 'change' AS type FROM [change]
                    UNION ALL
                    SELECT unid AS topdesk_id, [number] AS id, '60349ef8-9de4-4c56-809c-31ca6f4962c4' AS customerid, briefdescription AS description, status, '' AS person, 'changeactivity' AS type FROM changeactivity
                ) AS data
                $whereFilter
                ORDER BY id DESC" 
            );

            // Execute the query with the search value
            $dbQuery->execute($searchParameters);
            
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
                        "SELECT incident.unid, 
                        incident.ref_vestiging, 
                        incident.naam, 
                        incident.status,
                        incident.aanmeldernaam, 
                        incident.aanmelderemail, 
                        incident.korteomschrijving, 
                        incident.ref_soortmelding, 
                        incident.ref_domein, 
                        incident.ref_specificatie,
                        CONVERT(VARCHAR, incident.dataanmk, 120) AS dataanmk,
                        incident.ref_operatorgroup,
                        incident.ref_operatordynanaam,
                        CONVERT(VARCHAR, incident.datumgereed, 120) AS datumgereed,
                        CONVERT(VARCHAR, incident.datumafspraaksla, 120) AS datumafspraaksla,
                        incident.ref_status,
                        impact.naam AS impact, 
                        urgency.naam AS urgency,
                        priority.naam AS priority
                        FROM incident LEFT OUTER JOIN 
                        impact ON incident.impactid = impact.unid LEFT OUTER JOIN 
                        urgency ON incident.urgencyid = urgency.unid LEFT OUTER JOIN 
                        priority ON incident.priorityid = priority.unid
                        WHERE incident.naam = :id"
                    );

                    // Execute the query with the search value
                    $dbQuery->execute([':id' => $id]);
                    
                    $ticket = $dbQuery->fetch(\PDO::FETCH_ASSOC);

                    // Check again if ticket is not false
                    if ($ticket) {
                        // Return the ticket results in the view
                        return view('tickets', ['ticket' => $ticket]);
                    }
                } 
                else if ($ticket['type'] == 'change') {

                    $dbQuery = $dbConnection->prepare(
                        "SELECT change.unid, 
                        change.ref_caller_branch_name, 
                        change.changetype,
                        change.number, 
                        change.status,
                        change.aanmeldernaam, 
                        change.aanmelderemail, 
                        change.briefdescription, 
                        change.ref_type_name, 
                        change.ref_category_name, 
                        change.ref_subcategory_name,
                        CONVERT(VARCHAR, change.completeddate, 120) AS completeddate, 
                        operatorgroup.ref_dynanaam AS ref_operatorgroupname,
                        operator.ref_dynanaam AS ref_operatorname,
                        status.naam AS ref_status_name,
                        CONVERT(VARCHAR, change.dataanmk, 120) AS dataanmk, 
                        impact.naam AS impact, 
                        changebenefit.naam AS benefit,
                        priority.naam AS priority
                        FROM change LEFT OUTER JOIN
                        actiedoor AS operator ON change.operatorid = operator.unid LEFT OUTER JOIN
                        actiedoor AS operatorgroup ON change.operatorgroupid = operatorgroup.unid LEFT OUTER JOIN
                        wijzigingstatus AS status ON change.statusid = status.unid LEFT OUTER JOIN 
                        wijziging_impact AS impact ON change.impactid = impact.unid LEFT OUTER JOIN 
                        changebenefit ON change.benefitid = changebenefit.unid LEFT OUTER JOIN 
                        change_priority AS priority ON change.priorityid = priority.unid
                        WHERE change.number = :id"
                    );

                    // Execute the query with the search value
                    $dbQuery->execute([':id' => $id]);
                    
                    $ticket = $dbQuery->fetch(\PDO::FETCH_ASSOC);

                    // Check again if ticket is not false
                    if ($ticket) {
                        return view('changes', ['ticket' => $ticket]);
                    }
                } 
                else if ($ticket['type'] == 'changeactivity') {
                    $dbQuery = $dbConnection->prepare(
                        "SELECT changeactivity.unid, 
                        changeactivity.number, 
                        changeactivity.status,
                        changeactivity.briefdescription, 
                        CONVERT(VARCHAR, changeactivity.dataanmk, 120) AS dataanmk,
                        changeactivity.ref_change_number,
                        changeactivity.ref_change_brief_description,
                        CONVERT(VARCHAR, changeactivity.resolveddate, 120) AS resolveddate,
                        operator.ref_dynanaam AS ref_operatorname,
                        status.naam AS ref_status_name,
                        change.ref_caller_branch_name
                        FROM changeactivity LEFT OUTER JOIN
                        actiedoor AS operator ON changeactivity.operatorid = operator.unid LEFT OUTER JOIN
                        changeactivity_status AS status ON changeactivity.activitystatusid = status.unid LEFT OUTER JOIN
                        change ON changeactivity.changeid = change.unid
                        WHERE changeactivity.number = :id"
                    );

                    // Execute the query with the search value
                    $dbQuery->execute([':id' => $id]);
                    
                    $ticket = $dbQuery->fetch(\PDO::FETCH_ASSOC);

                    // Check again if ticket is not false
                    if ($ticket) {
                        return view('changeactivities', ['ticket' => $ticket]);
                    }
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

    public function requests(Request $request, DatabaseController $Database){
    
        $dbConnection = $Database->connect();

        $id = $request->query('unid');
        $type = $request->query('type');

        try {

            if ($type == 'ticket'){
                $dbQuery = $dbConnection->prepare(
                    "SELECT comments.memotekst, CONVERT(VARCHAR,comments.dataanmk,120) AS dataanmk, comments.invisibleforcaller, comments.origin, comments.veldnaam, operator.naam
                    FROM [topdesk].[dbo].[incident__memogeschiedenis] AS comments LEFT OUTER JOIN
                    gebruiker AS operator ON comments.gebruikerid = operator.unid
                    WHERE parentid = :id AND veldnaam = 'VERZOEK'
                    ORDER BY comments.dataanmk DESC"
                );
            }
            else if ($type == 'change'){
                $dbQuery = $dbConnection->prepare(
                    "SELECT comments.memotekst, CONVERT(VARCHAR,comments.dataanmk,120) AS dataanmk, comments.invisibleforcaller, comments.origin, comments.veldnaam, operator.naam
                    FROM [topdesk].[dbo].[change__memo_history] AS comments LEFT OUTER JOIN
                    gebruiker AS operator ON comments.gebruikerid = operator.unid
                    WHERE parentid = :id AND veldnaam = 'DESCRIPTION'
                    ORDER BY comments.dataanmk DESC"
                );
            }
            else if ($type == 'changeactivity'){
                $dbQuery = $dbConnection->prepare(
                    "SELECT comments.memotekst, CONVERT(VARCHAR,comments.dataanmk,120) AS dataanmk, comments.invisibleforcaller, comments.origin, comments.veldnaam, operator.naam
                    FROM [topdesk].[dbo].[changeactivity__memo_history] AS comments LEFT OUTER JOIN
                    gebruiker AS operator ON comments.gebruikerid = operator.unid
                    WHERE parentid = :id AND veldnaam = 'DESCRIPTION'
                    ORDER BY comments.dataanmk DESC"
                );
            }
            else {
                return;
            }

            // Execute the query with the search value
            $dbQuery->execute([':id' => $id]);
            
            // Fetch the results
            $ticketData = $dbQuery->fetchAll(\PDO::FETCH_ASSOC);

            // Ensure $ticket is not false
            if ($ticketData) {
                return view('api.requests', ['requests' => $ticketData]);
            } else {
                return response()->json(['error' => 'No requests found.'], 404);
            }
        } catch (\PDOException $e) {
            // Log the error and return an error response
            \Log::error('Query failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while searching tickets.'], 500);
        }
    }

    public function comments(Request $request, DatabaseController $Database){
    
        $dbConnection = $Database->connect();
        
        $id = $request->query('unid');
        $type = $request->query('type');

        try {

            if ($type == 'ticket'){
                $dbQuery = $dbConnection->prepare(
                    "SELECT comments.memotekst, CONVERT(VARCHAR,comments.dataanmk,120) AS dataanmk, comments.invisibleforcaller, comments.origin, comments.veldnaam, operator.naam
                    FROM [topdesk].[dbo].[incident__memogeschiedenis] AS comments LEFT OUTER JOIN
                    gebruiker AS operator ON comments.gebruikerid = operator.unid
                    WHERE parentid = :id AND veldnaam = 'ACTIE'
                    ORDER BY comments.dataanmk DESC"
                );
            }
            else if ($type == 'change'){
                $dbQuery = $dbConnection->prepare(
                    "SELECT comments.memotekst, CONVERT(VARCHAR,comments.dataanmk,120) AS dataanmk, comments.invisibleforcaller, comments.origin, comments.veldnaam, operator.naam
                    FROM [topdesk].[dbo].[change__memo_history] AS comments LEFT OUTER JOIN
                    gebruiker AS operator ON comments.gebruikerid = operator.unid
                    WHERE parentid = :id AND veldnaam = 'ACTION'
                    ORDER BY comments.dataanmk DESC"
                );
            }
            else if ($type == 'changeactivity'){
                $dbQuery = $dbConnection->prepare(
                    "SELECT comments.memotekst, CONVERT(VARCHAR,comments.dataanmk,120) AS dataanmk, comments.invisibleforcaller, comments.origin, comments.veldnaam, operator.naam
                    FROM [topdesk].[dbo].[changeactivity__memo_history] AS comments LEFT OUTER JOIN
                    gebruiker AS operator ON comments.gebruikerid = operator.unid
                    WHERE parentid = :id AND veldnaam = 'ACTION'
                    ORDER BY comments.dataanmk DESC"
                );
            }
            else {
                return;
            }

            // Execute the query with the search value
            $dbQuery->execute([':id' => $id]);
            
            // Fetch the results
            $ticketData = $dbQuery->fetchAll(\PDO::FETCH_ASSOC);

            // Ensure $ticket is not false
            if ($ticketData) {
                return view('api.comments', ['comments' => $ticketData]);
            } else {
                return response()->json(['error' => 'No comments found.'], 404);
            }
        } catch (\PDOException $e) {
            // Log the error and return an error response
            \Log::error('Query failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while searching tickets.'], 500);
        }
    }
    public function changeactivities($unid, DatabaseController $Database){
        $dbConnection = $Database->connect();

        try {
            $dbQuery = $dbConnection->prepare(
                "SELECT number,briefdescription, status.naam AS status
                FROM changeactivity LEFT OUTER JOIN
                changeactivity_status AS status ON changeactivity.activitystatusid = status.unid
                WHERE changeid = :unid"
            );

            // Execute the query with the search value
            $dbQuery->execute([':unid' => $unid]);
            
            // Fetch the results
            $changeactivities = $dbQuery->fetchAll(\PDO::FETCH_ASSOC);

            // Ensure $ticket is not false
            if ($changeactivities) {
                return view('api.activities', ['changeactivities' => $changeactivities]);
            }
            else {
                return response()->json(['error' => 'This change has no activities.'], 404);
            }
        } catch (\PDOException $e) {
            // Log the error and return an error response
            \Log::error('Query failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while retrieving changeactivities.'], 500);
        }
    }

    public function customers(DatabaseController $Database){
        $dbConnection = $Database->connect();

        try {
            $dbQuery = $dbConnection->prepare(
                "SELECT unid,naam,status
                FROM vestiging
                ORDER BY naam"
            );

            // Execute the query with the search value
            $dbQuery->execute();
            
            // Fetch the results
            $customers = $dbQuery->fetchAll(\PDO::FETCH_ASSOC);

            // Ensure $customers is not false
            if ($customers) {
                return view('api.customers', ['customers' => $customers]);
            }
            else {
                return response()->json(['error' => 'Found no customers.'], 404);
            }
        } catch (\PDOException $e) {
            // Log the error and return an error response
            \Log::error('Query failed: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while retrieving customers.'], 500);
        }
    }
}