<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use Exception;
use ReallySimpleJWT\Token;
use App\Application\DAO\AuthDAO;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
	/**
	 * @var AuthDAO $authDAO
	 */
	private AuthDAO $authDAO;

	public function __construct()
	{
		$this->authDAO = new AuthDAO();
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function authenticate(Request $request, Response $response): Response
	{
		$body = $request->getParsedBody();
		if (!Util::validateEmail($body['email'])) {
			throw new Exception('Invalid email');
		}
		$user = $this->authDAO->authenticate($body['email'], $body['password']);
		if ($user) {
			$payload = [
				'iat' => time(),
				'exp' => time() + 99999999,
				'user_id' => intval($user['id']),
				'branch_id' => intval($user['branch_id']),
				'root' => boolval($user['root'])
			];
			$secret = $_ENV["JWT_SECRET"];
			$token = Token::customPayload($payload, $secret);
			$response->getBody()->write(Util::encodeData($token, "jwt"));
		} else {
			throw new Exception('Invalid credentials');
		}
		return $response->withHeader('Content-Type', 'application/json');
	}
}
