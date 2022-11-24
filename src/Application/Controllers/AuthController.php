<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use Exception;
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
		if (!Util::isEmailValid($body['email'])) {
			throw new Exception('Invalid email');
		}

		$token = $this->authDAO->authenticate($body['email'], $body['password']);

		if (is_null($token)) {
			throw new Exception('Invalid credentials');
		}

		$response->getBody()->write(Util::encodeData($token, "jwt"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function switchBranch(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$token = $this->authDAO->switchBranch($jwt, intval($body['branch_id']));
		$response->getBody()->write(Util::encodeData($token, "jwt"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
