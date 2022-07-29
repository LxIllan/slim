<?php

declare(strict_types=1);

use App\Application\Controller\FoodController;
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
        $body = $request->getParsedBody();
        $foodController = new FoodController();
        $result = $foodController->createFood($body);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods
     * @method GET
     * @description Create all foods from a branch
     */
    $app->get('/foods', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $foodController = new FoodController();
        $foods = $foodController->getFoodByBranch(intval($body['branch_id']));
        $response->getBody()->write(json_encode($foods));
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
        $response->getBody()->write(json_encode($food));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}
     * @method PUT
     * @description Update food
     */
    $app->put('/foods/{id}', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $foodController = new FoodController();
        $food = $foodController->editFood(intval($args['id']), $body);
        $response->getBody()->write(json_encode($food));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}
     * @method DELETE
     * @description Delete food
     */
    $app->delete('/foods/{id}', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $foodController = new FoodController();
        $food = $foodController->deleteFood(intval($args['id']));
        $response->getBody()->write(json_encode($food));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}/supply
     * @method PUT
     * @description Delete food
     */
    $app->put('/foods/{id}/supply', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $foodController = new FoodController();
        $food = $foodController->supply(intval($args['id']), floatval($body['quantity']), intval($body['user_id']), intval($body['branch_id']));
        $response->getBody()->write(json_encode($food));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /foods/{id}/alter
     * @method PUT
     * @description Delete food
     */
    $app->put('/foods/{id}/alter', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $foodController = new FoodController();
        $food = $foodController->alter(intval($args['id']), floatval($body['quantity']), $body['reason'], intval($body['user_id']), intval($body['branch_id']));
        $response->getBody()->write(json_encode($food));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
