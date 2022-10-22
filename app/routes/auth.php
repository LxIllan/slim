<?php

declare(strict_types=1);

use App\Application\Controllers\AuthController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;

return function (App $app) {
	$app->post('/login', AuthController::class . ':authenticate');

	$app->get('/logout', function (Request $request, Response $response) {
		$response->getBody()->write(json_encode('log out'));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /branches/num-ticket
	 * @method GET
	 * @description Get num ticket by branch id
	 */
	$app->get('/branches/check-jwt', function (Request $request, Response $response) {
		$jwt = $request->getAttribute("token");
		$response->getBody()->write(Util::encodeData($jwt, "jwt"));
		return $response->withHeader('Content-Type', 'application/json');
	});
};
