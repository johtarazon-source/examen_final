<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Usuario de ms-auth visto en SOLO LECTURA (tabla `usuarios` de la base elcuervo_auth, conexión `auth`).
 * Sirve para validar el token de sesión; este microservicio nunca escribe en la base de autenticación.
 *
 * @property int $id
 * @property string $nombre
 * @property string $correo
 * @property string $usuario
 * @property string $rol
 * @property string|null $token
 * @property bool $sesion_activa
 * @property string $estado
 */
class UsuarioAuth extends Model
{
    protected $connection = 'auth';

    protected $table = 'usuarios';

    protected $hidden = ['contrasena', 'token'];

    protected $casts = [
        'sesion_activa' => 'boolean',
    ];

    protected static function booted(): void
    {
        $bloquear = static function (): never {
            throw new LogicException('La base de autenticación es de solo lectura en este microservicio.');
        };

        static::saving($bloquear);
        static::deleting($bloquear);
    }

    /** Datos públicos del usuario (nunca incluye contraseña ni token). */
    public function to_array(): array
    {
        return [
            'id' => (int) $this->id,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'usuario' => $this->usuario,
            'rol' => $this->rol,
        ];
    }
}
