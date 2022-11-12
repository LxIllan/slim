<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Controllers\DishController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/special-dishes', function (Group $group) {
		/**
		 * @api /special-dishes
		 * @method POST
		 */
		$group->post('', DishController::class . ':create');

		/**
		 * @api /special-dishes
		 * @method GET
		 */
		$group->get('', DishController::class . ':getSpecialDishes');

		/**
		 * @api /special-dishes/{id}
		 * @method GET
		 */
		$group->get('/{id}', DishController::class . ':getById');

		/**
		 * @api /special-dishes/{id}
		 * @method PUT
		 */
		$group->put('/{id}', DishController::class . ':edit');

		/**
		 * @api /special-dishes/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', DishController::class . ':delete');

		/**
		 * @api /special-dishes/{id}/dishes
		 * @method POST
		 */
		$group->post('/{id}/dishes', DishController::class . ':addDishToCombo');

		/**
		 * @api /special-dishes/{id}/dishes
		 * @method GET
		 */
		$group->get('/{id}/dishes', DishController::class . ':getDishesByCombo');

		/**
		 * @api /special-dishes/{id}/dishes/{dish_id}
		 * @method DELETE
		 */
		$group->delete('/{id}/dishes/{dish_id}', DishController::class . ':deleteDishFromCombo');
	});
};
