<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\DAO\PreferenceDAO;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

class PreferenceController
{
	/**
	 * @var PreferenceDAO $preferenceDAO
	 */
	private PreferenceDAO $preferenceDAO;

	public function __construct()
	{
		$this->preferenceDAO = new PreferenceDAO();
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function create(Request $request, Response $response): Response
	{		
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();        
		$body["branch_id"] = $jwt["branch_id"];
		$branch = $this->preferenceDAO->create($body);
		$response->getBody()->write(Util::encodeData($branch, "preference", 201));
		return $response->withHeader('Content-Type', 'application/json');
	}	

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getById(Request $request, Response $response, array $args): Response
	{
		$jwt = $request->getAttribute("token");
		if (preg_match('(\d)', $args['id'])) {
			$preference = $this->preferenceDAO->getById(intval($args['id']));
		} else {
			$preference = $this->preferenceDAO->getByKey($args['id'], $jwt["branch_id"]);
		}
		if ($preference) {
			$response->getBody()->write(Util::encodeData($preference, "preference"));
		return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function getAll(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$preferences = $this->preferenceDAO->getAll($jwt["branch_id"]);
		$response->getBody()->write(Util::encodeData($preferences, "preferences"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function edit(Request $request, Response $response, array $args): Response
	{
		$body = $request->getParsedBody();
		$preference = $this->preferenceDAO->edit(intval($args['id']), $body);
		if ($preference) {
			$response->getBody()->write(Util::encodeData($preference, "preference"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function delete(Request $request, Response $response, array $args): Response
	{
		$wasDeleted = $this->preferenceDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
