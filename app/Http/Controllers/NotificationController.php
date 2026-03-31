<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Notificaciones",
 *     description="Endpoints para gestionar las notificaciones del usuario autenticado"
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notifications/unread",
     *     summary="Obtener notificaciones no leídas",
     *     description="Retorna un arreglo con todas las notificaciones que el usuario autenticado aún no ha marcado como leídas.",
     *     tags={"Notificaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Notificaciones no leídas obtenidas correctamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="06e22f2b-8a8f-4d32-8418-bd73e3a479eb"),
     *                 @OA\Property(property="type", type="string", example="App\\Notifications\\PropertyLiked"),
     *                 @OA\Property(property="notifiable_type", type="string", example="App\\Models\\User"),
     *                 @OA\Property(property="notifiable_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     @OA\Property(property="property_id", type="integer", example=15),
     *                     @OA\Property(property="message", type="string", example="A alguien le gustó tu propiedad")
     *                 ),
     *                 @OA\Property(property="read_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function getUnread(Request $request)
    {
        return response()->json($request->user()->unreadNotifications);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/read/{propertyId}",
     *     summary="Marcar notificación como leída por ID de propiedad",
     *     description="Busca notificaciones no leídas asociadas a un property_id específico dentro del campo JSON 'data' y las marca como leídas.",
     *     tags={"Notificaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="propertyId",
     *         in="path",
     *         required=true,
     *         description="ID de la propiedad",
     *         @OA\Schema(type="string", example="15")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificación marcada como leída",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notificación no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Notificación no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function markOneAsRead(Request $request, $propertyId)
    {
        $notifications = $request->user()->unreadNotifications()
            ->whereRaw("(data::json)->>'property_id' = ?", [(string)$propertyId])
            ->get();

        if ($notifications->isNotEmpty()) {
            $notifications->markAsRead();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Notificación no encontrada'], 404);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/read-all",
     *     summary="Marcar todas las notificaciones como leídas",
     *     description="Marca masivamente todas las notificaciones pendientes del usuario autenticado como leídas.",
     *     tags={"Notificaciones"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Todas las notificaciones fueron marcadas como leídas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function markAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}
