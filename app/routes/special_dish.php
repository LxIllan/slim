<?php

declare(strict_types=1);

use App\Application\Controllers\DishController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\App;

return function (App $app) {
	/**
	 * @api /special-dishes
	 * @method POST
	 * @description Create a new special-dish
	 */
	$app->post('/special-dishes', function (Request $request, Response $response) {
		$dishController = new DishController();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$body["branch_id"] = $jwt["branch_id"];
		$dish = $dishController->createDish($body);
		$response->getBody()->write(Util::encodeData($dish, "special_dish", 201));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /special-dishes
	 * @method GET
	 * @description Get special-dishes by branch
	 */
	$app->get('/special-dishes', function (Request $request, Response $response) {
		$dishController = new DishController();
		$jwt = $request->getAttribute("token");
		$specialDishes = $dishController->getSpecialDishesByBranch($jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($specialDishes, "special_dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /special-dishes/{id}
	 * @method GET
	 * @description Get special-dish by id
	 */
	$app->get('/special-dishes/{id}', function (Request $request, Response $response, $args) {
		$dishController = new DishController();
		$specialDish = $dishController->getDishById(intval($args['id']));
		if ($specialDish) {
			$response->getBody()->write(Util::encodeData($specialDish, "special_dish"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});

	/**
	 * @api /special-dishes/{id}
	 * @method PUT
	 * @description Edit a special-dish
	 */
	$app->put('/special-dishes/{id}', function (Request $request, Response $response, $args) {
		$dishController = new DishController();
		$body = $request->getParsedBody();
		$specialDish = $dishController->editDish(intval($args['id']), $body);
		if ($specialDish) {
			$response->getBody()->write(Util::encodeData($specialDish, "special_dish"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});

	/**
	 * @api /special-dishes/{id}
	 * @method DELETE
	 * @description Delete a special-dish
	 */
	$app->delete('/special-dishes/{id}', function (Request $request, Response $response, $args) {
		$dishController = new DishController();
		$wasDeleted = $dishController->deleteDish(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /special-dishes/{id}/dishes
	 * @method GET
	 * @description Get dishes by special-dish
	 */
	$app->get('/special-dishes/{id}/dishes', function (Request $request, Response $response, $args) {
		$dishController = new DishController();
		$dishes = $dishController->getDishesByCombo(intval($args['id']));
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /special-dishes/{id}/add-dish
	 * @method POST
	 * @description Add dish to special dish
	 */
	$app->post('/special-dishes/{id}/add-dish', function (Request $request, Response $response, $args) {
		$body = $request->getParsedBody();
		$dishController = new DishController();
		$dishes = $dishController->addDishToCombo(intval($args['id']), $body['dishes']);
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /special-dishes/{id}/delete-dish
	 * @method DELETE
	 * @description Delete dish from special dish
	 */
	$app->delete('/special-dishes/{id}/delete-dish/{dish_id}', function (Request $request, Response $response, $args) {
		$dishController = new DishController();
		$dishes = $dishController->deleteDishFromCombo(intval($args['id']), intval($args['dish_id']));
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	});
};
