<?php

declare(strict_types=1);

use App\Application\Controllers\BranchController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\App;
use Slim\Exception\HttpException;

return function (App $app) {
	/**
	 * @api /branches
	 * @method POST
	 * @description Create a new branch
	 */
	$app->post('/branches', function (Request $request, Response $response) {
		$branchController = new BranchController();
		$body = $request->getParsedBody();
		$branch = $branchController->create($body);
		$response->getBody()->write(Util::encodeData($branch, "branch", 201));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /branches
	 * @method GET
	 * @description Get branches
	 */
	$app->get('/branches', function (Request $request, Response $response) {
		$jwt = $request->getAttribute("token");
		if (!Util::isAdmin($jwt)) {
			throw new HttpException($request, "You don't have permission to access this resource", 403);
		}
		$branchController = new BranchController();
		$branches = $branchController->getBranches();
		$response->getBody()->write(Util::encodeData($branches, "branches"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /branches/num-ticket
	 * @method GET
	 * @description Get num ticket by branch id
	 */
	$app->get('/branches/check-jwt', function (Request $request, Response $response) {
		$jwt = $request->getAttribute("token");
		$response->getBody()->write(Util::encodeData($jwt, "jwt"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /branches/num-ticket
	 * @method GET
	 * @description Get num ticket by branch id
	 */
	$app->get('/branches/connection', function (Request $request, Response $response) {
		// $connection = new Connection();
		$connectionId = $this->has('Logger');
		$response->getBody()->write(Util::encodeData($connectionId, "connection_id"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /branches/{id}
	 * @method GET
	 * @description Get branch by id
	 */
	$app->get('/branches/{id}', function (Request $request, Response $response, $args) {
		$branchController = new BranchController();
		$branch = $branchController->getById(intval($args['id']));
		if ($branch) {
			$response->getBody()->write(Util::encodeData($branch, "branch"));
		return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});

	/**
	 * @api /branches/{id}
	 * @method PUT
	 * @description Edit branch by id
	 */
	$app->put('/branches/{id}', function (Request $request, Response $response, $args) {
		$branchController = new BranchController();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$branch = $branchController->edit(intval($args['id']), $body);
		if ($branch) {
			$response->getBody()->write(Util::encodeData($branch, "branch"));
		return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});
};
