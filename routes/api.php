<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\ReturnController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']); // Optional

// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
    


 
    Route::apiResource('products', ProductController::class);
    Route::post('/products/{id}/add-quantity', [ProductController::class, 'addQuantity']);

    Route::apiResource('clients', ClientController::class);
    
    // Additional client routes if necessary
    Route::get('/clients', [ClientController::class, 'index']);


    Route::get('/invoices/monthly-data', [InvoiceController::class, 'getMonthlyData']);
    Route::get('/invoices/years', [InvoiceController::class, 'getYears']);

   
    //Route::apiResource('invoices', InvoiceController::class);
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
    Route::get('/total-revenue', [InvoiceController::class, 'getTotalRevenue']);
    
    Route::apiResource('/returns', ReturnController::class);
    Route::post('/return-quantity', [ReturnController::class, 'returnToStock']);


    // Delete old invoice items
    Route::delete('/invoice-items', [InvoiceController::class, 'destroyItems']);

    Route::delete('/invoices/{invoice}/items/{item}', [ReturnController::class, 'removeInvoiceItem']);


    Route::get('/countries', [CountryController::class, 'index']);
    Route::post('/countries', [CountryController::class, 'store']);
    Route::delete('/countries/{id}', [CountryController::class, 'destroy']);
    Route::put('/countries/{id}', [CountryController::class, 'update']);

    Route::get('purchases/{countryId}', [PurchaseController::class, 'index']);
    Route::post('purchases', [PurchaseController::class, 'store']);
    Route::put('purchases/{id}', [PurchaseController::class, 'update']);
    Route::delete('purchases/{id}', [PurchaseController::class, 'destroy']);
    Route::get('/purchases/{id}', [PurchaseController::class, 'show']);




});
