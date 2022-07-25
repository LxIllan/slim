<?php

declare(strict_types=1);

use App\Application\Controller\BranchController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    /**
     * @api /branches
     * @method GET
     * @description Get branches
     */
    $app->get('/branches', function (Request $request, Response $response) {
        $branchController = new BranchController();
        $branches = $branchController->getBranches();
        $response->getBody()->write(json_encode($branches));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /branches/{id}
     * @method GET
     * @description Get branch by id
     */
    $app->get('/branches/{id}', function (Request $request, Response $response, $args) {
        $branchController = new BranchController();
        $branches = $branchController->getById(intval($args['id']));
        $response->getBody()->write(json_encode($branches));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /get-ticket/{id}
     * @method GET
     * @description Get branch by id
     */
    $app->get('/branches/get-ticket/{id}', function (Request $request, Response $response, $args) {
        $branchController = new BranchController();
        $branches = $branchController->getNumTicket(intval($args['id']));
        $response->getBody()->write(json_encode($branches));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
