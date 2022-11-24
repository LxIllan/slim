<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Middleware\AdminMiddleware;
use App\Application\Controllers\CourtesyController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/courtesies', function (Group $group) {
		/**
		 * @api /courtesies
		 * @method GET
		 */
		$group->get('', CourtesyController::class . ':getAll');

		/**
		 * @api /courtesies/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', CourtesyController::class . ':cancel');
	})->add(new AdminMiddleware());
	/**
	 * @api /courtesies
	 * @method POST
	 */
	$app->post('/courtesies', CourtesyController::class . ':create');
};
