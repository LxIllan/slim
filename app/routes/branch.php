<?php

declare(strict_types=1);

use App\Application\Controller\BranchController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->post('/branches', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $branch = BranchController::create($body['name'], $body['location'], $body['phone_number']);
        $response->getBody()->write(json_encode($branch));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/branches', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        if (isset($body['id'])) {
            $branches = BranchController::get($body['id']);
        } else {
            $branches = BranchController::getAll();
        }

        $response->getBody()->write(json_encode($branches));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/branches', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();

        $category = BranchController::update(intval($body['id']), $body['category']);

        $response->getBody()->write(json_encode($category));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
