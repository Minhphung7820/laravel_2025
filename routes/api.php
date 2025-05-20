<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'user'], function () {
  Route::get('/list', [UserController::class, 'index']);
  Route::get('/advanced-list', [UserController::class, 'advancedEngagement']);
});
