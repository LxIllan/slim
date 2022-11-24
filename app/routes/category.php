<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Middleware\AdminMiddleware;
use App\Application\Controllers\CategoryController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/categories', function (Group $group) {
		/**
		 * @api /categories
		 * @method POST
		 */
		$group->post('', CategoryController::class . ':create');

		/**
		 * @api /categories/{id}
		 * @method GET
		 */
		$group->get('/{id}', CategoryController::class . ':getById');

		/**
		 * @api /categories/{id}
		 * @method PUT
		 */
		$group->put('/{id}', CategoryController::class . ':edit');

		/**
		 * @api /categories/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', CategoryController::class . ':delete');
	})->add(new AdminMiddleware());

	/**
	 * @api /categories
	 * @method GET
	 */
	$app->get('/categories', CategoryController::class . ':getAll');
};
