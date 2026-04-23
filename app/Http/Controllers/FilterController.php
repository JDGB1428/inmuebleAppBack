<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchPropertyRequest;
use App\Models\Properties;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class FilterController extends Controller
{


    protected $search_service;

    public function __construct(PropertyService $search_service)
    {
        $this->search_service = $search_service;
    }

    /**
     * @OA\Get(
     *     path="/api/property/filter",
     *     summary="Filtrar inmuebles por categoría",
     *     description="Obtiene una lista de inmuebles filtrados por el ID de su categoría enviando el parámetro en la URL (?category_id=X).",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="ID de la categoría para aplicar el filtro",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de inmuebles filtrada",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function filterByCategory(Request $request)
    {
        $user = $request->user();

        $properties = Properties::query()

            ->when($user->hasRole('owner'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })

            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })

            ->get();

        return response()->json([
            'data' => $properties
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/property/search",
     *     summary="Buscar inmuebles por título o ciudad",
     *     description="Realiza una búsqueda de inmuebles donde el título o la ciudad coincidan parcialmente con el término ingresado (?search=X).",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Término de búsqueda (ej. Cartagena, Apartamento, etc.)",
     *         @OA\Schema(type="string", example="Cartagena")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resultados de la búsqueda",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $user = $request->user();

        $properties = Properties::query()

            ->when($user->hasRole('owner'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })

            ->when($request->filled('search'), function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', $searchTerm)
                        ->orWhere('city', 'like', $searchTerm);
                });
            })

            ->get();

        return response()->json([
            'data' => $properties
        ]);
    }


        /**
     * @OA\Get(
     *     path="/api/property/suggetsSearch",
     *     summary="Buscador de Inmuebles y Sugerencias",
     *     description="Consulta el servicio de propiedades para buscar y filtrar inmuebles publicados. Aplica filtros por ciudad, capacidad total de huéspedes (adultos + niños) y verifica la disponibilidad real de fechas cruzando con las reservas existentes. Calcula e inyecta dinámicamente el precio total del viaje si se proporcionan fechas.",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         required=false,
     *         description="Nombre de la ciudad, municipio o parte de ella (Ej. Cartagena, Medellín). Búsqueda parcial.",
     *         @OA\Schema(type="string", example="Cartagena")
     *     ),
     *     @OA\Parameter(
     *         name="check_in",
     *         in="query",
     *         required=false,
     *         description="Fecha de llegada al alojamiento en formato YYYY-MM-DD. Debe enviarse junto con check_out.",
     *         @OA\Schema(type="string", format="date", example="2026-06-15")
     *     ),
     *     @OA\Parameter(
     *         name="check_out",
     *         in="query",
     *         required=false,
     *         description="Fecha de salida del alojamiento en formato YYYY-MM-DD. Debe enviarse junto con check_in y ser estrictamente mayor a este.",
     *         @OA\Schema(type="string", format="date", example="2026-06-20")
     *     ),
     *     @OA\Parameter(
     *         name="adults",
     *         in="query",
     *         required=false,
     *         description="Cantidad de adultos que se hospedarán (Valor por defecto: 1).",
     *         @OA\Schema(type="integer", minimum=1, example=2)
     *     ),
     *     @OA\Parameter(
     *         name="children",
     *         in="query",
     *         required=false,
     *         description="Cantidad de niños que se hospedarán. Suma a la capacidad requerida de la propiedad (Valor por defecto: 0).",
     *         @OA\Schema(type="integer", minimum=0, example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Búsqueda completada exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", description="Colección de inmuebles disponibles que cumplen con los filtros aplicados.",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=15),
     *                     @OA\Property(property="title", type="string", example="Hermosa casa con piscina y vista al mar"),
     *                     @OA\Property(property="city", type="string", example="Cartagena"),
     *                     @OA\Property(property="price", type="number", format="float", example=250000, description="Precio base de la propiedad por una sola noche."),
     *                     @OA\Property(property="likes_count", type="integer", example=42, description="Número de favoritos o 'me gusta' acumulados por la propiedad."),
     *                     @OA\Property(property="total_price_for_trip", type="number", format="float", nullable=true, example=1250000, description="Costo total de la estancia calculada (Precio Base x Noches). Aparece solo si se pasaron fechas."),
     *                     @OA\Property(property="total_nights", type="integer", nullable=true, example=5, description="Número total de noches calculadas de la estadía.")
     *                 )
     *             ),
     *             @OA\Property(property="search_summary", type="object", description="Metadatos informativos que resumen los parámetros que el sistema terminó utilizando para la búsqueda.",
     *                 @OA\Property(property="city", type="string", nullable=true, example="Cartagena"),
     *                 @OA\Property(property="dates", type="string", example="2026-06-15 al 2026-06-20"),
     *                 @OA\Property(property="guests", type="string", example="2 adultos, 1 niños"),
     *                 @OA\Property(property="nights", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación (Unprocessable Entity). Ocurre si las fechas son inválidas (ej. formato incorrecto) o si check_in es posterior al check_out.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Los datos proporcionados no son válidos."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "check_out": {"La fecha de salida debe ser una fecha posterior a la fecha de llegada."}
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error inesperado en el servidor.")
     *         )
     *     )
     * )
     */
    public function searchSuggetProperty(SearchPropertyRequest $request)
    {
        $filters = $request->validated();
        $result = $this->search_service->search($filters);

        return response()->json($result);
    }
}
