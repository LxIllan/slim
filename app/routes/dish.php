<?php

declare(strict_types=1);

use App\Application\Controllers\DishController;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {	
	$app->group('/dishes', function (Group $group) {
		/**
		 * @api /dishes
		 * @method POST
		 */
		$group->post('', DishController::class . ':create');

		/**
		 * @api /dishes/sold
		 * @method GET
		 */
		$group->get('/sold', DishController::class . ':getSold');

		/**
		 * @api /dishes/{id}
		 * @method GET
		 */
		$group->get('/{id}', DishController::class . ':getById');

		/**
		 * @api /dishes/{id}
		 * @method PUT
		 */
		$group->put('/{id}', DishController::class . ':edit');

		/**
		 * @api /dishes/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', DishController::class . ':delete');
	});
};
