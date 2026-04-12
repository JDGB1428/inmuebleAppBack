<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CommentaryController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RoleRequestController;
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

    Route::get('/property', [PropertyController::class, 'index']);
    Route::get('/property/filter', [FilterController::class, 'filterByCategory']);
    Route::get('/property/search', [FilterController::class, 'search']);
    Route::get('/property/{property}', [PropertyController::class, 'show'])->whereNumber('property');

    Route::get('/property/{property}/comments', [CommentaryController::class, 'index']);


    // ------------------------------------------
    // RUTAS PARA ADMINISTRADORES Y PROPETARIOS (Creación y Modificación)
    // ------------------------------------------
    Route::group(['middleware' => ['role:admin|owner']], function () {

        // Propiedades: Escritura (Solo Admin y Agente pueden crear, editar y borrar)
        Route::post('/property', [PropertyController::class, 'store']);
        Route::put('/property/{property}', [PropertyController::class, 'update']);
        Route::delete('/property/{property}', [PropertyController::class, 'destroy']);



        // Ver la lista de todos los perfiles en el Panel de control
        Route::get('/profiles', [ProfileController::class, 'index']);
    });

    Route::group(['middleware' => ['role:owner|client']], function(){
        Route::delete('/profiles/{id}', [ProfileController::class, 'destroy']);
        Route::get('/notifications/unread', [NotificationController::class, 'getUnread']);
        Route::post('/notifications/read/{propertyId}', [NotificationController::class, 'markOneAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAsRead']);
    });


    // ------------------------------------------
    // RUTAS EXCLUSIVAS PARA ADMINISTRADORES
    // ------------------------------------------
    Route::group(['middleware' => ['role:admin']], function () {

        Route::get('/property/trashed', [PropertyController::class, 'trashed']);
        Route::post('/property/{id}/restore', [PropertyController::class, 'restore']);
        Route::get('/profiles/trashed', [ProfileController::class, 'trashed']);
        Route::post('/profiles/{id}/restore', [ProfileController::class, 'restore']);
        Route::get('/role_requests',[RoleRequestController::class, 'index']);
        Route::post('/role_request/{id}/approve',[RoleRequestController::class, 'approve']);
        Route::post('/role_request/{id}/reject',[RoleRequestController::class, 'reject']);
    });


    // ------------------------------------------
    // RUTAS EXCLUSIVAS PARA CLIENTES
    // ------------------------------------------
    Route::group(['middleware' => ['role:client']], function () {
        Route::get('/user/likes', [LikesController::class, 'myLikes']);
        Route::post('/property/{id}/like', [LikesController::class, 'store']);
        Route::post('/property/{property}/comment', [CommentaryController::class, 'store']);
        Route::get('/role_requestById',[RoleRequestController::class, 'showMyRequest']);
        Route::post('/role_request',[RoleRequestController::class, 'store']);

    });
});
