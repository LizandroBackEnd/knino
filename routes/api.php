<?php

use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BreedController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ServiceController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsReceptionist;
use App\Http\Middleware\IsVeterinarian;
use App\Models\Breed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//public routes
Route::post('/register', [AuthController::class,  'register']);
Route::post('/login', [AuthController::class,  'login']);
Route::post('/logout', [AuthController::class, 'logout']);

//private routes
Route::middleware([IsAdmin::class])->group((function () {
    Route::controller(ServiceController::class)->group(function () {
        Route::get('/services', 'getServices');
        Route::get('/services/{name}', 'getServiceByName');
        Route::post('/services', 'addService');
        Route::patch('/services/{id}', 'updateServiceById');
        Route::delete('/services/{id}', 'deleteServiceById');
    });
    Route::controller(EmployeesController::class)->group(function () {
        Route::get('/employees', 'getEmployees');
        Route::get('/employees/{email}', 'getEmployeeByEmail');
        Route::post('/employees', 'addEmployee');
        Route::patch('/employees/{id}', 'updateEmployeeById');
        Route::delete('/employees/{id}', 'deleteEmployeeById');
    });
    Route::controller(ClientController::class)->group(function () {
        Route::get('/clients', 'getClients');
        Route::get('/clients/{email}', 'getClientByEmail');
        Route::post('/clients', 'addClient');
        Route::patch('/clients/{id}', 'updateClientById');
        Route::delete('/clients/{id}', 'deleteClientById');
    });
    Route::controller(BreedController::class)->group(function () {
        Route::get('/breed/{species}', 'getBreedBySpecies');
        Route::post('/breed', 'addBreed');
    });
    Route::controller(PetController::class)->group(function () {
        Route::get('/pets', 'getPets');
        Route::get('/pets/{name}', 'getPetByName');
        Route::post('/pets', 'addPet');
        Route::patch('/pets/{id}', 'updatePetById');
        Route::delete('/pets/{id}', 'deletePetById');
    });
}));

Route::middleware([IsReceptionist::class])->group((function () {
    Route::controller(ClientController::class)->group(function () {
        Route::get('/clients', 'getClients');
        Route::get('/clients/{email}', 'getClientByEmail');
        Route::post('/clients', 'addClient');
        Route::patch('/clients/{id}', 'updateClientById');
        Route::delete('/clients/{id}', 'deleteClientById');
    });
    Route::controller(BreedController::class)->group(function () {
        Route::get('/breeds/{species}', 'getBreedBySpecies');
        Route::post('/breeds', 'addBreed');
    });
    Route::controller(PetController::class)->group(function () {
        Route::get('/pets', 'getPets');
        Route::get('/pets/{name}', 'getPetByName');
        Route::post('/pets', 'addPet');
        Route::patch('/pets/{id}', 'updatePetById');
        Route::delete('/pets/{id}', 'deletePetById');
    });
}));


Route::middleware([IsVeterinarian::class])->group((function () {}));
