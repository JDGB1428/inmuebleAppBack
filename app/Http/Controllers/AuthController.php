<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Iniciar sesión",
     *     description="Autentica a un usuario y devuelve un token de acceso (Sanctum/Passport).",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="cliente@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", description="Token de acceso Bearer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Juan Pérez"),
     *                 @OA\Property(property="email", type="string", example="cliente@example.com"),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string", example="client")),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Credenciales incorrectas o error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string", example="El email o el password son incorrectos")
     *             )
     *         )
     *     )
     * )
     */
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
                'email' => $user->email, // CORREGIDO: antes decía $user->name
                'id' => $user->id,
                'name' => $user->name,
                'updated_at' => $user->updated_at,
                'roles' => $user->roles->pluck('name'),
            ]

        ];
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar un nuevo usuario",
     *     description="Crea una cuenta de usuario nueva, le asigna el rol 'client' y devuelve un token de acceso.",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", description="Token de acceso Bearer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Juan Pérez"),
     *                 @OA\Property(property="email", type="string", example="juan@example.com"),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string", example="client")),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación (ej. el email ya existe)"
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole('client');

        return [
            'token' => $user->createToken('token')->plainTextToken,
            'data' => [
                'created_at' => $user->created_at,
                'email' => $user->email, // CORREGIDO: antes decía $user->name
                'id' => $user->id,
                'name' => $user->name,
                'updated_at' => $user->updated_at,
                'roles' => $user->roles->pluck('name'),
            ]
        ];

    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Cerrar sesión",
     *     description="Revoca el token de acceso actual del usuario autenticado.",
     *     tags={"Autenticación"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cierre de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function logout(Request $request){
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return [
            'data' => null
        ];
    }
}
