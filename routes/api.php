<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CommentaryController;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// ==========================================
// RUTAS PÚBLICAS (No requieren token)
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Opcional: Si quieres que usuarios no logueados vean las propiedades,
// mueve el GET /property aquí afuera. (Por ahora lo dejamos privado).

// ==========================================
// RUTAS PRIVADAS (Requieren token de Sanctum)
// ==========================================
Broadcast::routes(['middleware' => ['auth:sanctum']]);
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

    // Perfiles
    Route::get('/profile/{id}', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    // ✅ Propiedades: Lectura (Todos pueden ver la lista y el detalle)
    Route::get('/property', [PropertyController::class, 'index']);
    Route::get('/property/{property}', [PropertyController::class, 'show'])->whereNumber('property');

    Route::get('/property/{property}/comments', [CommentaryController::class, 'index']);


    // ------------------------------------------
    // RUTAS PARA ADMINISTRADORES Y AGENTES (Creación y Modificación)
    // ------------------------------------------
    Route::group(['middleware' => ['role:admin|agent']], function () {

        // Propiedades: Escritura (Solo Admin y Agente pueden crear, editar y borrar)
        Route::post('/property', [PropertyController::class, 'store']);
        Route::put('/property/{property}', [PropertyController::class, 'update']);
        Route::delete('/property/{property}', [PropertyController::class, 'destroy']);



        // Ver la lista de todos los perfiles en el Panel de control
        Route::get('/profiles', [ProfileController::class, 'index']);
    });


    // ------------------------------------------
    // RUTAS EXCLUSIVAS PARA ADMINISTRADORES
    // ------------------------------------------
    Route::group(['middleware' => ['role:admin']], function () {

        Route::get('/property/trashed', [PropertyController::class, 'trashed']);
        Route::post('/property/{id}/restore', [PropertyController::class, 'restore']);
    });


    // ------------------------------------------
    // RUTAS EXCLUSIVAS PARA CLIENTES
    // ------------------------------------------
    Route::group(['middleware' => ['role:client']], function () {
        Route::get('/user/likes', [LikesController::class, 'myLikes']);
        Route::post('/property/{id}/like', [LikesController::class, 'store']);
        Route::post('/property/{property}/comment', [CommentaryController::class, 'store']);
        Route::get('/notifications/unread', [NotificationController::class, 'getUnread']);
        Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/{propertyId}/mark-read', [NotificationController::class, 'markOneAsRead']);
    });
});
