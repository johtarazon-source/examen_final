<?php

declare(strict_types=1);

/**
 * Crea la base de datos del microservicio y aplica el script SQL del ejercicio si faltan las tablas.
 * Uso (desde la carpeta del microservicio):  php scripts/preparar_bd.php
 */

use App\Config\Env;

use function App\Config\asegurar_esquema;
use function App\Config\ruta_script_sql;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require __DIR__ . '/../vendor/autoload.php';

Env::cargar(__DIR__ . '/../.env');

asegurar_esquema();

fwrite(STDOUT, 'Base "' . Env::obtener('DB_DATABASE') . '" lista (script: ' . basename(ruta_script_sql()) . ').' . PHP_EOL);
