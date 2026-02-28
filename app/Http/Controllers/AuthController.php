<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\File;

class AuthController extends Controller
{
    // Registration method :
    public function register(Request $request)
    {
        $user = User::create([
            'name'=> $request->input('name'),
            'last_name'=> $request->input('last_name'),
            'email'=> $request->input('email'),
            'password'=> Hash::make($request->input('password')),
            'phone_number'=> $request->input('phone_number'),
            'speciality'=> $request->input('speciality'),
            'gender'=> $request->input('gender'),
            'role'=> $request->input('role'),
        ]);
        if($request->hasFile('file')){
            $file= $request->file('file');
            $path= $file->store('uploads','public');

            File::create([
                'file_name'=>$file->getClientOriginalName(),
                'file_type'=>$file->getClientOriginalExtension(),
                'file_size'=>$file->getSize(),
                'user_id'=>$user->id,
            ]);
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->load('files'),
        ], 201);
    }
    // Login method :
    public function login(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        #verify if the user exists
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 401);
        }
        #verify if the password is correct
        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid password'
            ], 401);
        }
        #create a token for the user and return it in the response
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ], 200);
    }
}
