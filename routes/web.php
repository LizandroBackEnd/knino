<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');

});
// Dashboard grouped routes: render the corresponding dashboard view.
// These return the full layout on normal requests and only the content fragment
// when requested via X-Requested-With: XMLHttpRequest (our router fetches it).
Route::get('/dashboard', function () {
    return view('dashboard.admin');
})->name('dashboard.home');

Route::get('/dashboard/clientes', function () {
    return view('dashboard.clients');
})->name('dashboard.clientes');

// Show create page by reusing the same view but toggling the create form component
Route::get('/dashboard/clientes/create', function () {
    return view('dashboard.clients', ['showCreate' => true]);
})->name('dashboard.clientes.create');

Route::get('/dashboard/mascotas', function () {
    return view('dashboard.pets');
})->name('dashboard.mascotas');

// Show create page for pets
Route::get('/dashboard/mascotas/create', function () {
    return view('dashboard.pets', ['showCreate' => true]);
})->name('dashboard.mascotas.create');

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