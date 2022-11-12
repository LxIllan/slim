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
		$group->get('/altered', ProductController::class . ':getSuppliedOrAlteredOrUsed');

		/**
		 * @api /products/supplied
		 * @method GET
		 */
		$group->get('/supplied', ProductController::class . ':getSuppliedOrAlteredOrUsed');

		/**
		 * @api /products/used
		 * @method GET
		 */
		$group->get('/used', ProductController::class . ':getSuppliedOrAlteredOrUsed');

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
		 * @api /products/{id}/supply
		 * @method PUT
		 */
		$group->put('/{id}/supply', ProductController::class . ':supply');

		/**
		 * @api /products/{id}/use
		 * @method PUT
		 */
		$group->put('/{id}/use', ProductController::class . ':use');

		/**
		 * @api /products/altered/{id}
		 * @method DELETE
		 */
		$group->delete('/altered/{id}', ProductController::class . ':cancelSuppliedOrAlteredOrUsed');

		/**
		 * @api /products/supplied/{id}
		 * @method DELETE
		 */
		$group->delete('/supplied/{id}', ProductController::class . ':cancelSuppliedOrAlteredOrUsed');

		/**
		 * @api /products/used/{id}
		 * @method DELETE
		 */
		$group->delete('/used/{id}', ProductController::class . ':cancelSuppliedOrAlteredOrUsed');
	});
};
