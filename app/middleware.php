<?php

declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Application\Middleware\CorsMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tuupola\Middleware\JwtAuthentication;
use Slim\App;

return function (App $app) {
	$app->add(SessionMiddleware::class);
	$app->add(JsonBodyParserMiddleware::class);

	$app->options('/{routes:.*}', function (Request $request, Response $response) {
		// CORS Pre-Flight OPTIONS Request Handler
		return $response;
	});
	$app->add(CorsMiddleware::class);
	// JWT Authentication
	$app->add(new JwtAuthentication([
		"secret" => $_ENV["JWT_SECRET"],
		"path" => [
			"/branches",
			"/categories",
			"/combos",
			"/courtesy",
			"/dishes",
			"/expenses",
			"/foods",
			"/histories",
			"/logout",
			"/preferences",        
			"/products",
			"/sell",
			"/special-dishes",
			"/tickets",
			"/users"
		],
		"error" => function ($response, $arguments) {
			$data["statusCode"] = 401;
			$data["Error"] = ["type" => "UNAUTHENTICATED", "description" => "The request requires valid user authentication."];
			$response->getBody()->write(json_encode($data));
			return $response->withHeader("Content-Type", "application/json");
		},
		"secure" => true
	]));
};
