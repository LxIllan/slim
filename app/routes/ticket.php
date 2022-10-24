<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Controllers\TicketController;
use App\Application\Controllers\CourtesyController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/tickets', function (Group $group) {
		/**
		 * @api /tickets
		 * @method GET
		 */
		$group->get('', TicketController::class . ':getAll');

		/**
		 * @api /tickets/{id}
		 * @method GET
		 */
		$group->get('/{id}', TicketController::class . ':getById');
	});

	$app->get('/courtesies', CourtesyController::class . ':getAll');
};
