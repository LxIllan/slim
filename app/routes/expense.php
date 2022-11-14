<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Controllers\ExpenseController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/expenses', function (Group $group) {
		/**
		 * @api /expenses
		 * @method POST
		 */
		$group->post('', ExpenseController::class . ':create');

		/**
		 * @api /expenses
		 * @method GET
		 */
		$group->get('', ExpenseController::class . ':getAll');

		/**
		 * @api /expenses/{id}
		 * @method GET
		 */
		$group->get('/{id}', ExpenseController::class . ':getById');

		/**
		 * @api /expenses/{id}
		 * @method PUT
		 */
		$group->put('/{id}', ExpenseController::class . ':edit');

		/**
		 * @api /expenses/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', ExpenseController::class . ':delete');
	});
};
