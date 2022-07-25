<?php

declare(strict_types=1);

use App\Application\Controller\BranchController;
use App\Application\Controller\SucursalController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
<<<<<<< HEAD
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
=======

    $app->post('/branches', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $branch = BranchController::create($body['name'], $body['location'], $body['phone_number']);
        $response->getBody()->write(json_encode($branch));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/branches', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $sucursalController = new SucursalController();
        if (isset($body['id'])) {
            $branches = $sucursalController->getBranch($body['id']);
        } else {
            $branches = $sucursalController->getBranches();
        }        
        
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
        $response->getBody()->write(json_encode($branches));
        return $response->withHeader('Content-Type', 'application/json');
    });

<<<<<<< HEAD
    /**
     * @api /get-ticket/{id}
     * @method GET
     * @description Get branch by id
     */
    $app->get('/branches/get-ticket/{id}', function (Request $request, Response $response, $args) {
        $branchController = new BranchController();
        $branches = $branchController->getNumTicket(intval($args['id']));
        $response->getBody()->write(json_encode($branches));
=======
    $app->put('/branches', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $sucursalController = new SucursalController();
        $category = $sucursalController->getNumTicket(1);        
        $response->getBody()->write(json_encode($category));
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
        return $response->withHeader('Content-Type', 'application/json');
    });
};
