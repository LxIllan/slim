<?php

declare(strict_types=1);

use App\Application\Controller\HistoryController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    /**
     * @api /history/expenses
     * @method GET
     * @description Get history expenses
     */
    $app->get('/history/expenses', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $historyController = new HistoryController();
        $expenses = $historyController->getExpenses(intval($body['branch_id']), $body['start_date'], $body['end_date'], $body['reason']);
        $response->getBody()->write(json_encode($expenses));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /history/sales
     * @method GET
     * @description Get history sales
     */
    $app->get('/history/sales', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $historyController = new HistoryController();
        $sales = $historyController->getSales(intval($body['branch_id']), $body['start_date'], $body['end_date']);
        $response->getBody()->write(json_encode($sales));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /history/courtesies
     * @method GET
     * @description Get history courtesies
     */
    $app->get('/history/courtesies', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $historyController = new HistoryController();
        $courtesies = $historyController->getCourtesies(intval($body['branch_id']), $body['start_date'], $body['end_date']);
        $response->getBody()->write(json_encode($courtesies));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /history/supplied-food
     * @method GET
     * @description Get history supplied food
     */
    $app->get('/history/supplied-food', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $historyController = new HistoryController();
        $foods = $historyController->getSuppliedFood(intval($body['branch_id']), $body['start_date'], $body['end_date']);
        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /history/altered-food
     * @method GET
     * @description Get history altered food
     */
    $app->get('/history/altered-food', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $historyController = new HistoryController();
        $foods = $historyController->getAlteredFood(intval($body['branch_id']), $body['start_date'], $body['end_date']);
        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /history/used-products
     * @method GET
     * @description Get history used products
     */
    $app->get('/history/used-products', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $historyController = new HistoryController();
        $products = $historyController->getUsedProducts(intval($body['branch_id']), $body['start_date'], $body['end_date']);
        $response->getBody()->write(json_encode($products));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
