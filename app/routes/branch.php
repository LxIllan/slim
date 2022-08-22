<?php

declare(strict_types=1);

use App\Application\Controllers\BranchController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

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
    $app->get('/branches/num-ticket', function (Request $request, Response $response) {
        $branchController = new BranchController();
        $jwt = $request->getAttribute("token");
        $numTicket = $branchController->getNumTicket($jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($numTicket, "num_ticket"));
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
        $response->getBody()->write(Util::encodeData($branch, "branch"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
