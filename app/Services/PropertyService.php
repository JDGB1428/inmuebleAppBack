<?php

namespace App\Services;

use App\Models\Properties;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class PropertyService
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
            ->available()
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

    public function createProperty(array $validatedData, Request $request, $user): Properties
    {
        $validatedData['image'] = $this->uploadImages($request);

        return $user->property()->create($validatedData);
    }


    public function updateProperty(Properties $property, array $validatedData, Request $request): Properties
    {
        $validatedData['image'] = $this->handleImageUpdate($request, $property);
        $property->update($validatedData);
        return $property;
    }


    private function uploadImages(Request $request): ?array
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $files = Arr::wrap($request->file('image'));
        $imagePaths = [];

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $path = $file->store('properties', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        return empty($imagePaths) ? null : $imagePaths;
    }


    private function handleImageUpdate(Request $request, Properties $property): ?array
    {
        // 1. Obtener imágenes que el usuario decidió conservar
        $existingImages = $request->input('existing_images', []);
        if (!is_array($existingImages)) {
            $existingImages = [$existingImages];
        }


        $currentImages = $property->image ?? [];
        $imagesToDelete = array_diff($currentImages, $existingImages);

        foreach ($imagesToDelete as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image));
        }
        $newImages = $this->uploadImages($request) ?? [];

        $finalImages = array_values(array_merge($existingImages, $newImages));

        return empty($finalImages) ? null : $finalImages;
    }
}
