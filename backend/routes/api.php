<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\PointController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('mock.jwt')->group(function () {
  Route::get('/profile', [AuthController::class, 'me']);
  Route::put('/profile/update', [AuthController::class, 'updateProfile']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/change-password', [AuthController::class, 'changePassword']);
  Route::post('/refresh', [AuthController::class, 'refresh']);
  Route::get('/rewards', [RewardController::class, 'index']);
  Route::get('/rewards/categories', [RewardController::class, 'categories']);
  Route::get('/rewards/categories/{category}', [RewardController::class, 'byCategory']);
  Route::get('/rewards/{id}', [RewardController::class, 'show']);
  Route::post('/rewards/redeem', [RewardController::class, 'redeem']);
  Route::get('/rewards/history', [RewardController::class, 'history']);
  Route::post('/topup', [PointController::class, 'topup']);
  Route::get('/topup/history', [PointController::class, 'history']);
});
