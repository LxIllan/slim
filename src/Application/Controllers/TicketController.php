<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Helpers\Util;
use App\Application\DAO\TicketDAO;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TicketController
{
	/**
	 * @var TicketDAO
	 */
	private TicketDAO $ticketDAO;

	public function __construct()
	{
		$this->ticketDAO = new TicketDAO();
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
		$result = $sellDAO->sell($body['items'], $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($result, "ticket", 201));
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
		$ticket = $this->ticketDAO->getById(intval($args['id']));
		if ($ticket) {
			$response->getBody()->write(Util::encodeData($ticket, "ticket"));
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
		$params = $request->getQueryParams();
		$from = $params['from'] ?? date("Y-m-d");
		$to = $params['to'] ?? date("Y-m-d");
		$tickets = $this->ticketDAO->getAll($jwt['branch_id'], $from, $to);
		$response->getBody()->write(Util::encodeData($tickets, "tickets"));
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
		$result = $this->ticketDAO->cancel(intval($args['id']));
		$response->getBody()->write(Util::encodeData($result, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
