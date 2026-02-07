<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::group(['middleware' => [
        'role:admin|agent',
        'permission:
            ver inmuebles,
            crear inmuebles,
            editar inmuebles,
            eliminar inmuebles']],
            function(){
                Route::get('/property',[PropertyController::class, 'index']);
                Route::post('/property', [PropertyController::class, 'store']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
