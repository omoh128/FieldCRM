<?php

use Illuminate\Support\Facades\Route;

// Required so Laravel doesn't throw RouteNotFoundException
// when Sanctum redirects unauthenticated API requests
Route::get('/login', fn() => response()->json(['message' => 'Unauthenticated.'], 401))
    ->name('login');

Route::view('/{any}', 'app')->where('any', '.*');
