<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Controllers\CourtesyController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/courtesies', function (Group $group) {
		/**
		 * @api /courtesy
		 * @method POST
		 */
		$group->post('', CourtesyController::class . ':create');

		/**
		 * @api /tickets
		 * @method GET
		 */
		$group->get('', CourtesyController::class . ':getAll');

		/**
		 * @api /courtesies/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', CourtesyController::class . ':cancel');
	});
};
