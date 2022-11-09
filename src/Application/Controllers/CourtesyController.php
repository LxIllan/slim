<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Helpers\Util;
use App\Application\DAO\CourtesyDAO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CourtesyController
{
	/**
	 * @var CourtesyDAO
	 */
	private CourtesyDAO $courtesyDAO;

	public function __construct()
	{
		$this->courtesyDAO = new CourtesyDAO();
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function create(Request $request, Response $response): Response
	{
		$sellDAO = new \App\Application\DAO\SellDAO();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$result = $sellDAO->courtesy($body['items'], $body['reason'], $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($result, "response"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function getAll(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$from = $params['from'] ?? date("Y-m-d");
		$to = $params['to'] ?? date("Y-m-d");
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$courtesies = $this->courtesyDAO->getAll($jwt['branch_id'], $from, $to, $getDeleted);
		$response->getBody()->write(Util::encodeData($courtesies, "courtesies"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function cancel(Request $request, Response $response, $args): Response
	{
		$response->getBody()->write(Util::encodeData($args['id'], "id"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
