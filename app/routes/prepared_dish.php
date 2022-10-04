<?php

declare(strict_types=1);

use App\Application\Controllers\DishController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\App;

return function (App $app) {
    /**
     * @api /prepared-dishes
     * @method POST
     * @description Create a new prepared-dish
     */
    $app->post('/prepared-dishes', function (Request $request, Response $response) {
        $dishController = new DishController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $body["branch_id"] = $jwt["branch_id"];
        $dish = $dishController->createDish($body);
        $response->getBody()->write(Util::encodeData($dish, "prepared_dish", 201));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /prepared-dishes
     * @method GET
     * @description Get prepared-dishes by branch
     */
    $app->get('/prepared-dishes', function (Request $request, Response $response) {
        $dishController = new DishController();
        $jwt = $request->getAttribute("token");
        $preparedDishes = $dishController->getPreparedDishesByBranch($jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($preparedDishes, "prepared_dishes"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /prepared-dishes/{id}
     * @method GET
     * @description Get prepared-dish by id
     */
    $app->get('/prepared-dishes/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $preparedDish = $dishController->getDishById(intval($args['id']));
        if ($preparedDish) {
            $response->getBody()->write(Util::encodeData($preparedDish, "prepared_dish"));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            throw new HttpNotFoundException($request);
        }
    });

    /**
     * @api /prepared-dishes/{id}
     * @method PUT
     * @description Edit a prepared-dish
     */
    $app->put('/prepared-dishes/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $body = $request->getParsedBody();
        $preparedDish = $dishController->editDish(intval($args['id']), $body);
        if ($preparedDish) {
            $response->getBody()->write(Util::encodeData($preparedDish, "prepared_dish"));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            throw new HttpNotFoundException($request);
        }
    });

    /**
     * @api /prepared-dishes/{id}
     * @method DELETE
     * @description Delete a prepared-dish
     */
    $app->delete('/prepared-dishes/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $wasDeleted = $dishController->deleteDish(intval($args['id']));
        $response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /prepared-dishes/{id}/dishes
     * @method GET
     * @description Get dishes by prepared-dish
     */
    $app->get('/prepared-dishes/{id}/dishes', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $dishes = $dishController->getDishesByCombo(intval($args['id']));
        $response->getBody()->write(Util::encodeData($dishes, "dishes"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /prepared-dishes/{id}/add-dish
     * @method POST
     * @description Add dish to prepared dish
     */
    $app->post('/prepared-dishes/{id}/add-dish', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $dishController = new DishController();
        $dishes = $dishController->addDishToCombo(intval($args['id']), $body['dishes']);
        $response->getBody()->write(Util::encodeData($dishes, "dishes"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /prepared-dishes/{id}/delete-dish
     * @method DELETE
     * @description Delete dish from prepared dish
     */
    $app->delete('/prepared-dishes/{id}/delete-dish', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $dishController = new DishController();
        $dishes = $dishController->deleteDishFromCombo(intval($args['id']), intval($body['dish_id']));
        $response->getBody()->write(Util::encodeData($dishes, "dishes"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
