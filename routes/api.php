<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/wallet', [WalletController::class, 'balance']);
    Route::post('/transfer', [TransferController::class, 'transfer']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/latest', [TransactionController::class, 'latest']);
});
