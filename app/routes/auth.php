<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PsrJwt\Factory\JwtMiddleware;
use ReallySimpleJWT\Token;
use Slim\App;
use App\Application\Controller\UserController;
use App\Application\Helper\Util;

return function (App $app) {
    $app->post('/login', function (Request $request, Response $response) {
        $body = $request->getParsedBody();  
        $userController = new UserController();
        $user = $userController->validateSession($body['email'], $body['password']);
        
        if ($user) {
            $payload = [
                'iat' => time(),
                'exp' => time() + 9999999,
                'user_id' => intval($user['id']),
                'username' => $user['username'],
                'branch_id' => intval($user['branch_id']),
                'root' => $user['root']
            ];            
            $secret = $_ENV["JWT_SECRET"];
            $token = Token::customPayload($payload, $secret);
            $response->getBody()->write(Util::orderReturnData($token));
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
