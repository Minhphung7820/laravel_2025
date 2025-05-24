<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {

  Route::group(['prefix' => 'user'], function () {
    Route::get('/', function (Request $request) {
      return $request->user();
    });
    Route::get('/list', [UserController::class, 'index']);
    Route::get('/advanced-list', [UserController::class, 'advancedEngagement']);
  });

  Route::group(['prefix' => 'customer'], function () {
    Route::get('/list', [CustomerController::class, 'index']);
    Route::get('/detail/{id}', [CustomerController::class, 'show']);
    Route::post('/create', [CustomerController::class, 'store']);
    Route::post('/update/{id}', [CustomerController::class, 'update']);
  });

  Route::group(['prefix' => 'warehouse'], function () {
    Route::group(['prefix' => 'category'], function () {
      Route::get('/list', [CategoryController::class, 'index']);
      Route::get('/detail/{id}', [CategoryController::class, 'show']);
      Route::post('/create', [CategoryController::class, 'store']);
      Route::post('/update/{id}', [CategoryController::class, 'update']);
    });
  });

  Route::group(['prefix' => 'product'], function () {
    Route::get('/recommend', [ProductController::class, 'recommend']);
  });

  Route::group(['prefix' => 'report'], function () {
    Route::get('/top-customers', [ReportController::class, 'topCustomers']);
    Route::get('/top-products', [ReportController::class, 'topProducts']);
  });
});
