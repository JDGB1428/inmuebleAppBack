<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="API de Inmuebles",
 *     version="1.0",
 *     description="Documentación de la aplicación de Inmuebles"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Endpoint de prueba para Swagger",
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa"
     *     )
     * )
     */
    public function test()
    {
        return response()->json(['status' => 'ok']);
    }
}
