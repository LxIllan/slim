<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Controllers\PreferenceController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/preferences', function (Group $group) {
		/**
		 * @api /preferences
		 * @method POST
		 */
		$group->post('', PreferenceController::class . ':create');

		/**
		 * @api /preferences
		 * @method GET
		 */
		$group->get('', PreferenceController::class . ':getAll');

		/**
		 * @api /preferences/{id}
		 * @method GET
		 */
		$group->get('/{id}', PreferenceController::class . ':getById');

		/**
		 * @api /preferences/{id}
		 * @method PUT
		 */
		$group->put('/{id}', PreferenceController::class . ':edit');

		/**
		 * @api /preferences/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', PreferenceController::class . ':delete');
	});
};
