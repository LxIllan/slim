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
};
