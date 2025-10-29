<?php

use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
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
    Route::controller(ServiceController::class)->group(function () {
        Route::get('/services', 'getServices');
        Route::get('/services/{id}', 'getServiceById');
        Route::post('/services', 'addService');
        Route::put('/services/{id}', 'updateServiceById');
        Route::delete('/services/{id}', 'deleteServiceById');
    });
    Route::controller(EmployeesController::class)->group(function () {
        Route::get('/employees', 'getEmployees');
        Route::get('/employee/{id}', 'getEmployeeById');
        Route::post('/employees', 'addEmployee');
        Route::patch('/employee/{id}', 'updateEmployeeById');
        Route::delete('/employee/{id}', 'deleteEmployeeById');
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
