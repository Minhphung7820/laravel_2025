<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'user'], function () {
  Route::get('/list', [UserController::class, 'index']);
  Route::get('/advanced-list', [UserController::class, 'advancedEngagement']);
});
Route::group(['prefix' => 'product'], function () {
  Route::get('/recommend', [ProductController::class, 'recommend']);
});

Route::group(['prefix' => 'report'], function () {
  Route::get('/top-customers', [ReportController::class, 'topCustomers']);
  Route::get('/top-products', [ReportController::class, 'topProducts']);
});
