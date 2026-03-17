<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetCode;

class ForgotPasswordController extends Controller
{
        //___________________________________________________________________________________________________________________________________________________
        //function to handle forgot password request and send a reset code to the user's email
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
        //___________________________________________________________________________________________________________________________________________________
        //function to verify the reset code
        function verifyCode(Request $request)
        {
            $email = $request->input('email');
            $code = $request->input('code');
            $record = DB::table('password_reset_codes')
                ->where('email', $email)
                ->where('code', $code)
                ->where('created_at', '>=', now()->subSeconds(300))
                ->first();
            if (!$record) {
                $resp=response()->json([
                    'message' => 'Invalid or expired code'
                ], 400);
            }else{
                #return a success response
                $resp=response()->json([
                'message' => 'Code verified successfully'
            ], 200);
            }
            #delete the code from the database
            DB::table('password_reset_codes')
                ->where('email', $email)
                ->where('code', $code)
                ->delete();
            return $resp;
        }
}
