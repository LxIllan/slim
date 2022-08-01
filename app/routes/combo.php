<?php

declare(strict_types=1);

use App\Application\Controller\DishController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    /**
     * @api /combos
     * @method GET
     * @description Get combos by branch
     */
    $app->get('/combos', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $dishController = new DishController();
        $dishes = $dishController->getCombosByBranch(intval($body['branch_id']));
        $response->getBody()->write(json_encode($dishes));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos
     * @method POST
     * @description Create a new combo
     */
    $app->post('/combos', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $dishController = new DishController();
        $dish = $dishController->createDish($body);
        $response->getBody()->write(json_encode($dish));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos/{id}
     * @method GET
     * @description Get combo by id
     */
    $app->get('/combos/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $dishes = $dishController->getDishById(intval($args['id']));
        $response->getBody()->write(json_encode($dishes));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos/{id}/dishes
     * @method GET
     * @description Get dishes by combo
     */
    $app->get('/combos/{id}/dishes', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $dishes = $dishController->getDishesByCombo(intval($args['id']));
        $response->getBody()->write(json_encode($dishes));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos/{id}/add-dish
     * @method POST
     * @description Add dish to combo
     */
    $app->post('/combos/{id}/add-dish', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $dishController = new DishController();
        $result = $dishController->addDishToCombo(intval($args['id']), intval($body['dish_id']));
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos/{id}/delete-dish
     * @method DELETE
     * @description Delete dish from combo
     */
    $app->delete('/combos/{id}/delete-dish', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $dishController = new DishController();
        $result = $dishController->deleteDishFromCombo(intval($args['id']), intval($body['dish_id']));
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
