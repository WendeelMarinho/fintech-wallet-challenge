<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'API em /api. Frontend: npm run dev (Vite em outra porta).',
    ]);
});

Route::get('/login', function () {
    return response()->json(['message' => 'Não autenticado.'], 401);
})->name('login');
