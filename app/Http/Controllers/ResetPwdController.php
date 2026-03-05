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
        #update the user's password
        $user->password = bcrypt($request->input('new_password'));
        $user->save();
        #return a success response
        return response()->json([
            'message' => 'Password reset successful',
            'user' => $user
        ], 200);
    }
}
