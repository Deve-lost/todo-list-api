<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistsController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Auth
Route::post('login', [AuthController::class, 'signin']);
Route::post('register', [AuthController::class, 'signup']);

// With Bearer Token
Route::group(['middleware' => 'auth:api'], function () {
    // Checklist
    Route::group(['prefix' => 'checklist'], function () {
        Route::get('/', [ChecklistsController::class, 'index']);
        Route::post('/', [ChecklistsController::class, 'store']);
        Route::put('{id}', [ChecklistsController::class, 'update']);
        Route::delete('{id}', [ChecklistsController::class, 'destroy']);
    });
});
