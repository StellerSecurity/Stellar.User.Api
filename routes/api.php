<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['basicAuth'])->group(function () {
    Route::prefix('v1')->group(function () {


        Route::prefix('personaltokencontroller')->group(function () {
            Route::controller(\App\Http\Controllers\V1\PersonalTokenController::class)->group(function () {
                Route::get('/{token}', 'find');
            });
        });

        Route::prefix('usercontroller')->group(function () {
            Route::controller(\App\Http\Controllers\V1\UserController::class)->group(function () {
                Route::post('/createuser', 'create');
                Route::post('/login', 'login');
                Route::get('/user/{id}', 'user');
                Route::post('/sendresetpasswordlink', 'sendresetpasswordlink');
                Route::patch('/patch', 'patch');
                Route::patch('/changepassword', 'changepassword');

                Route::post('/verifyresetpasswordconfirmationcode', 'verifyresetpasswordconfirmationcode');
                Route::post('/checkresetpasswordconfirmationcode', 'checkresetpasswordconfirmationcode');


                Route::post('/verifyresetpasswordandupdate', 'verifyresetpasswordandupdate');
                Route::get('/verifyresetpasswordandupdate', 'verifyresetpasswordandupdate');
            });
        });

    });
});
