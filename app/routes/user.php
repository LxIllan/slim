<?php

declare(strict_types=1);

use App\Application\Controllers\UserController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
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
		$response->getBody()->write(Util::encodeData($user, "user", 201));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /profile
	 * @method GET
	 * @description Get user by id
	 */
	$app->get('/profile', function (Request $request, Response $response) {
		$userController = new UserController();
		$token = $request->getAttribute("token");
		$user = $userController->getUserById($token['user_id']);
		$response->getBody()->write(Util::encodeData($user, "user"));
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
		$response->getBody()->write(Util::encodeData($existEmail, "exist"));
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
		$response->getBody()->write(Util::encodeData($cashiers, "cashiers"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /cashiers/reset-password
	 * @method POST
	 * @description Reset cashier password
	 */
	$app->post('/cashiers/reset-password', function (Request $request, Response $response) {
		$userController = new UserController();
		$body = $request->getParsedBody();
		$wasUpdated = $userController->resetPassword(intval($body['user_id']));
		$response->getBody()->write(Util::encodeData($wasUpdated, "response"));
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
		if ($user) {
			$response->getBody()->write(Util::encodeData($user, "user"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
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
		if ($user) {
			$response->getBody()->write(Util::encodeData($user, "user"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});

	/**
	 * @api /users/{id}
	 * @method DELETE
	 * @description Delete user by id
	 */
	$app->delete('/users/{id}', function (Request $request, Response $response, $args) {
		$userController = new UserController();
		$wasDeleted = $userController->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	});
};
