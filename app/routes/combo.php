<?php

declare(strict_types=1);

use App\Application\Controller\AlimentoController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/combos', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        $alimentoController = new AlimentoController();
        $dishes = [];

        if (isset($body['branchId'])) {
            $dishes = $alimentoController->listarPaquetes(intval($body['branchId']));
        }

        $response->getBody()->write(json_encode($dishes));

        return $response->withHeader('Content-Type', 'application/json');
    });
};
