<?php

declare(strict_types=1);

use App\Application\Controller\CategoryController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->post('/categories', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $category = CategoryController::create($body['category']);
        $response->getBody()->write(json_encode($category));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/categories', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        if (isset($body['id'])) {
            $categories = CategoryController::get($body['id']);
        } else {
            $categories = CategoryController::getAll();
        }

        $response->getBody()->write(json_encode($categories));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/categories', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();

        $category = CategoryController::update(intval($body['id']), $body['category']);

        $response->getBody()->write(json_encode($category));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
