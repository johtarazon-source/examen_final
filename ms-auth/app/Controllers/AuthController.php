<?php

namespace App\Controllers;

use App\Exceptions\AppException;
use App\Exceptions\UnauthorizedException;
use App\Models\Usuario;
use App\Support\Peticion;
use App\Support\Respuesta;
use App\Support\Validator;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use function App\Config\configurar_base_de_datos;

class AuthController
{
    public function __construct()
    {
        try {
            configurar_base_de_datos();
        } catch (PDOException $excepcion) {
            error_log('ms-auth: base de datos no disponible: ' . $excepcion->getMessage());

            throw new AppException('El servicio de autenticación no está disponible en este momento.', 503);
        }
    }

    /** POST /auth/login — acepta nombre de usuario o correo. */
    public function login(Request $peticion, Response $respuesta): Response
    {
        $datos = Peticion::cuerpo($peticion);

        $validador = new Validator($datos);
        $identificador = $validador->texto('usuario', 'El usuario', 150, true);

        // La contraseña no se recorta (trim) para no alterar lo que escribió la persona.
        $contrasena = is_string($datos['contrasena'] ?? null) ? $datos['contrasena'] : '';

        if ($contrasena === '') {
            $validador->agregar_error('contrasena', 'La contraseña es un campo obligatorio.');
        } elseif (strlen($contrasena) > 255) {
            $validador->agregar_error('contrasena', 'La contraseña no puede superar 255 caracteres.');
        }

        $validador->verificar();

        $usuario = Usuario::query()
            ->where('usuario', $identificador)
            ->orWhere('correo', $identificador)
            ->first();

        // Mismo mensaje si el usuario no existe o la clave es errónea (no revela cuál de los dos falló).
        if ($usuario === null || !$this->contrasena_valida($contrasena, (string) $usuario->contrasena)) {
            throw new UnauthorizedException('Credenciales incorrectas.');
        }

        if ($usuario->estado !== 'activo') {
            throw new UnauthorizedException('El usuario está inactivo.');
        }

        // Un nuevo inicio de sesión reemplaza el token anterior (un solo token por usuario).
        $usuario->token = bin2hex(random_bytes(32));
        $usuario->sesion_activa = true;
        $usuario->save();

        return Respuesta::json($respuesta, 200, 'Inicio de sesión exitoso.', [
            'token' => $usuario->token,
            'usuario' => $usuario->to_array(),
        ]);
    }

    /** POST /auth/logout — invalida el token actual. */
    public function logout(Request $peticion, Response $respuesta): Response
    {
        $usuario = $this->usuario_autenticado($peticion);

        $usuario->token = null;
        $usuario->sesion_activa = false;
        $usuario->save();

        return Respuesta::json($respuesta, 200, 'Sesión cerrada correctamente.');
    }

    /** GET /auth/validar — confirma que el token es válido y devuelve el usuario. */
    public function validar(Request $peticion, Response $respuesta): Response
    {
        $usuario = $this->usuario_autenticado($peticion);

        return Respuesta::json($respuesta, 200, 'Token válido.', ['usuario' => $usuario->to_array()]);
    }

    /** Usuario dueño del token Bearer, con sesión activa y estado activo; 401 en cualquier otro caso. */
    private function usuario_autenticado(Request $peticion): Usuario
    {
        $token = Peticion::token($peticion);

        if ($token === '') {
            throw new UnauthorizedException('Falta el token de autenticación (Authorization: Bearer <token>).');
        }

        $usuario = Usuario::query()
            ->where('token', $token)
            ->where('sesion_activa', true)
            ->where('estado', 'activo')
            ->first();

        if ($usuario === null) {
            throw new UnauthorizedException('Token inválido o sesión no activa.');
        }

        return $usuario;
    }

    /** Admite contraseñas en texto plano (datos iniciales del ejercicio) o con hash de password_hash(). */
    private function contrasena_valida(string $ingresada, string $almacenada): bool
    {
        if ($almacenada === '') {
            return false;
        }

        if (password_get_info($almacenada)['algo'] !== null) {
            return password_verify($ingresada, $almacenada);
        }

        return hash_equals($almacenada, $ingresada);
    }
}
