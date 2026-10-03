<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test', function () {
    return response()->json([
        'message' => 'Laravel B2 Integration API',
        'version' => '1.0',
        'timestamp' => now()
    ]);
});