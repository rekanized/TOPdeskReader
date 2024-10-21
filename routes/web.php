<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController;

Route::get('/', function () {
    return view('home');
});

Route::get('/tickets', [DataController::class, 'search']);

Route::get('/tickets/{id}', [DataController::class, 'show']);

Route::get('/api/comments', [DataController::class, 'comments']);

Route::get('/api/requests', [DataController::class, 'requests']);

Route::get('/api/customers', [DataController::class, 'customers']);

Route::get('/api/exporter', [DataController::class, 'ticketexporter']);

Route::get('/toggleDarkMode', function() {
    if (session()->has('dark_mode')) {
        session()->forget('dark_mode');
    } else {
        session(['dark_mode' => true]);
    }

    return redirect()->back();
});

Route::get('/api/tickets/{unid}/activities', [DataController::class, 'changeactivities']);
