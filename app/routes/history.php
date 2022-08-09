<?php

declare(strict_types=1);

use App\Application\Controller\HistoryController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    /**
     * @api /histories/expenses
     * @method GET
     * @description Get history expenses
     */
    $app->get('/histories/expenses', function (Request $request, Response $response) {
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $historyController = new HistoryController();
        $expenses = $historyController->getExpenses($jwt['branch_id'], $params['from'], $params['to'], $params['reason'] ?? null);
        $response->getBody()->write(json_encode(["statusCode" => 200, "data" => $expenses]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/sales
     * @method GET
     * @description Get history sales
     */
    $app->get('/histories/sales', function (Request $request, Response $response) {
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $historyController = new HistoryController();
        $sales = $historyController->getSales($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(json_encode(["statusCode" => 200, "data" => $sales]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/courtesies
     * @method GET
     * @description Get history courtesies
     */
    $app->get('/histories/courtesies', function (Request $request, Response $response) {
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $historyController = new HistoryController();
        $courtesies = $historyController->getCourtesies($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(json_encode(["statusCode" => 200, "data" => $courtesies]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/supplied-food
     * @method GET
     * @description Get history supplied food
     */
    $app->get('/histories/supplied-food', function (Request $request, Response $response) {
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $historyController = new HistoryController();
        $foods = $historyController->getSuppliedFood($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(json_encode(["statusCode" => 200, "data" => $foods]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/altered-food
     * @method GET
     * @description Get history altered food
     */
    $app->get('/histories/altered-food', function (Request $request, Response $response) {
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $historyController = new HistoryController();
        $foods = $historyController->getAlteredFood($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(json_encode(["statusCode" => 200, "data" => $foods]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /histories/used-products
     * @method GET
     * @description Get history used products
     */
    $app->get('/histories/used-products', function (Request $request, Response $response) {
        $jwt = $request->getAttribute("token");
        $params = $request->getQueryParams();
        $historyController = new HistoryController();
        $products = $historyController->getUsedProducts($jwt['branch_id'], $params['from'], $params['to']);
        $response->getBody()->write(json_encode(["statusCode" => 200, "data" => $products]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
