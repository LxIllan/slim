<?php

declare(strict_types=1);

use App\Application\Controller\AlimentoController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/foods', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        
        $alimentoController = new AlimentoController();
        $foods = [];
        
        if (isset($body['id'])) {
            $foods = $alimentoController->consultarAlimento(intval($body['id']));
        }

        if (isset($body['branchId'])) {
            $foods = $alimentoController->listarAlimentos(intval($body['branchId']));
        }        
        
        $response->getBody()->write(json_encode($foods));

        return $response->withHeader('Content-Type', 'application/json');
    });
    
};
