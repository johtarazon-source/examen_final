<?php

use App\Controllers\SaludController;
use Slim\App;


return function (App $app): void {
    $app->get('/salud', SaludController::class);
};
