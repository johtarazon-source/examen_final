<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mesa del restaurante (tabla `mesas` de elcuervo_reservas).
 *
 * @property int $id
 * @property string $numero
 * @property int $capacidad
 * @property string $estado
 */
class Mesa extends Model
{
    public const ESTADOS = ['disponible', 'reservada', 'ocupada', 'fuera_servicio'];

    protected $table = 'mesas';

    public $timestamps = true;

    protected $fillable = ['numero', 'capacidad', 'estado'];

    protected $casts = [
        'capacidad' => 'integer',
    ];

    /** Datos públicos de la mesa para las respuestas de la API. */
    public function to_array(): array
    {
        return [
            'id' => (int) $this->id,
            'numero' => $this->numero,
            'capacidad' => (int) $this->capacidad,
            'estado' => $this->estado,
        ];
    }
}
