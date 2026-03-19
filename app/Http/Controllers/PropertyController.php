<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Models\Properties;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class PropertyController extends Controller
{

    public function __construct() {}

    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver inmuebles'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear inmuebles'), only: ['store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar inmuebles'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('restaurar inmuebles'), only: ['restore']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver papelera inmuebles'), only: ['trashed'])
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $properties = Properties::query()
        ->when($user->hasRole('agent'), function ($query) use ($user) {
            // El agente SOLO ve las suyas
            $query->where('user_id', $user->id);
        })
        ->when(! $user->hasRole(['admin', 'agent']), function ($query) {
            // El cliente (u otros roles) SOLO ve disponibles y rentadas
            $query->whereIn('state', ['available', 'rented']);
        })
        ->latest()
        ->get();

        return response()->json([
            'data' => $properties
        ]);
    }

    public function store(PropertyRequest $request)
    {
        $validatedData = $request->validated();
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

        $validatedData['image'] = $rutasDeImagenes;

        $property = $request->user()->property()->create($validatedData);

        return response()->json([
            'message' => 'El inmueble ha sido creado correctamente',
            'data' => $property
        ], 201);
    }

    public function update(PropertyRequest $request, string $id)
    {
        $property = Properties::findOrFail($id);
        $validatedData = $request->validated();
        $imagenesExistentes = $request->input('existing_images', []);

        $rutasFinalesDeImagenes = $imagenesExistentes;

        if (is_array($property->image)) {
            $imagenesBorradas = array_diff($property->image, $imagenesExistentes);

            foreach ($imagenesBorradas as $imagenParaBorrar) {
                $oldPath = str_replace('/storage/', '', $imagenParaBorrar);
                Storage::disk('public')->delete($oldPath);
            }
        }

        if ($request->hasFile('image')) {
            $imagenesNuevas = is_array($request->file('image'))
                ? $request->file('image')
                : [$request->file('image')];

            foreach ($imagenesNuevas as $image) {
                // Guardar en storage/app/public/properties
                $path = $image->store('properties', 'public');
                $rutasFinalesDeImagenes[] = Storage::url($path);
            }
        }
        $validatedData['image'] = empty($rutasFinalesDeImagenes) ? null : $rutasFinalesDeImagenes;

        // 4. Actualizamos el modelo
        $property->update($validatedData);

        return response()->json([
            'message' => 'El inmueble ha sido actualizado correctamente',
            'data' => $property->fresh()
        ], 200);
    }


    public function show(string $id)
    {
        return response()->json([
            'data' => Properties::findOrFail($id)
        ], 200);
    }

    public function trashed()
    {
        $trashedProperties = Properties::onlyTrashed()->get();

        return response()->json([
            'message' => 'Propiedades en la papelera',
            'data' => $trashedProperties
        ]);
    }

    public function destroy(String $id)
    {
        $property = Properties::findOrFail($id);
        $property->delete();

        return response()->json([
            'message' => 'El inmueble ha sido eliminado'
        ]);
    }

    public function restore(String $id)
    {
        $property = Properties::withTrashed()->findOrFail($id);
        $property->restore();

        return response()->json([
            'message' => 'El inmueble ha sido restaurado con éxito.'
        ]);
    }
}
