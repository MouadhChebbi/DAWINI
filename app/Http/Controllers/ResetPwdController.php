<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
            $code = $request->input('code');
            $record = DB::table('password_reset_codes')
                ->where('email', $request->input('email'))
                ->where('code', $code)
                ->first();
            if (!$record) {
                return response()->json([
                    'message' => 'Invalid code'
                ], 400);
            }
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
    
        $user->password = bcrypt($request->input('new_password'));
        $user->save();
            DB::table('password_reset_codes')
                ->where('email', $request->input('email'))
                ->where('code', $code)
                ->delete();
        return response()->json([
            'message' => 'Password reset successful',
            'user' => $user
        ], 200);
    }
}
