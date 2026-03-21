<?php

namespace App\Http\Controllers;

use App\Http\Requests\LikeRequest;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Likes",
 *     description="Endpoints para gestionar likes de inmuebles"
 * )
 */
class LikesController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/property/{id}/like",
     *     summary="Agregar o quitar like a un inmueble",
     *     description="Alterna el like del usuario autenticado sobre un inmueble. Si ya existe el like, lo elimina; si no existe, lo agrega.",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_id"},
     *             @OA\Property(
     *                 property="property_id",
     *                 type="integer",
     *                 example=12,
     *                 description="ID del inmueble"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like procesado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Like agregado"
     *             ),
     *             @OA\Property(
     *                 property="is_liked",
     *                 type="boolean",
     *                 example=true
     *             )
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
    public function store(LikeRequest $request)
    {
        $user = $request->user();
        $propertyId = $request->validated()['property_id'];

        $result = $user->likes()->toggle($propertyId);

        $isLiked = count($result['attached']) > 0;

        return response()->json([
            'message' => $isLiked ? 'Like agregado' : 'Like eliminado',
            'is_liked' => $isLiked
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/likes",
     *     summary="Obtener mis likes",
     *     description="Retorna un arreglo con los IDs de los inmuebles a los que el usuario autenticado les dio like.",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Listado de likes obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="integer",
     *                     example=12
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function myLikes(Request $request)
    {
        $user = $request->user();
        $likedIds = $user->likes()->pluck('properties.id')->toArray();

        return response()->json([
            'data' => $likedIds
        ]);
    }
}
