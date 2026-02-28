<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetCode;

class ForgotPasswordController extends Controller
{
        function forgotPassword(Request $request)
        {
            $user = User::where('email', $request->input('email'))->first();
            $email = $request->input('email');
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            $code = rand(100000, 999999);
            DB::table('password_reset_codes')->insert([
                'email' => $email,
                'code' => $code,
                'created_at' => now(),
            ]);

             Mail::to($email)->send(new PasswordResetCode($code));

            return response()->json([
                'message' => 'Password reset link sent to your email if the email is registered',
                'user' => $user
            ], 200);
        }
}
