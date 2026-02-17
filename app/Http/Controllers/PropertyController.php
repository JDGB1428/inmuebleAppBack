<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Models\Properties;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{

    public function __construct() {}

    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver inmuebles'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear inmuebles'), only: ['store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar inmuebles'), only: ['update'])
        ];
    }

    public function index() {
        return [
            'data' => Properties::all()
        ];
    }

    public function store(PropertyRequest $request)
    {
        $rutasDeImagenes = [];

        if ($request->hasFile('image')) {
            $imagenes = is_array($request->file('image'))
                ? $request->file('image')
                : [$request->file('image')];

            foreach ($imagenes as $image) {
                // Guardar en storage/app/public/properties
                $path = $image->store('properties', 'public');
                $rutasDeImagenes[] = Storage::url($path);
            }
        }

        // 3. Crear la propiedad
        // Gracias al 'cast' del modelo, Laravel convertirá $rutasDeImagenes a JSON automáticamente
        $property = $request->user()->property()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $request->price,
            'direction'   => $request->direction,
            'room'        => $request->room,
            'area_m2'     => $request->area_m2,
            'bathrooms'   => $request->bathrooms,
            'state'       => $request->state,
            'category_id' => $request->category_id,
            'image'       => $rutasDeImagenes, // <--- Pasamos el array completo
        ]);

        return response()->json([
            'message' => 'El inmueble ha sido creado correctamente',
            'data' => $property
        ], 201); // El código 201 va fuera del array
    }

    public function update() {}
}
