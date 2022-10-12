<?php

declare(strict_types=1);

use App\Application\Controllers\HistoryController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    /**
     * @api /histories/sales
     * @method GET
     * @description Get history sales
     */
    $app->get('/histories/sales', function (Request $request, Response $response) {
        $historyController = new HistoryController();
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $sales = $historyController->getSales($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(Util::encodeData($sales, "sales"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/courtesies
     * @method GET
     * @description Get history courtesies
     */
    $app->get('/histories/courtesies', function (Request $request, Response $response) {
        $historyController = new HistoryController();
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $courtesies = $historyController->getCourtesies($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(Util::encodeData($courtesies, "courtesies"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/supplied-foods
     * @method GET
     * @description Get history supplied foods
     */
    $app->get('/histories/supplied-foods', function (Request $request, Response $response) {
        $historyController = new HistoryController();
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $suppliedFoods = $historyController->getSuppliedFood($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(Util::encodeData($suppliedFoods, "supplied_foods"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/altered-foods
     * @method GET
     * @description Get history altered foods
     */
    $app->get('/histories/altered-foods', function (Request $request, Response $response) {
        $historyController = new HistoryController();
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $alteredFoods = $historyController->getAlteredFood($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(Util::encodeData($alteredFoods, "altered_foods"));
        return $response->withHeader('Content-Type', 'application/json');
    });    

    /**
     * @api /histories/foods-sold
     * @method GET
     * @description Get history foods sold
     */
    $app->get('/histories/foods-sold', function (Request $request, Response $response) {
        $historyController = new HistoryController();
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $foodsSold = $historyController->getFoodsSold($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(Util::encodeData($foodsSold, "foods_sold"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/tickets
     * @method GET
     * @description Get history tickets
     */
    $app->get('/histories/tickets', function (Request $request, Response $response) {
        $historyController = new HistoryController();
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $tickets = $historyController->getTickets($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(Util::encodeData($tickets, "tickets"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
