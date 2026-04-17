<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $booking_service;

    public function __construct(BookingService $booking_service)
    {
        $this->booking_service = $booking_service;
    }

    /**
     * @OA\Get(
     *     path="/api/property/{propertyId}/availabilities",
     *     summary="Obtener fechas no disponibles de una propiedad",
     *     description="Devuelve únicamente las fechas de check_in y check_out de las reservas de una propiedad. Si el usuario envía un Bearer Token válido, el sistema calculará el campo 'is_my_booking' en true para sus propias reservas.",
     *     tags={"Reservas"},
     *     security={
     *         {},
     *         {"bearerAuth":{}}
     *     },
     *     @OA\Parameter(
     *         name="propertyId",
     *         in="path",
     *         required=true,
     *         description="ID de la propiedad a consultar",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de fechas ocupadas obtenida correctamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="check_in", type="string", format="date", example="2026-05-10", description="Fecha de llegada de la reserva"),
     *                 @OA\Property(property="check_out", type="string", format="date", example="2026-05-15", description="Fecha de salida de la reserva"),
     *                 @OA\Property(property="is_my_booking", type="boolean", example=false, description="Indica si esta reserva le pertenece al usuario que realiza la petición (Requiere Token)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Propiedad no encontrada"
     *     )
     * )
     */
    public function getPropertyAvailability($propertyId)
    {
        $user = Auth::id();

        $bookedDates = Booking::where('property_id', $propertyId)
                ->select('id', 'check_in', 'check_out', 'user_id')
                ->get()
                ->map(function ($booking) use ($user) {
                    return [
                        'check_in' => $booking->check_in,
                        'check_out' => $booking->check_out,
                        'is_my_booking' => $booking->user_id === $user
                    ];
                });
        return response()->json([
            'data' => $bookedDates
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/property/{propertyId}/bookings",
     *     summary="Listar reservas de una propiedad específica",
     *     description="Devuelve todas las reservas de una propiedad, verificando que la propiedad pertenezca al usuario autenticado. Usado para poblar FullCalendar.",
     *     tags={"Reservas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="propertyId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Lista de reservas")
     * )
     */
    public function getPropertyBookings($propertyId)
    {
        $userId = Auth::id();

        // Buscamos las reservas que cumplan 2 condiciones:
        // 1. Que pertenezcan al Inmueble solicitado
        // 2. Que el Inmueble le pertenezca al Usuario logueado
        $bookings = Booking::where('property_id', $propertyId)
            ->whereHas('property', function ($query) use ($userId) {
                $query->where('user_id', $userId); // Asumiendo que tu tabla properties tiene un user_id (propietario)
            })
            ->get();

        return response()->json([
            'data'=>$bookings
            ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/property/{propertyId}/booking",
     *     summary="Crear una nueva reserva para una propiedad",
     *     description="Crea una reserva validando la disponibilidad de las fechas y la cantidad de huéspedes. El usuario se asigna automáticamente mediante el token de autenticación.",
     *     tags={"Reservas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="propertyId",
     *         in="path",
     *         description="ID de la propiedad que se desea reservar",
     *         required=true,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"check_in", "check_out", "guests_count"},
     *             @OA\Property(property="check_in", type="string", format="date", example="2026-05-10", description="Fecha de llegada (YYYY-MM-DD)"),
     *             @OA\Property(property="check_out", type="string", format="date", example="2026-05-15", description="Fecha de salida (YYYY-MM-DD)"),
     *             @OA\Property(property="guests_count", type="integer", example=2, description="Cantidad de huéspedes que se alojarán")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reserva creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Reserva creada con éxito"),
     *             @OA\Property(property="data", type="object", description="Objeto de la reserva recién creada",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="property_id", type="integer", example=15),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="check_in", type="string", format="date", example="2026-05-10"),
     *                 @OA\Property(property="check_out", type="string", format="date", example="2026-05-15"),
     *                 @OA\Property(property="guests_count", type="integer", example=2),
     *                 @OA\Property(property="total_price", type="number", format="float", example=750000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación (Fechas solapadas o datos incorrectos)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El inmueble ya ha sido reservado para estas fechas por otro usuario."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Propiedad no encontrada"
     *     )
     * )
     */
    public function store(BookingRequest $request, $propertyId) {
        $data = $request->validated();

        $data['property_id'] = $propertyId;
        $data['user_id'] = Auth::id();
        $booking = $this->booking_service->createBooking($request->validated());
        return response()->json([
            'message' => 'Reserva creada con éxito',
            'data' => $booking
        ], 201);
    }
}
