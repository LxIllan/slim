<?php

declare(strict_types=1);

use App\Application\Controllers\TicketController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\App;

return function (App $app) {
	/**
	 * @api /tickets
	 * @method GET
	 * @description Get history tickets
	 */
	$app->get('/tickets', function (Request $request, Response $response) {
		$ticketController = new TicketController();
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$tickets = $ticketController->getAll($jwt['branch_id'], $params['from'], $params['to']);
		$response->getBody()->write(Util::encodeData($tickets, "tickets"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /tickets/{id}
	 * @method GET
	 * @description Get ticket by id
	 */
	$app->get('/tickets/{id}', function (Request $request, Response $response, $args) {
		$ticketController = new TicketController();
		$ticket = $ticketController->getById(intval($args['id']));        
		if ($ticket) {
			$response->getBody()->write(Util::encodeData($ticket, "ticket"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});
};
