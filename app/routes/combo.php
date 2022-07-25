<?php

declare(strict_types=1);

<<<<<<< HEAD
use App\Application\Controller\FoodController;
=======
use App\Application\Controller\AlimentoController;
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/combos', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

<<<<<<< HEAD
        $alimentoController = new FoodController();
=======
        $alimentoController = new AlimentoController();
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
        $dishes = [];

        if (isset($body['branchId'])) {
            $dishes = $alimentoController->listarPaquetes(intval($body['branchId']));
        }

        $response->getBody()->write(json_encode($dishes));

        return $response->withHeader('Content-Type', 'application/json');
    });
};
