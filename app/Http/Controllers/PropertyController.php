<?php

namespace App\Http\Controllers;

use App\Events\PropertyCreatedEvent;
use App\Http\Requests\PropertyRequest;
use App\Models\Properties;
use App\Models\User;
use App\Notifications\NewPropertyNotification;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * @OA\Tag(
 *     name="Inmuebles",
 *     description="Endpoints para la gestión de inmuebles"
 * )
 */
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

    /**
     * @OA\Get(
     *     path="/api/property",
     *     summary="Listar todos los inmuebles",
     *     description="Retorna una lista de inmuebles. Los administradores ven todos, los agentes ven los suyos, y los clientes ven solo los disponibles o rentados.",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de inmuebles obtenida con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $properties = Properties::query()
            ->when($user->hasRole('agent'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when(! $user->hasRole(['admin', 'agent']), function ($query) {
                $query->whereIn('state', ['available', 'rented']);
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $properties
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/property",
     *     summary="Crear un nuevo inmueble",
     *     description="Crea un inmueble permitiendo subir una o múltiples imágenes.",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "price", "state"},
     *                 @OA\Property(property="title", type="string", description="Título del inmueble"),
     *                 @OA\Property(property="price", type="number", description="Precio"),
     *                 @OA\Property(property="state", type="string", description="Estado (ej. available, rented)"),
     *                 @OA\Property(
     *                     property="image[]",
     *                     type="array",
     *                     description="Arreglo de imágenes a subir",
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inmueble creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(PropertyRequest $request)
    {
        $validatedData = $request->validated();
        $rutasDeImagenes = [];

        if ($request->hasFile('image')) {
            $imagenes = is_array($request->file('image'))
                ? $request->file('image')
                : [$request->file('image')];

            foreach ($imagenes as $image) {
                $path = $image->store('properties', 'public');
                $rutasDeImagenes[] = Storage::url($path);
            }
        }

        $validatedData['image'] = $rutasDeImagenes;

        $property = $request->user()->property()->create($validatedData);

        $users = User::role('client')
            ->where('id', '!=', $request->user()->id)
            ->get();

        if ($users->isNotEmpty()) {
            // 1. Guardar silenciosamente en la BD
            Notification::send($users, new NewPropertyNotification($property));

            // 2. Disparar el WebSocket al canal privado de cada cliente
            foreach ($users as $user) {
                broadcast(new PropertyCreatedEvent($property, $user->id));
            }
        }

        // <-- AÑADE ESTE BLOQUE AL FINAL -->
        return response()->json([
            'message' => 'El inmueble ha sido creado correctamente',
            'data' => $property
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/property/{id}",
     *     summary="Mostrar un inmueble específico",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del inmueble",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalle del inmueble",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Inmueble no encontrado")
     * )
     */
    public function show(string $id)
    {
        return response()->json([
            'data' => Properties::findOrFail($id)
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/property/{id}",
     *     summary="Actualizar un inmueble",
     *     description="Actualiza datos e imágenes. NOTA: Se usa POST simulando PUT (_method=PUT) debido a la limitación de PHP/Laravel con multipart/form-data en peticiones PUT.",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del inmueble",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT", description="Requerido por Laravel para spoofing"),
     *                 @OA\Property(property="title", type="string", description="Título del inmueble"),
     *                 @OA\Property(
     *                     property="existing_images[]",
     *                     type="array",
     *                     description="Rutas de las imágenes que se desean conservar",
     *                     @OA\Items(type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="image[]",
     *                     type="array",
     *                     description="Nuevas imágenes a subir",
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inmueble actualizado correctamente"
     *     )
     * )
     */
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
                $path = $image->store('properties', 'public');
                $rutasFinalesDeImagenes[] = Storage::url($path);
            }
        }
        $validatedData['image'] = empty($rutasFinalesDeImagenes) ? null : $rutasFinalesDeImagenes;

        $property->update($validatedData);

        return response()->json([
            'message' => 'El inmueble ha sido actualizado correctamente',
            'data' => $property->fresh()
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/property/trashed",
     *     summary="Ver inmuebles en papelera",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de inmuebles eliminados",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function trashed()
    {
        $trashedProperties = Properties::onlyTrashed()->get();

        return response()->json([
            'message' => 'Propiedades en la papelera',
            'data' => $trashedProperties
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/property/{id}",
     *     summary="Eliminar un inmueble (Soft Delete)",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del inmueble",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inmueble eliminado",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(response=404, description="Inmueble no encontrado")
     * )
     */
    public function destroy(String $id)
    {
        $property = Properties::findOrFail($id);
        $property->delete();

        return response()->json([
            'message' => 'El inmueble ha sido eliminado'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/property/{id}/restore",
     *     summary="Restaurar un inmueble eliminado",
     *     tags={"Inmuebles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del inmueble",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inmueble restaurado con éxito",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(response=404, description="Inmueble no encontrado en la papelera")
     * )
     */
    public function restore(String $id)
    {
        $property = Properties::withTrashed()->findOrFail($id);
        $property->restore();

        return response()->json([
            'message' => 'El inmueble ha sido restaurado con éxito.'
        ]);
    }
}
