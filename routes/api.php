<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResetPwdController;

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
Route::get('home/register', function () {
    return response()->json([
        'message' => 'Welcome to the registration page',
    ]);
});

Route::post('/register', AuthController::class.'@register');

Route::post('/login',AuthController::class.'@login');

Route::put('/reset password',ResetPwdController::class.'@resetPassword');
