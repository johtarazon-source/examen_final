<?php

use App\Controllers\AuthController;
use App\Controllers\SaludController;
use Slim\App;

/**
 * Rutas del microservicio. Se amplían día a día (ver docs/PLAN_16_DIAS.md).
 */
return function (App $app): void {
    $app->get('/salud', SaludController::class);

    $app->post('/auth/login', AuthController::class . ':login');
    $app->post('/auth/logout', AuthController::class . ':logout');
    $app->get('/auth/validar', AuthController::class . ':validar');
};
