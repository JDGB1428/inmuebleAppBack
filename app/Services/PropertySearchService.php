<?php

namespace App\Services;

use App\Models\Properties;
use Carbon\Carbon;

class PropertySearchService
{
    /**
     * Ejecuta la búsqueda de propiedades y retorna los datos y el resumen.
     */
    public function search(array $filters): array
    {
        $city = $filters['city'] ?? null;
        $checkIn = $filters['check_in'] ?? null;
        $checkOut = $filters['check_out'] ?? null;
        $adults = $filters['adults'] ?? 1;
        $children = $filters['children'] ?? 0;

        // 1. Ejecutamos la consulta usando los Scopes del modelo
        $properties = Properties::query()
            ->published()
            ->filterByCity($city)
            ->filterByCapacity($adults, $children)
            ->availableForDates($checkIn, $checkOut)
            ->withCount('likes')
            ->get();

        if ($checkIn && $checkOut) {
            $properties->append(['total_price_for_trip', 'total_nights']);
        }

        // 2. Calculamos las noches totales para el resumen
        $totalNightsCalculated = ($checkIn && $checkOut)
            ? Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut))
            : 0;

        // 3. Retornamos un arreglo estructurado con la data lista
        return [
            'data' => $properties,
            'search_summary' => [
                'city' => $city,
                'dates' => ($checkIn && $checkOut) ? "$checkIn al $checkOut" : 'Fechas flexibles',
                'guests' => "$adults adultos, $children niños",
                'nights' => $totalNightsCalculated
            ]
        ];
    }
}
