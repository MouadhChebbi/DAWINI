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
            #verify if the user exists
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            #generate a random 6-digit code and save it in the database
            $code = rand(100000, 999999);
            DB::table('password_reset_codes')->insert([
                'email' => $email,
                'code' => $code,
                'created_at' => now(),
            ]);
            #send the code to the user's email
            Mail::to($email)->send(new PasswordResetCode($code));
            #return a success response
            return response()->json([
                'message' => 'Password reset link sent to your email if the email is registered',
                'user' => $user
            ], 200);
        }
}
