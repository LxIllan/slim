<?php

declare(strict_types=1);

use App\Application\Controllers\HistoryController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
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
};
