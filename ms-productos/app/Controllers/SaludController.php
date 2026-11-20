<?php

namespace App\Controllers;

use App\Support\Respuesta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Comprobación pública de que el microservicio está arriba (no toca la base de datos).
 */
class SaludController
{
    public function __invoke(Request $peticion, Response $respuesta): Response
    {
        $servicio = basename(dirname(__DIR__, 2));

        return Respuesta::json($respuesta, 200, "{$servicio} operativo.", ['servicio' => $servicio, 'restaurante' => 'El Cuervo']);
    }
}
