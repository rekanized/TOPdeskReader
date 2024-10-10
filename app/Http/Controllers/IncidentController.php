<?php

namespace App\Http\Controllers;

class IncidentController extends Controller
{
    public function index(){
        $Incidents = "";
        return view('home', compact('Incidents'));
    }
}