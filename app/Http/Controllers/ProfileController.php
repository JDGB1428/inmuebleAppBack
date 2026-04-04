<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Perfiles",
 *     description="Endpoints para la gestión de perfiles de usuario"
 * )
 */
class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profiles",
     *     summary="Listar perfiles activos y eliminados",
     *     description="Retorna los perfiles activos junto con los perfiles eliminados lógicamente.",
     *     tags={"Perfiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Listado de perfiles obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="active",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="trashed",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function index()
    {
        $activeProfiles = Profile::with('user')
            ->whereHas('user')
            ->get();

        $trashedProfiles = Profile::with(['user' => function ($query) {
            $query->withTrashed();
        }])
            ->onlyTrashed()
            ->get();

        return response()->json([
            'active'  => $activeProfiles,
            'trashed' => $trashedProfiles
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Mostrar el perfil del usuario autenticado",
     *     description="Retorna los datos básicos del usuario autenticado junto con su perfil asociado.",
     *     tags={"Perfiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Perfil obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Juan Pérez"),
     *                 @OA\Property(property="email", type="string", example="juan@example.com"),
     *                 @OA\Property(property="profile", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function show($id)
    {
        $user = User::with('profile')->findOrFail($id);

        return response()->json([
            'data' => $user->only(['id', 'name', 'email', 'profile'])
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/profile",
     *     summary="Crear o actualizar el perfil del usuario autenticado",
     *     description="Crea el perfil si no existe o lo actualiza si ya existe. Permite subir un avatar usando multipart/form-data.",
     *     tags={"Perfiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     format="binary",
     *                     description="Imagen de avatar del usuario"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     example="3001234567",
     *                     nullable=true,
     *                     description="Campo de ejemplo, ajústalo a tu ProfileRequest"
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     type="string",
     *                     example="Cartagena, Bolívar",
     *                     nullable=true,
     *                     description="Campo de ejemplo, ajústalo a tu ProfileRequest"
     *                 ),
     *                 @OA\Property(
     *                     property="bio",
     *                     type="string",
     *                     example="Asesor inmobiliario",
     *                     nullable=true,
     *                     description="Campo de ejemplo, ajústalo a tu ProfileRequest"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Perfil actualizado correctamente"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Perfil creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Perfil creado correctamente"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function update(ProfileRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->profile && $user->profile->avatar) {
                $oldPath = str_replace('/storage/', '', $user->profile->avatar);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar'] = Storage::url($path);
        } else {
            unset($validatedData['avatar']);
        }

        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validatedData
        );

        $wasCreated = $profile->wasRecentlyCreated;

        return response()->json([
            'message' => $wasCreated ? 'Perfil creado correctamente' : 'Perfil actualizado correctamente',
            'data' => $profile
        ], $wasCreated ? 201 : 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/profiles/{id}",
     *     summary="Eliminar un perfil",
     *     description="Elimina un perfil por su identificador.",
     *     tags={"Perfiles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del perfil",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El perfil ha sido eliminado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Perfil no encontrado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function destroy(String $id)
    {
        $profile = Profile::findOrFail($id);
        $profile->delete();

        return response()->json([
            'message' => 'El perfil ha sido eliminado'
        ]);
    }
}
