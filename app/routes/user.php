<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Controllers\UserController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/users', function (Group $group) {
		/**
		 * @api /users
		 * @method POST
		 */
		$group->post('', UserController::class . ':create');

		/**
		 * @api /users
		 * @method GET
		 */
		$group->get('', UserController::class . ':getAll');

		/**
		 * @api /users/{id}
		 * @method GET
		 */
		$group->get('/{id}', UserController::class . ':getById');

		/**
		 * @api /users/{id}
		 * @method PUT
		 */
		$group->put('/{id}', UserController::class . ':edit');

		/**
		 * @api /users/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', UserController::class . ':delete');

		/**
		 * @api /users/{id}/reset-password
		 * @method PUT
		 */
		$group->put('/{id}/reset-password', UserController::class . ':resetPassword');
	});
};
