<?php

declare(strict_types=1);

use App\Application\Controllers\FoodController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    /**
     * @api /dishes
     * @method POST
     * @description Create a new dish
     */
    $app->post('/foods', function (Request $request, Response $response) {
        $foodController = new FoodController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $body['branch_id'] = $jwt['branch_id'];
        $food = $foodController->createFood($body);
        $response->getBody()->write(Util::encodeData($food, "food"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods
     * @method GET
     * @description Get all foods from a branch
     */
    $app->get('/foods', function (Request $request, Response $response) {
        $foodController = new FoodController();
        $jwt = $request->getAttribute("token");
        $foods = $foodController->getFoodByBranch($jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($foods, "foods"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}
     * @method GET
     * @description Get food by id
     */
    $app->get('/foods/{id}', function (Request $request, Response $response, $args) {
        $foodController = new FoodController();
        $food = $foodController->getFoodById(intval($args['id']));
        $response->getBody()->write(Util::encodeData($food, "food"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}
     * @method PUT
     * @description Update food
     */
    $app->put('/foods/{id}', function (Request $request, Response $response, $args) {
        $foodController = new FoodController();
        $body = $request->getParsedBody();
        $food = $foodController->editFood(intval($args['id']), $body);
        $response->getBody()->write(Util::encodeData($food, "food"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}
     * @method DELETE
     * @description Delete food
     */
    $app->delete('/foods/{id}', function (Request $request, Response $response, $args) {
        $foodController = new FoodController();
        $wasDeleted = $foodController->deleteFood(intval($args['id']));
        $response->getBody()->write(Util::encodeData($wasDeleted, "response"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}/supply
     * @method PUT
     * @description Delete food
     */
    $app->put('/foods/{id}/supply', function (Request $request, Response $response, $args) {
        $foodController = new FoodController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $food = $foodController->supply(intval($args['id']), floatval($body['quantity']), $jwt['user_id'], $jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($food, "food"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}/alter
     * @method PUT
     * @description Delete food
     */
    $app->put('/foods/{id}/alter', function (Request $request, Response $response, $args) {
        $foodController = new FoodController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $food = $foodController->alter(intval($args['id']), floatval($body['quantity']), $body['reason'], $jwt['user_id'], $jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($food, "food"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
