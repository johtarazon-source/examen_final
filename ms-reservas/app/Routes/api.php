<?php

use App\Controllers\MesaController;
use App\Controllers\SaludController;
use Slim\App;

/**
 * Rutas del microservicio. Se amplían día a día (ver docs/PLAN_16_DIAS.md).
 */
return function (App $app): void {
    $app->get('/salud', SaludController::class);

    // Mesas (día 4)
    $app->get('/mesas', MesaController::class . ':listar');
    $app->post('/mesas', MesaController::class . ':crear');
    $app->get('/mesas/{id:[0-9]+}', MesaController::class . ':obtener');
    $app->put('/mesas/{id:[0-9]+}', MesaController::class . ':editar');
    $app->patch('/mesas/{id:[0-9]+}/estado', MesaController::class . ':cambiar_estado');
};
