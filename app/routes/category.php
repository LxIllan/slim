<?php

declare(strict_types=1);

use App\Application\Controllers\CategoryController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
	/**
	 * @api /categories
	 * @method GET
	 * @description Get all categories
	 */
	$app->get('/categories', function (Request $request, Response $response) {
		$categoryController = new CategoryController();        
		$categories = $categoryController->getCategories();
		$params = $request->getQueryParams();
		if (isset($params['dishes']) && Util::strToBool($params['dishes'])) {
			$jwt = $request->getAttribute("token");
			$getAll = isset($params['all']) ? Util::strToBool($params['all']) : false;
			$categories = $categoryController->getCategoriesWithDishes($jwt['branch_id'], $getAll);
		} else {
			$categories = $categoryController->getCategories();
		}
		$response->getBody()->write(Util::encodeData($categories, "categories"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /categories
	 * @method GET
	 * @description Get all categories
	 */
	$app->get('/categories/dishes', function (Request $request, Response $response) {
		$categoryController = new CategoryController();
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$dishes = $categoryController->getCategoriesWithDishes($jwt['branch_id'], boolval($params['all']) ?? false);
		$response->getBody()->write(Util::encodeData($dishes, "categories"));
		return $response->withHeader('Content-Type', 'application/json');
	});
};
