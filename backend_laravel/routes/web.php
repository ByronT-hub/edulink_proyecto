<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Incluir rutas de prueba temporales
require __DIR__.'/test_routes.php';
