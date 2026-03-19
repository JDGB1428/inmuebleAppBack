<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Obtener todos los perfiles (activos y en papelera).
     */
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
            'active'  => $activeProfiles,
            'trashed' => $trashedProfiles

        ]);
    }

    /**
     * Mostrar el perfil del usuario autenticado.
     */
    public function show(Request $request)
    {
        // Traemos al usuario con su perfil anidado
        $user = $request->user()->load('profile');

        return response()->json([
            'data' => $user->only(['id', 'name', 'email', 'profile'])
        ]);
    }

    /**
     * Crear o actualizar el perfil del usuario autenticado.
     */
    public function update(ProfileRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();

        // 1. Manejo de la subida del Avatar
        if ($request->hasFile('avatar')) {

            // Si el usuario ya tiene un perfil y un avatar previo, lo borramos del disco duro
            if ($user->profile && $user->profile->avatar) {
                $oldPath = str_replace('/storage/', '', $user->profile->avatar);
                Storage::disk('public')->delete($oldPath);
            }

            // Guardamos el nuevo avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar'] = Storage::url($path);

        } else {
            // Si no envió avatar en esta petición, quitamos la llave para no sobreescribir con "null"
            // y así conservar la foto que ya tenía guardada en la base de datos.
            unset($validatedData['avatar']);
        }

        // 2. Guardar en la BD (Actualiza si existe, Crea si no existe)
        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id], // Condición: Busca si ya hay un perfil con este user_id
            $validatedData            // Valores: Los datos a guardar
        );

        // 3. Verificamos si Eloquent acaba de hacer un INSERT (crear) o un UPDATE (editar)
        $wasCreated = $profile->wasRecentlyCreated;

        return response()->json([
            'message' => $wasCreated ? 'Perfil creado correctamente' : 'Perfil actualizado correctamente',
            'data' => $profile
        ], $wasCreated ? 201 : 200);
    }

    /**
     * Eliminar un perfil.
     */
    public function destroy(String $id)
    {
        $profile = Profile::findOrFail($id);
        $profile->delete();

        return response()->json([
            'message' => 'El perfil ha sido eliminado'
        ]);
    }
}
