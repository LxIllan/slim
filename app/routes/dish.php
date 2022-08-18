<?php

declare(strict_types=1);

use App\Application\Controllers\DishController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    /**
     * @api /dishes
     * @method POST
     * @description Create a new dish
     */
    $app->post('/dishes', function (Request $request, Response $response) {
        $dishController = new DishController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $body["branch_id"] = $jwt["branch_id"];
        $dish = $dishController->createDish($body);
        $response->getBody()->write(Util::encodeData($dish, "dish", 201));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /dishes/{id}
     * @method GET
     * @description Get dish by id
     */
    $app->get('/dishes/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $dish = $dishController->getDishById(intval($args['id']));
        $response->getBody()->write(Util::encodeData($dish, "dish"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /dishes/{id}
     * @method GET
     * @description Get dish by id
     */
    $app->get('/categories/{id}/dishes', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $jwt = $request->getAttribute("token");
        $dishes = $dishController->getDishesByCategory(intval($args['id']), $jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($dishes, "dishes"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
    * @api /foods/{id}/dishes
    * @method GET
    * @description Get dishes by food
    */
    $app->get('/foods/{id}/dishes', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $dishes = $dishController->getDishesByFood(intval($args['id']));
        $response->getBody()->write(Util::encodeData($dishes, "dishes"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /dishes/{id}
     * @method PUT
     * @description Edit a dish
     */
    $app->put('/dishes/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $body = $request->getParsedBody();
        $dish = $dishController->editDish(intval($args['id']), $body);
        $response->getBody()->write(Util::encodeData($dish, "dish"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /dishes/{id}
     * @method DELETE
     * @description Delete a dish
     */
    $app->delete('/dishes/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $wasDeleted = $dishController->deleteDish(intval($args['id']));
        $response->getBody()->write(Util::encodeData($wasDeleted, "response"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /sell
     * @method POST
     * @description Sell dishes
     */
    $app->post('/sell', function (Request $request, Response $response) {
        $dishController = new DishController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $result = $dishController->sell($body['items'], $jwt['user_id'], $jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($result, "response"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
