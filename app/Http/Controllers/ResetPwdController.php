<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPwdController extends Controller
{
    function resetPassword(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        #verify if the user exists
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        #verify if the new password is the same as the old password
        if(Hash::check($request->input('password'), $user->password)){
            return response()->json([
                'message' => 'New password cannot be the same as the old password'
            ], 400);
        }
            $code = $request->input('code');
            $record = DB::table('password_reset_codes')
                ->where('email', $request->input('email'))
                ->where('code', $code)
                ->first();
        #verify if the code is valid
        if (!$record) {
            return response()->json([
                'message' => 'Invalid code'
            ], 400);
        }
        #verify if the code has expired (10 minutes)
        $expiredAt = strtotime($record->created_at) + 600;
            if (time() > $expiredAt) {
                DB::table('password_reset_codes')
                    ->where('email', $request->input('email'))
                    ->where('code', $code)
                    ->delete();
                return response()->json([
                    'message' => 'Code has expired'
                ], 400);
            }
        #update the user's password
        $user->password = bcrypt($request->input('new_password'));
        $user->save();
        #delete the code from the database
            DB::table('password_reset_codes')
                ->where('email', $request->input('email'))
                ->where('code', $code)
                ->delete();
        #return a success response
        return response()->json([
            'message' => 'Password reset successful',
            'user' => $user
        ], 200);
    }
}
