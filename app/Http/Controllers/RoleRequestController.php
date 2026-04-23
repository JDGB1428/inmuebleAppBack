<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest as RequestsRoleRequest;
use App\Models\RoleRequest;
use App\Models\User;
use App\Notifications\NewRoleRequestNotification;
use App\Notifications\RoleApprovedNotification;
use App\Notifications\RoleRejectNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleRequestController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/role_requests",
 *     summary="Listar solicitudes de rol pendientes",
 *     description="Obtiene una lista de todas las solicitudes de rol que tienen el estado 'pendiente'. Incluye la información del usuario asociado a cada solicitud.",
 *     tags={"Solicitudes de rol"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Lista de solicitudes pendientes obtenida con éxito",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="user_id", type="integer", example=4),
 *                     @OA\Property(property="description", type="string", example="Quiero ser agente para publicar propiedades"),
 *                     @OA\Property(property="status", type="string", example="pending"),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time"),
 *                     @OA\Property(
 *                         property="user",
 *                         type="object",
 *                         description="Usuario que realizó la solicitud",
 *                         @OA\Property(property="id", type="integer", example=4),
 *                         @OA\Property(property="name", type="string", example="Carlos López"),
 *                         @OA\Property(property="email", type="string", example="carlos@example.com")
 *                     )
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
    public function index()
    {
        $requests = RoleRequest::with('user')->where('status', 'pendiente')->get();
        return response()->json(['data' => $requests]);
    }

    /**
 * @OA\Post(
 *     path="/api/role_request",
 *     summary="Enviar solicitud de rol",
 *     description="Permite al usuario autenticado enviar una solicitud de rol. Si ya tiene una solicitud pendiente, no crea una nueva.",
 *     tags={"Solicitudes de rol"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"description"},
 *             @OA\Property(
 *                 property="description",
 *                 type="string",
 *                 example="Quiero solicitar el rol de agente inmobiliario"
 *             ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Solicitud procesada correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Solicitud enviada correctamente"
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

    public function store(RequestsRoleRequest $request)
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        RoleRequest::create([
            'user_id' => $user->id,
            'description' => $validatedData['description'],
            'status' => 'pendiente'
        ]);

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewRoleRequestNotification($user));
        }

        return response()->json([
            'message' => 'Solicitud enviada correctamente'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/role_requestById",
     *     summary="Obtener la solicitud actual del usuario autenticado",
     *     description="Devuelve la última solicitud de cambio de rol (cliente/propietario) realizada por el usuario actual.",
     *     operationId="showMyRequest",
     *     tags={"Role Requests"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud encontrada exitosamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=5),
     *             @OA\Property(property="description", type="string", example="Quiero rentar mi propiedad en el centro."),
     *             @OA\Property(property="status", type="string", example="pendiente"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2026-04-09T15:30:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2026-04-09T15:30:00.000000Z")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="No se encontró ninguna solicitud activa.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No tienes ninguna solicitud de agente activa.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado (Token inválido o ausente).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function showMyRequest(Request $request)
    {
        $user = $request->user();
        $ownerRequest = RoleRequest::where('user_id', $user->id)
                                    ->latest()
                                    ->first();

        if (!$ownerRequest) {
            return response()->json([
                'message' => 'No tienes ninguna solicitud de agente activa.',
            ], 404);
        }
        return response()->json($ownerRequest, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/role_request/{id}/approve",
     *     summary="Aprobar una solicitud de rol",
     *     description="Cambia el estado de la solicitud a 'approved', asigna el rol de 'owner' al usuario (removiendo sus roles anteriores) y le envía una notificación.",
     *     tags={"Solicitudes de rol"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la solicitud de rol a aprobar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud aprobada con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Solicitud aprobada y rol actualizado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Solicitud no encontrada"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function approve($id)
    {
        $roleRequest = RoleRequest::findOrFail($id);
        $roleRequest->update(['status' => 'aprobado']);

        $user = $roleRequest->user;
        $user->syncRoles(['owner']);

        $user->notify(new RoleApprovedNotification());

        return response()->json(['message' => 'Solicitud aprobada y rol actualizado.']);
    }

    /**
     * @OA\Post(
     *     path="/api/role_request/{id}/reject",
     *     summary="Rechazar una solicitud de rol",
     *     description="Actualiza el estado de la solicitud a 'rejected'. El usuario mantendrá sus roles actuales.",
     *     tags={"Solicitudes de rol"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la solicitud de rol a rechazar",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud rechazada con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Solicitud rechazada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Solicitud no encontrada"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function reject($id)
    {
        $roleRequest = RoleRequest::findOrFail($id);
        $roleRequest->update(['status' => 'Rechazado']);
        $user = $roleRequest->user;
        $user->syncRoles(['client']);
        $user->notify(new RoleRejectNotification());

        return response()->json(['message' => 'Solicitud rechazada.']);
    }
}
