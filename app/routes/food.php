<?php

declare(strict_types=1);

use App\Application\Controllers\FoodController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\App;

return function (App $app) {
	$app->group('/foods', function (Group $group) {
		/**
		 * @api /dishes
		 * @method POST
		 */
		$group->post('', FoodController::class . ':create');

		/**
		 * @api /foods
		 * @method GET
		 */
		$group->get('', FoodController::class . ':getAll');

		/**
		 * @api /foods/altered
		 * @method GET
		 */
		$group->get('/altered', FoodController::class . ':getAltered');

		/**
		 * @api /foods/supplied
		 * @method GET
		 */	
		$group->get('/supplied', FoodController::class . ':getSupplied');

		/**
		 * @api /foods/sold
		 * @method GET
		 */
		$group->get('/sold', FoodController::class . ':getSold');

		/**
		 * @api /foods/{id}
		 * @method GET
		 */
		$group->get('/{id}', FoodController::class . ':getById');

		/**
		 * @api /foods/{id}
		 * @method PUT
		 */
		$group->put('/{id}', FoodController::class . ':edit');

		/**
		 * @api /foods/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', FoodController::class . ':delete');

		/**
		* @api /foods/{id}/dishes
		* @method GET
		*/
		$group->get('/{id}/dishes', \App\Application\Controllers\DishController::class . ':getDishesByFood');

		/**
		 * @api /foods/{id}/supply
		 * @method PUT
		 */
		$group->put('/{id}/supply', FoodController::class . ':supply');

		/**
		 * @api /foods/{id}/alter
		 * @method PUT
		 */
		$group->put('/{id}/alter', FoodController::class . ':alter');
	});
};
