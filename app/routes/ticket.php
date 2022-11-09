<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Controllers\TicketController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
	$app->group('/tickets', function (Group $group) {
		/**
		 * @api /tickets
		 * @method POST
		 */
		$group->post('', TicketController::class . ':create');

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

		/**
		 * @api /tickets/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', TicketController::class . ':cancel');
	});
};
