<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Usuario del sistema (tabla `usuarios` de elcuervo_auth).
 *
 * @property int $id
 * @property string $nombre
 * @property string $correo
 * @property string $usuario
 * @property string $contrasena
 * @property string $rol
 * @property string|null $token
 * @property bool $sesion_activa
 * @property string $estado
 */
class Usuario extends Model
{
    protected $table = 'usuarios';

    public $timestamps = true;

    protected $hidden = ['contrasena', 'token'];

    protected $casts = [
        'sesion_activa' => 'boolean',
    ];

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
