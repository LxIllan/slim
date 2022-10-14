<?php

declare(strict_types=1);

use App\Application\Controllers\FoodController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpNotFoundException;

return function (App $app) {
	/**
	 * @api /dishes
	 * @method POST
	 * @description Create a new dish
	 */
	$app->post('/foods', function (Request $request, Response $response) {
		$foodController = new FoodController();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$body['branch_id'] = $jwt['branch_id'];
		$food = $foodController->create($body);
		$response->getBody()->write(Util::encodeData($food, "food", 201));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /foods
	 * @method GET
	 * @description Get all foods from a branch
	 */
	$app->get('/foods', function (Request $request, Response $response) {
		$foodController = new FoodController();
		$jwt = $request->getAttribute("token");
		$foods = $foodController->getByBranch($jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($foods, "foods"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /foods/altered
	 * @method GET
	 * @description Get history altered foods
	 */
	$app->get('/foods/altered', function (Request $request, Response $response) {
		$foodController = new FoodController();
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$alteredFoods = $foodController->getAltered($jwt['branch_id'], $params['from'], $params['to'], $getDeleted);
		$response->getBody()->write(Util::encodeData($alteredFoods, "altered_foods"));
		return $response->withHeader('Content-Type', 'application/json');
	});  

	/**
	 * @api /foods/supplied
	 * @method GET
	 * @description Get history supplied foods
	 */
	$app->get('/foods/supplied', function (Request $request, Response $response) {
		$foodController = new FoodController();
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$suppliedFoods = $foodController->getSupplied($jwt['branch_id'], $params['from'], $params['to'], $getDeleted);
		$response->getBody()->write(Util::encodeData($suppliedFoods, "supplied_foods"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /foods/{id}
	 * @method GET
	 * @description Get food by id
	 */
	$app->get('/foods/{id}', function (Request $request, Response $response, $args) {
		$foodController = new FoodController();
		$food = $foodController->getById(intval($args['id']));        
		if ($food) {
			$response->getBody()->write(Util::encodeData($food, "food"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});

	/**
	 * @api /foods/{id}
	 * @method PUT
	 * @description Update food
	 */
	$app->put('/foods/{id}', function (Request $request, Response $response, $args) {
		$foodController = new FoodController();
		$body = $request->getParsedBody();
		$food = $foodController->edit(intval($args['id']), $body);
		if ($food) {
			$response->getBody()->write(Util::encodeData($food, "food"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});

	/**
	 * @api /foods/{id}
	 * @method DELETE
	 * @description Delete food
	 */
	$app->delete('/foods/{id}', function (Request $request, Response $response, $args) {
		$foodController = new FoodController();
		$wasDeleted = $foodController->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /foods/{id}/supply
	 * @method PUT
	 * @description Delete food
	 */
	$app->put('/foods/{id}/supply', function (Request $request, Response $response, $args) {
		$foodController = new FoodController();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$food = $foodController->supply(intval($args['id']), floatval($body['quantity']), $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($food, "food"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /foods/{id}/alter
	 * @method PUT
	 * @description Delete food
	 */
	$app->put('/foods/{id}/alter', function (Request $request, Response $response, $args) {
		$foodController = new FoodController();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$food = $foodController->alter(intval($args['id']), floatval($body['quantity']), $body['reason'], $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($food, "food"));
		return $response->withHeader('Content-Type', 'application/json');
	});
};
