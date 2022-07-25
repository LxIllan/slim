<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PsrJwt\Factory\JwtMiddleware;
use PsrJwt\Factory\Jwt;
use ReallySimpleJWT\Token;
use Slim\App;

return function (App $app) {
    $app->get('/login', function (Request $request, Response $response) {        
        
        $payload = [
            'iat' => time(),
            'uid' => 1,
            'exp' => time() + 10,
            'iss' => 'localhost'
        ];
        
        // $secret = $_ENV['JWT_SECRET'];
        $secret = 's' . $_ENV['JWT_SECRET'];
        
        $token = Token::customPayload($payload, $secret);
        $response->getBody()->write(json_encode($token));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/logout', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode('log out'));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(JwtMiddleware::json($_ENV['JWT_SECRET'], 'jwt', ['Authorisation Failed']));
};
