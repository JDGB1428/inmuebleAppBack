<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==========================================
// RUTAS PÚBLICAS (No requieren token)
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// ==========================================
// RUTAS PRIVADAS (Requieren token de Sanctum)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // Obtener usuario actual
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categorías (Accesible para cualquiera que esté logueado)
    Route::get('/category', [CategoriesController::class, 'index']);

    // ------------------------------------------
    // RUTAS GENERALES (Cualquier usuario logueado: Admin, Agente o Cliente)
    // ------------------------------------------
    // Obtiene el perfil del usuario logueado
    Route::get('/profile', [ProfileController::class, 'show']);

    // Crea o actualiza el perfil del usuario logueado
    Route::post('/profile', [ProfileController::class, 'update']);


    // ------------------------------------------
    // RUTAS EXCLUSIVAS PARA ADMINISTRADORES
    // ------------------------------------------
    Route::group(['middleware' => ['role:admin']], function () {
        Route::get('/property/trashed', [PropertyController::class, 'trashed']);
        Route::post('/property/{id}/restore', [PropertyController::class, 'restore']);
    });

    // ------------------------------------------
    // RUTAS PARA ADMINISTRADORES Y AGENTES
    // ------------------------------------------
    Route::group(['middleware' => ['role:admin|agent']], function() {
        Route::apiResource('/property', PropertyController::class);

        // Ver la lista de todos los perfiles (Panel de control)
        Route::get('/profiles', [ProfileController::class, 'index']);
    });

    // ------------------------------------------
    // RUTAS EXCLUSIVAS PARA CLIENTES
    // ------------------------------------------
    Route::group(['middleware' => ['role:client']], function () {
        // Dar Like a una propiedad
        Route::post('/property/{id}/like', [LikesController::class, 'store']);
    });

});
