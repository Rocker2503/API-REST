<?php

use App\Http\Controllers\UserController;

Route::get('/', function () {

    return view('welcome');
});

Route::get('/login', [UserController::class, 'login']);
Route::get('/new', [UserController::class, 'new']);
Route::get('/me', [UserController::class, 'me']);
