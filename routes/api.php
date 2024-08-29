<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


use App\Http\Controllers\api\ActivityController;
use App\Http\Controllers\api\testrcontroller;
use App\Http\Controllers\api\LoginApiController;
use App\Http\Controllers\api\UserDetailsController;
use App\Http\Controllers\api\ProjectDetailsController;

Route::get('api-testing', function () {
    return response()->json('Post Api Is Success');
});

Route::post('activities/store', [ActivityController::class, 'store']);
Route::get('activities', [ActivityController::class, 'index']);

Route::get('/api-get', [testrcontroller::class, 'index']);

Route::post('/login',[LoginApiController::class, 'login']);
Route::get('/user-detail/{id}',[UserDetailsController::class, 'show']);
use App\Http\Controllers\Plan_Show;

Route::post('plans', [Plan_Show::class, 'index']);
Route::get('ProjectDetail/{id}',[ProjectDetailsController::class, 'show']);
