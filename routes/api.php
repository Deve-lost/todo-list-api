<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistsController;
use App\Http\Controllers\Api\TodoItemController;
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
        Route::get('detail/{id}', [ChecklistsController::class, 'show']);
        Route::post('/', [ChecklistsController::class, 'store']);
        Route::put('{id}', [ChecklistsController::class, 'update']);
        Route::delete('{id}', [ChecklistsController::class, 'destroy']);

        Route::group(['prefix' => '{id}/item'], function () {
            Route::get('/', [TodoItemController::class, 'index']);
            Route::post('/', [TodoItemController::class, 'store']);
            Route::get('/{itemId}', [TodoItemController::class, 'show']);
            Route::delete('/{itemId}', [TodoItemController::class, 'destroy']);
            Route::put('/{itemId}', [TodoItemController::class, 'updateStatus']);
        });
    });
});
