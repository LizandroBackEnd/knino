<?php

use App\Http\Middleware\Role;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', function () {
    return view('auth.login');
})->name('login');



Route::get('/dashboard', function () {
    return view('dashboard.admin');
})->name('dashboard.home');


Route::get('/dashboard/servicios', function () {
    return view('dashboard.services');
})->name('dashboard.servicios');
Route::get('/dashboard/servicios/create', function () {
    return view('dashboard.services', ['showCreate' => true]);
})->name('dashboard.servicios.create');

Route::get('/dashboard/empleados', function () {
    return view('dashboard.employees');
})->name('dashboard.empleados');
Route::get('/dashboard/empleados/create', function () {
    return view('dashboard.employees', ['showCreate' => true]);
})->name('dashboard.empleados.create');


Route::get('/dashboard/users', function () {
    return view('dashboard.users');
})->name('dashboard.users');
Route::get('/dashboard/users/create', function () {
    return view('dashboard.users', ['showCreate' => true]);
})->name('dashboard.users.create');


Route::get('/dashboard/clientes', function () {
    return view('dashboard.clients');
})->name('dashboard.clientes');
Route::get('/dashboard/clientes/create', function () {
    return view('dashboard.clients', ['showCreate' => true]);
})->name('dashboard.clientes.create');

Route::get('/dashboard/mascotas', function () {
    return view('dashboard.pets');
})->name('dashboard.mascotas');
Route::get('/dashboard/mascotas/create', function () {
    return view('dashboard.pets', ['showCreate' => true]);
})->name('dashboard.mascotas.create');
