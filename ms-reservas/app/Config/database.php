<?php

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;
use PDOException;

const ERROR_BASE_DE_DATOS_INEXISTENTE = 1049;

/** Ruta del script SQL entregado con el ejercicio (database/<ms_nombre>.sql). Nunca se modifica. */
function ruta_script_sql(): string
{
    $raiz = dirname(__DIR__, 2);

    return $raiz . '/database/' . str_replace('-', '_', basename($raiz)) . '.sql';
}

/**
 * Conecta Eloquent a la base PROPIA del microservicio. Si la base o sus tablas no existen,
 * las crea ejecutando el script SQL del ejercicio tal cual (estructura y datos iniciales).
 */
function configurar_base_de_datos(): void
{
    static $configurada = false;

    if ($configurada) {
        return;
    }

    asegurar_esquema();

    capsule()->addConnection(parametros_conexion((string) Env::obtener('DB_DATABASE', '')));

    $configurada = true;
}

/** Gestor único de Eloquent para el proceso (se crea la primera vez que se necesita). */
function capsule(): Capsule
{
    static $capsule = null;

    if ($capsule === null) {
        $capsule = new Capsule();
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    return $capsule;
}

function parametros_conexion(string $base_de_datos): array
{
    return [
        'driver' => 'mysql',
        'host' => Env::obtener('DB_HOST', '127.0.0.1'),
        'port' => (int) Env::obtener('DB_PORT', '3306'),
        'database' => $base_de_datos,
        'username' => Env::obtener('DB_USERNAME', 'root'),
        'password' => Env::obtener('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];
}

/** Crea la base y aplica el script SQL solo si faltan las tablas que éste define. */
function asegurar_esquema(): void
{
    $servidor = (string) Env::obtener('DB_HOST', '127.0.0.1');
    $puerto = (int) Env::obtener('DB_PORT', '3306');
    $base_de_datos = (string) Env::obtener('DB_DATABASE', '');
    $usuario = (string) Env::obtener('DB_USERNAME', 'root');
    $contrasena = (string) Env::obtener('DB_PASSWORD', '');
    $opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

    if ($base_de_datos === '') {
        throw new \RuntimeException('Falta DB_DATABASE en el .env del microservicio.');
    }

    try {
        $pdo = new PDO("mysql:host={$servidor};port={$puerto};dbname={$base_de_datos};charset=utf8mb4", $usuario, $contrasena, $opciones);
    } catch (PDOException $excepcion) {
        if (($excepcion->errorInfo[1] ?? 0) !== ERROR_BASE_DE_DATOS_INEXISTENTE) {
            throw $excepcion;
        }

        $pdo = new PDO("mysql:host={$servidor};port={$puerto};charset=utf8mb4", $usuario, $contrasena, $opciones);
        $nombre_escapado = str_replace('`', '``', $base_de_datos);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$nombre_escapado}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$nombre_escapado}`");
    }

    $esquema = (string) file_get_contents(ruta_script_sql());
    preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $esquema, $coincidencias);
    $tablas = array_values(array_unique($coincidencias[1]));

    $marcadores = implode(', ', array_fill(0, count($tablas), '?'));
    $consulta = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ({$marcadores})"
    );
    $consulta->execute($tablas);

    if ((int) $consulta->fetchColumn() === count($tablas)) {
        return;
    }

    foreach (explode(';', $esquema) as $sentencia) {
        $sentencia = trim($sentencia);

        if ($sentencia !== '') {
            $pdo->exec($sentencia);
        }
    }
}
