<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResetPwdController;
use App\Http\Controllers\ForgotPasswordController;

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
//Ma to5zerloosh zeied
/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('home/register', function () {
    return response()->json([
        'message' => 'Welcome to the registration page',
    ]);
});
*/
Route::post('/register', AuthController::class.'@register');

Route::post('/login',AuthController::class.'@login');

Route::post('/resetpassword',ResetPwdController::class.'@resetPassword');

Route::post('/forgotpassword',ForgotPasswordController::class.'@forgotPassword');

Route::post('/verifycode',ForgotPasswordController::class.'@verifyCode');

