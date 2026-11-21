<?php

namespace App\Controllers;

use App\Support\Respuesta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * GET /sesion — devuelve el usuario autenticado por el middleware Auth (útil para comprobar el token).
 */
class SesionController
{
    public function __invoke(Request $peticion, Response $respuesta): Response
    {
        return Respuesta::json($respuesta, 200, 'Token válido.', ['usuario' => $peticion->getAttribute('usuario')]);
    }
}
