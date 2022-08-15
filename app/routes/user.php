<?php

declare(strict_types=1);

use App\Application\Controller\UserController;
use App\Application\Helper\Util;
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
        $userController = new UserController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $body["branch_id"] = $jwt["branch_id"];
        $user = $userController->create($body);
        $response->getBody()->write(Util::orderReturnData($user, "user", 201));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /users
     * @method POST
     * @description Create user
     */
    $app->post('/users/exist-email', function (Request $request, Response $response) {
        $userController = new UserController();
        $body = $request->getParsedBody();
        $existEmail = $userController->existEmail($body['email']);
        $response->getBody()->write(Util::orderReturnData($existEmail, "exist"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /cashiers
     * @method GET
     * @description Get all cashiers from a branch
     */
    $app->get('/cashiers', function (Request $request, Response $response) {
        $userController = new UserController();
        $token = $request->getAttribute("token");
        $cashiers = $userController->getCashiers($token['branch_id']);
        $response->getBody()->write(Util::orderReturnData($cashiers, "cashiers"));
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
        $response->getBody()->write(Util::orderReturnData($user, "user"));
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
        $response->getBody()->write(Util::orderReturnData($user, "user"));
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
        $response->getBody()->write(Util::orderReturnData($user, "user"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
