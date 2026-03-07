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
Route::post('/register', AuthController::class.'@register');

Route::post('/login',AuthController::class.'@login');

Route::post('/resetpassword',ResetPwdController::class.'@resetPassword');

Route::post('/forgotpassword',ForgotPasswordController::class.'@forgotPassword');

Route::post('/verifycode',ForgotPasswordController::class.'@verifyCode');

