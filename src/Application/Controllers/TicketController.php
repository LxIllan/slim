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

	// /**
	//  * @param int $id
	//  * @return bool
	//  */
	// public function delete(int $id): bool
	// {
	//     return $this->ticketDAO->delete($id);
	// }
}
