<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
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

    Route::group(['prefix' => 'supplier'], function () {
        Route::get('/list', [SupplierController::class, 'index']);
        Route::get('/detail/{id}', [SupplierController::class, 'show']);
        Route::post('/create', [SupplierController::class, 'store']);
        Route::post('/update/{id}', [SupplierController::class, 'update']);
    });

    Route::group(['prefix' => 'warehouse'], function () {
        Route::group(['prefix' => 'stock'], function () {
            Route::get('/list', [StockController::class, 'index']);
            Route::get('/detail/{id}', [StockController::class, 'show']);
            Route::post('/create', [StockController::class, 'store']);
            Route::post('/update/{id}', [StockController::class, 'update']);
        });

        Route::group(['prefix' => 'unit'], function () {
            Route::get('/list', [UnitController::class, 'index']);
            Route::get('/detail/{id}', [UnitController::class, 'show']);
            Route::post('/create', [UnitController::class, 'store']);
            Route::post('/update/{id}', [UnitController::class, 'update']);
        });

        Route::group(['prefix' => 'product'], function () {
            Route::get('/list', [ProductController::class, 'index']);
            Route::post('/create', [ProductController::class, 'store']);
            Route::get('/detail/{id}', [ProductController::class, 'show']);
            Route::put('/update/{id}', [ProductController::class, 'update']);
            Route::get('/get-init-combo', [ProductController::class, 'getInitCombo']);
        });

        Route::group(['prefix' => 'brand'], function () {
            Route::get('/list', [BrandController::class, 'index']);
            Route::get('/detail/{id}', [BrandController::class, 'show']);
            Route::post('/create', [BrandController::class, 'store']);
            Route::post('/update/{id}', [BrandController::class, 'update']);
        });

        Route::group(['prefix' => 'category'], function () {
            Route::get('/list', [CategoryController::class, 'index']);
            Route::get('/detail/{id}', [CategoryController::class, 'show']);
            Route::post('/create', [CategoryController::class, 'store']);
            Route::post('/update/{id}', [CategoryController::class, 'update']);
            Route::get('/{id}/attributes', [CategoryController::class, 'getAttributes']);
        });
    });

    Route::group(['prefix' => 'report'], function () {
        Route::get('/top-customers', [ReportController::class, 'topCustomers']);
        Route::get('/top-products', [ReportController::class, 'topProducts']);
    });
});
