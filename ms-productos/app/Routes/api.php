<?php

use App\Controllers\SaludController;
use Slim\App;

/**
 * Rutas del microservicio. Se amplían día a día (ver docs/PLAN_16_DIAS.md).
 */
return function (App $app): void {
    $app->get('/salud', SaludController::class);
};
