<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');

});

Route::get('/dashboard' , function () {
    return view('dashboard.admin');
})->name('dashboard');

Route::get('/clients', function () {
    return view('dashboard.clients');
})->name('clientes');

Route::get('/pets', function () {
    return view('dashboard.pets');
})->name('mascotas');