<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController;

Route::get('/', function () {
    return view('home');
});

Route::get('/tickets', [DataController::class, 'search']);

Route::get('/tickets/{id}', [DataController::class, 'show']);

Route::get('/api/comments', [DataController::class, 'comments']);
