<?php

declare(strict_types=1);

use App\Application\Controller\HistoryController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/history/expenses', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        $historyController = new HistoryController();
        $expenses = [];

        if (isset($body['branchId'])) {
            $expenses = $historyController->getExpenses(null, null, null, intval($body['branchId']));
        }

        $response->getBody()->write(json_encode($expenses));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/history/sales', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        $historyController = new HistoryController();
        $sales = [];

        if (isset($body['branchId'])) {
            $sales = $historyController->getSales(null, null, intval($body['branchId']));
        }
        $response->withStatus(201);
        $response->getBody()->write(json_encode($sales));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/history/courtesies', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        $historyController = new HistoryController();
        $courtesies = [];

        if (isset($body['branchId'])) {
            // $courtesies = $historyController->getSales($body['startDate'], $body['endDate'], intval($body['branchId']));
            $courtesies = $historyController->getCourtesies(null, null, intval($body['branchId']));
        }

        $response->getBody()->write(json_encode($courtesies));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/history/supplied-food', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        $historyController = new HistoryController();

        $foods = [];
        if (isset($body['branchId'])) {
            $foods = $historyController->getSuppliedFood(intval($body['branchId']));
        }

        $response->getBody()->write(json_encode($foods));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/history/altered-food', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        $historyController = new HistoryController();

        $foods = [];
        if (isset($body['branchId'])) {
            $foods = $historyController->getAlteredFood(intval($body['branchId']));
        }

        $response->getBody()->write(json_encode($foods));

        return $response->withHeader('Content-Type', 'application/json');
    });
};
