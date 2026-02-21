<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if(!Auth::attempt($data)){
            return response([
                'errors' => ['El email o el password son incorrectos']
            ], 422);
        }

        $user = Auth::user();

        return[
            'token' => $user->createToken('token')->plainTextToken,
            'data' => [
                'created_at' => $user->created_at,
                'email' => $user->name,
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'updated_at' => $user->updated_at,
                'roles' => $user->roles->pluck('name'),
            ]

        ];
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'phone' => $data['phone']
        ]);

        $user->assignRole('client');

        return [
            'token' => $user->createToken('token')->plainTextToken,
            'data' => [
                'created_at' => $user->created_at,
                'email' => $user->name,
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'updated_at' => $user->updated_at,
                'roles' => $user->roles->pluck('name'),
            ]
        ];

    }

    public function logout(Request $request){
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return [
            'data' => null
        ];
    }
}
