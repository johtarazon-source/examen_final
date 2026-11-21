<?php

namespace App\Middleware;

use App\Exceptions\AppException;
use App\Exceptions\UnauthorizedException;
use App\Models\UsuarioAuth;
use App\Support\Peticion;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use function App\Config\agregar_conexion_lectura;

/**
 * Exige un token Bearer válido emitido por ms-auth. Lo comprueba con una conexión de SOLO LECTURA
 * a la base de ms-auth (DB_AUTH_DATABASE): sesión activa y usuario activo. Si es válido, deja los
 * datos públicos del usuario en el atributo `usuario` de la petición.
 */
class Auth
{
    public function __invoke(Request $peticion, RequestHandler $manejador): Response
    {
        $token = Peticion::token($peticion);

        if ($token === '') {
            throw new UnauthorizedException('Falta el token de autenticación (Authorization: Bearer <token>).');
        }

        try {
            agregar_conexion_lectura('auth', 'DB_AUTH_DATABASE', 'elcuervo_auth');

            $usuario = UsuarioAuth::query()
                ->where('token', $token)
                ->where('sesion_activa', true)
                ->where('estado', 'activo')
                ->first();
        } catch (PDOException $excepcion) {
            error_log('Auth: base de autenticación no disponible: ' . $excepcion->getMessage());

            throw new AppException('El servicio de autenticación no está disponible en este momento.', 503);
        }

        if ($usuario === null) {
            throw new UnauthorizedException('Token inválido o sesión no activa.');
        }

        return $manejador->handle($peticion->withAttribute('usuario', $usuario->to_array()));
    }
}
