<?php

declare(strict_types=1);

use App\Application\Controllers\BranchController;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\App;

return function (App $app) {
	$app->group('/branches', function (Group $group) {
		/**
		 * @api /branches
		 * @method POST
		 */
		$group->post('', BranchController::class . ':create');

		/**
		 * @api /branches
		 * @method GET
		 */
		$group->get('', BranchController::class . ':getAll');		

		/**
		 * @api /branches/{id}
		 * @method GET
		 */
		$group->get('/{id}', BranchController::class . ':getById');

		/**
		 * @api /branches/{id}
		 * @method PUT
		 */
		$group->put('/{id}', BranchController::class . ':edit');

		/**
		 * @api /branches/{id}
		 * @method DELETE
		 */
		$group->delete('/{id}', BranchController::class . ':delete');		
	});	
};
