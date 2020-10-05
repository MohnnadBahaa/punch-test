<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MerchantController;

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

// health-check api
Route::get('/health-check', function () {
    return view('welcome');
});

// all merchants endpoints
Route::group(['prefix' => 'merchants'], function ($router) {
    Route::get('/', [MerchantController::class, 'index']);
    Route::post('register', [MerchantController::class, 'register']);
    Route::match(['put', 'patch'], 'update/{id}', [MerchantController::class, 'update']);
    Route::post('subscribe', [MerchantController::class, 'subscribe']);
});

// all users endpoints
Route::group(['prefix' => 'users'], function ($router) {
    Route::get('/', [UserController::class, 'index']);
    Route::post('register', [UserController::class, 'register']);
    Route::get('subscrabtions/{id}', [UserController::class, 'userMerchantsList']);
    Route::match(['put', 'patch'], 'update/{id}', [UserController::class, 'update']);
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});