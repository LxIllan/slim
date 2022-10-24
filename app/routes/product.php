<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Controllers\ProductController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/products', function (Group $group) {
		/**
		 * @api /products
		 * @method GET
		 */
		$group->get('', ProductController::class . ':getAll');		

		/**
		 * @api /products
		 * @method POST
		 */
		$group->post('', ProductController::class . ':create');

		/**
		 * @api /products/altered
		 * @method GET
		 */
		$group->get('/altered', ProductController::class . ':getAltered');

		/**
		 * @api /products/supplied
		 * @method GET
		 */
		$group->get('/supplied', ProductController::class . ':getSupplied');

		/**
		 * @api /products/used
		 * @method GET
		 */
		$group->get('/used', ProductController::class . ':getUsed');

		/**
		 * @api /products/{id}
		 * @method GET
		 */
		$group->get('/{id}', ProductController::class . ':getById');

		/**
		 * @api /products/{id}
		 * @method PUT
		 */
		$group->put('/{id}', ProductController::class . ':edit');

		/**
		 * @api /products/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', ProductController::class . ':delete');

		/**
		 * @api /products/{id}/alter
		 * @method PUT
		 */
		$group->put('/{id}/alter', ProductController::class . ':alter');

		/**
		 * @api /products/{id}/disuse
		 * @method POST
		 */
		$group->post('/{id}/disuse', ProductController::class . ':disuse');

		/**
		 * @api /products/{id}/supply
		 * @method PUT
		 */
		$group->put('/{id}/supply', ProductController::class . ':supply');

		/**
		 * @api /products/{id}/use
		 * @method POST
		 */
		$group->post('/{id}/use', ProductController::class . ':use');
	});
};
