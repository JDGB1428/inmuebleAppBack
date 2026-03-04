<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends Controller
{


    public function index()
    {
        $activeProfiles = Profile::with('user')
            ->whereHas('user') // Solo trae el perfil si su usuario existe y está activo
            ->get();

        $trashedProfiles = Profile::with(['user' => function ($query) {
            $query->withTrashed();
        }])
            ->onlyTrashed()
            ->get();

        return response()->json([
            'message' => 'Perfiles obtenidos con éxito',
            'data' => [
                'active'  => $activeProfiles,
                'trashed' => $trashedProfiles
            ]
        ]);
    }


    public function show(Request $request)
    {
        // Traemos al usuario con su perfil anidado
        $user = $request->user()->load('profile');

        return response()->json([
            'message' => 'Perfil obtenido con éxito',
            'data' => $user
        ]);
    }

    public function update(ProfileRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();

        // 1. Manejo de la subida del Avatar
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar'] = Storage::url($path);
        }

        // 2. Guardar en la base de datos (Actualiza si existe, Crea si no existe)
        // El primer array es cómo buscarlo, el segundo es qué datos guardar
        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validatedData
        );

        return response()->json([
            'message' => 'Perfil guardado correctamente',
            'data' => $profile
        ]);
    }

    public function destroy(String $id)
    {
        $property = Profile::findOrFail($id);
        $property->delete();

        return response()->json([
            'message' => 'El inmueble ha sido eliminado'
        ]);
    }
}
