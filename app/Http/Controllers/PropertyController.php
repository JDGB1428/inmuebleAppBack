<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Models\Categories;
use App\Models\PropertyImage;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index() {}

    public function store(PropertyRequest $request, Categories $categories)
    {
        $property = $request->user()->property()->create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'direction' => $request->direction,
            'room' => $request->room,
            'area_m2' => $request->area_m2,
            'bathrooms' => $request->bathrooms,
            'state' => $request->state,
            'user_id' => $request->user()->id,
            'category_id' => $categories->id,
            'images' => $request->images
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');

                // Crear registro en BD
                PropertyImage::create([
                    'property_id' => $property->id,
                    'url' => Storage::url($path)
                ]);
            }
        }

        return response()->json([
            'message' => 'Inmuble se ha creado correctamente',
            201
        ]);
    }
}
