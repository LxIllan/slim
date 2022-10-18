<?php

declare(strict_types=1);

use App\Application\Controllers\DishController;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/combos', function (Group $group) {
		/**
		 * @api /combos
		 * @method POST
		 */
		$group->post('', DishController::class . ':create');

		/**
		 * @api /combos
		 * @method GET
		 */
		$group->get('', DishController::class . ':getCombos');

		/**
		 * @api /combos/{id}
		 * @method GET
		 */
		$group->get('/{id}', DishController::class . ':getById');

		/**
		 * @api /combos/{id}
		 * @method PUT
		 */
		$group->put('/{id}', DishController::class . ':edit');

		/**
		 * @api /combos/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', DishController::class . ':delete');

		/**
		 * @api /combos/{id}/dishes
		 * @method POST
		 */
		$group->post('/{id}/add-dish', DishController::class . ':addDishToCombo');
		$group->post('/{id}/dishes', DishController::class . ':addDishToCombo');

		/**
		 * @api /combos/{id}/dishes
		 * @method GET
		 */
		$group->get('/{id}/dishes', DishController::class . ':getDishesByCombo');

		/**
		 * @api /combos/{id}/dishes/{dish_id}
		 * @method DELETE
		 */
		$group->delete('/{id}/delete-dish/{dish_id}', DishController::class . ':deleteDishFromCombo');
		$group->delete('/{id}/dishes/{dish_id}', DishController::class . ':deleteDishFromCombo');
	});
};
