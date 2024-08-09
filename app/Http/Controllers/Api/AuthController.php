<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signin(Request $request)
    {
        // Validation
        $messages = [
            'username.required' => 'Username harus diisi.',
            'username.string'   => 'Username harus berupa teks.',
            'username.max'      => 'Username tidak boleh lebih dari :max karakter.',
            'password.required' => 'Password harus diisi.',
            'password.string'   => 'Password harus berupa teks.',
            'password.min'      => 'Password minimal harus :min karakter.',
        ];

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Check User
        $credentials = ['username' => $request->username, 'password' => $request->password];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status'  => false,
                'message' => 'Username atau password salah.'
            ], 401);
        }

        // Initialize
        $user = Auth::user();
        $token = $user->createToken('Todo List')->accessToken;

        return response()->json([
            'status'    => true,
            'message'   => 'Login berhasil.',
            'data'      => [
                'user'  => $user,
                'token' => $token
            ]
        ]);
    }


    public function signup(Request $request)
    {
        // Validation
        $messages = [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah digunakan.',
            'username.required' => 'Username wajib diisi.',
            'username.string'   => 'Username harus berupa teks.',
            'username.max'      => 'Username tidak boleh lebih dari :max karakter.',
            'username.unique'   => 'Username sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.string'   => 'Password harus berupa teks.',
            'password.min'      => 'Password minimal harus :min karakter.',
        ];

        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users,email',
            'username'  => 'required|string|max:255|unique:users,username',
            'password'  => 'required|string|min:6'
        ], $messages);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Insert Data
        $user = User::create([
            'email'     => $request->email,
            'username'  => $request->username,
            'password'  => bcrypt($request->password)
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Registrasi berhasil',
            'user'    => $user
        ]);
    }
}
