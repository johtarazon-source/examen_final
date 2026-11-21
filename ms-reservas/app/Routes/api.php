<?php

use App\Controllers\SaludController;
use App\Controllers\SesionController;
use App\Middleware\Auth;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * Rutas del microservicio. Se amplían día a día (ver docs/PLAN_16_DIAS.md).
 */
return function (App $app): void {
    // Pública: comprobación de que el servicio está arriba.
    $app->get('/salud', SaludController::class);

    // Protegidas: requieren token Bearer válido de ms-auth. Las rutas de los próximos días van aquí dentro.
    $app->group('', function (RouteCollectorProxy $protegidas): void {
        $protegidas->get('/sesion', SesionController::class);
    })->add(new Auth());
};
