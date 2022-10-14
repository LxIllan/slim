<?php

declare(strict_types=1);

use App\Application\Controllers\TicketController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
};
