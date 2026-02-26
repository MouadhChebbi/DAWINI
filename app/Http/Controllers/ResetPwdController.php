<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ResetPwdController extends Controller
{
    function resetPassword(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $user->password = bcrypt($request->input('new_password'));
        $user->save();

        return response()->json([
            'message' => 'Password reset successful',
            'user' => $user
        ], 200);
    }
}
