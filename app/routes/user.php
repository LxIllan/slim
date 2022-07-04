<?php

declare(strict_types=1);

use App\Application\Controller\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {

        $data = array('name' => 'Bob', 'age' => 40);
        $payload = json_encode($data);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/test', function (Request $request, Response $response) {
        $response->getBody()->write('test!');
        return $response;
    });

    $app->post('/users', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $user = UserController::create($body['username'], $body['email'], $body['password']);
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/post', function (Request $request, Response $response) {
        $payload = json_encode($request->getParsedBody());        
        $token = $request->getAttribute("token");
        $response->getBody()->write(json_encode($token['id']));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
