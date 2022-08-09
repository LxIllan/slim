<?php

declare(strict_types=1);

use App\Application\Controller\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    /**
     * @api /users
     * @method POST
     * @description Create user
     */
    $app->post('/users', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $userController = new UserController();
        $user = $userController->create($body);
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /users
     * @method GET
     * @description Get all users from a branch
     */
    $app->get('/users', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $userController = new UserController();
        $users = $userController->getCashiers(intval($body['branch_id']));
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /users/{id}
     * @method GET
     * @description Get user by id
     */
    $app->get('/users/{id}', function (Request $request, Response $response, $args) {
        $userController = new UserController();
        $user = $userController->getUserById(intval($args['id']));
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /users/{id}
     * @method PUT
     * @description Edit user by id
     */
    $app->put('/users/{id}', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $userController = new UserController();
        $user = $userController->edit(intval($args['id']), $body);
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /users/{id}
     * @method DELETE
     * @description Delete user by id
     */
    $app->delete('/users/{id}', function (Request $request, Response $response, $args) {
        $userController = new UserController();
        $user = $userController->delete(intval($args['id']));
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/post', function (Request $request, Response $response) {
        $token = $request->getAttribute("token");
        $response->getBody()->write(json_encode($token['id']));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
