<?php

namespace App\Http\Controllers;

use App\Models\Properties;
use Illuminate\Http\Request;

class FilterController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/property/filter",
     *     summary="Filtrar inmuebles por categoría",
     *     description="Obtiene una lista de inmuebles filtrados por el ID de su categoría enviando el parámetro en la URL (?category_id=X).",
     *     tags={"Inmuebles"},
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
}
