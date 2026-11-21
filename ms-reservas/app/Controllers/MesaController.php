<?php

namespace App\Controllers;

use App\Exceptions\AppException;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Mesa;
use App\Support\Peticion;
use App\Support\Respuesta;
use App\Support\Validator;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use function App\Config\configurar_base_de_datos;

class MesaController
{
    public function __construct()
    {
        try {
            configurar_base_de_datos();
        } catch (PDOException $excepcion) {
            error_log('ms-reservas: base de datos no disponible: ' . $excepcion->getMessage());

            throw new AppException('El servicio de reservas no está disponible en este momento.', 503);
        }
    }

    /** GET /mesas — lista las mesas; filtro opcional ?estado=. */
    public function listar(Request $peticion, Response $respuesta): Response
    {
        $consulta = Mesa::query()->orderBy('numero');
        $estado = Peticion::consulta($peticion, 'estado');

        if ($estado !== null && trim($estado) !== '') {
            $validador = new Validator(['estado' => $estado]);
            $estado = $validador->opcion('estado', 'El estado', Mesa::ESTADOS);
            $validador->verificar();

            $consulta->where('estado', $estado);
        }

        $mesas = $consulta->get()->map(fn (Mesa $mesa) => $mesa->to_array())->all();

        return Respuesta::json($respuesta, 200, 'Mesas consultadas correctamente.', $mesas);
    }

    /** GET /mesas/{id} */
    public function obtener(Request $peticion, Response $respuesta, array $args): Response
    {
        $mesa = $this->buscar((int) $args['id']);

        return Respuesta::json($respuesta, 200, 'Mesa consultada correctamente.', $mesa->to_array());
    }

    /** POST /mesas — crea una mesa disponible. */
    public function crear(Request $peticion, Response $respuesta): Response
    {
        $datos = $this->validar_datos(Peticion::cuerpo($peticion));

        $this->verificar_numero_libre($datos['numero']);

        $mesa = Mesa::create($datos + ['estado' => 'disponible']);

        return Respuesta::json($respuesta, 201, 'Mesa creada correctamente.', $mesa->to_array());
    }

    /** PUT /mesas/{id} — edita número y capacidad. */
    public function editar(Request $peticion, Response $respuesta, array $args): Response
    {
        $mesa = $this->buscar((int) $args['id']);
        $datos = $this->validar_datos(Peticion::cuerpo($peticion));

        $this->verificar_numero_libre($datos['numero'], (int) $mesa->id);

        $mesa->numero = $datos['numero'];
        $mesa->capacidad = $datos['capacidad'];
        $mesa->save();

        return Respuesta::json($respuesta, 200, 'Mesa actualizada correctamente.', $mesa->to_array());
    }

    /** PATCH /mesas/{id}/estado — cambia el estado de la mesa. */
    public function cambiar_estado(Request $peticion, Response $respuesta, array $args): Response
    {
        $mesa = $this->buscar((int) $args['id']);

        $validador = new Validator(Peticion::cuerpo($peticion));
        $estado = $validador->opcion('estado', 'El estado', Mesa::ESTADOS, true);
        $validador->verificar();

        $mesa->estado = $estado;
        $mesa->save();

        return Respuesta::json($respuesta, 200, 'Estado de la mesa actualizado correctamente.', $mesa->to_array());
    }

    /** Valida número y capacidad (capacidad mayor que 0). Devuelve los valores limpios. */
    private function validar_datos(array $entrada): array
    {
        $validador = new Validator($entrada);
        $numero = $validador->texto('numero', 'El número de mesa', 20, true);
        $capacidad = $validador->entero('capacidad', 'La capacidad', 1, 100, true);
        $validador->verificar();

        return ['numero' => $numero, 'capacidad' => $capacidad];
    }

    /** 409 si otra mesa ya usa ese número (la colación de la BD ignora mayúsculas). */
    private function verificar_numero_libre(string $numero, ?int $id_excluido = null): void
    {
        $consulta = Mesa::query()->where('numero', $numero);

        if ($id_excluido !== null) {
            $consulta->where('id', '!=', $id_excluido);
        }

        if ($consulta->exists()) {
            throw new ConflictException('Ya existe una mesa con ese número.');
        }
    }

    private function buscar(int $id): Mesa
    {
        $mesa = Mesa::query()->find($id);

        if ($mesa === null) {
            throw new NotFoundException('La mesa solicitada no existe.');
        }

        return $mesa;
    }
}
