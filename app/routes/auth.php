<?php

declare(strict_types=1);

use App\Application\Controller\UserController;
use App\Application\Helper\Util;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PsrJwt\Factory\JwtMiddleware;
use ReallySimpleJWT\Token;
use Slim\App;

return function (App $app) {
    $app->post('/login', function (Request $request, Response $response) {
        $userController = new UserController();
        $body = $request->getParsedBody();
        $user = $userController->validateSession($body['email'], $body['password']);
        if ($user) {
            $payload = [
                'iat' => time(),
                'exp' => time() + 9999999,
                'user_id' => intval($user['id']),
                'branch_id' => intval($user['branch_id']),
                'root' => $user['root']
            ];
            $secret = $_ENV["JWT_SECRET"];
            $token = Token::customPayload($payload, $secret);
            $response->getBody()->write(Util::orderReturnData($token, "jwt"));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
            return $response->withHeader('Content-Type', 'application/json');
        }
    });

    $app->get('/logout', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode('log out'));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(JwtMiddleware::json($_ENV['JWT_SECRET'], 'jwt', ['Authorisation Failed']));
};
