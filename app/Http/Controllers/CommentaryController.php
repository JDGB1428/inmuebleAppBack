<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentaryRequest;
use App\Models\Commentary;
use App\Models\Properties;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Comentarios",
 *     description="Endpoints para gestionar comentarios de inmuebles"
 * )
 */
class CommentaryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/property/{property}/comments",
     *     summary="Listar comentarios de un inmueble",
     *     description="Retorna todos los comentarios asociados a un inmueble con la información del usuario que comentó.",
     *     tags={"Comentarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         description="ID del inmueble",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comentarios obtenidos correctamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=15),
     *                 @OA\Property(property="name", type="string", example="Juan Pérez"),
     *                 @OA\Property(property="avatar", type="string", nullable=true, example="https://example.com/storage/avatars/user.png"),
     *                 @OA\Property(property="description", type="string", example="Me interesa este inmueble"),
     *                 @OA\Property(property="createdAt", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inmueble no encontrado"
     *     )
     * )
     */
    public function index(Properties $property)
    {
        $commentary = $property->commentary()->with('user.profile')->get()->map(function ($commentary) {
            return [
                'id' => $commentary->id,
                'name' => $commentary->user->name,
                'avatar' => $commentary->user->profile->avatar ?? null,
                'description' => $commentary->description,
                'created_at' => $commentary->created_at,
            ];
        });

        return response()->json($commentary);
    }

    /**
     * @OA\Post(
     *     path="/api/property/{property}/comment",
     *     summary="Crear un comentario en un inmueble",
     *     description="Crea un comentario autenticado sobre un inmueble y devuelve el comentario con los datos del usuario.",
     *     tags={"Comentarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         description="ID del inmueble",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"description"},
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 example="Quiero más información sobre este inmueble"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comentario creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=15),
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="description", type="string", example="Quiero más información sobre este inmueble"),
     *             @OA\Property(property="avatar", type="string", nullable=true, example="https://example.com/storage/avatars/user.png"),
     *             @OA\Property(property="createdAt", type="string", format="date-time")
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
    public function store(CommentaryRequest $request, Properties $property)
    {
        $commentary = Commentary::create([
            'user_id' => Auth::user()->id,
            'property_id' => $property->id,
            'description' => $request->description
        ]);

        $commentary->load('user.profile');

        return response()->json([
            'id' => $commentary->id,
            'name' => $commentary->user->name,
            'description' => $commentary->description,
            'avatar' => $commentary->user->profile->avatar ?? null,
            'createdAt' => $commentary->created_at,
        ]);
    }
}
