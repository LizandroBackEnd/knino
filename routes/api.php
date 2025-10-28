<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsReceptionist;
use App\Http\Middleware\IsVeterinarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//public routes 
Route::post('/register', [AuthController::class,  'register']);
Route::post('/login', [AuthController::class,  'login']);

//private routes
Route::middleware([IsAdmin::class])->group((function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
    });
}));

Route::middleware([IsReceptionist::class])->group((function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
    });
}));


Route::middleware([IsVeterinarian::class])->group((function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
    });
}));