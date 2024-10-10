<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncidentController;

Route::get('/', function () {
    return view('home');
});

Route::get('/incidents', [IncidentController::class, 'index']);
