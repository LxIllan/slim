<?php

declare(strict_types=1);

use App\Application\Controllers\DishController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    /**
     * @api /combos
     * @method POST
     * @description Create a new combo
     */
    $app->post('/combos', function (Request $request, Response $response) {
        $dishController = new DishController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $body["branch_id"] = $jwt["branch_id"];
        $dish = $dishController->createDish($body);
        $response->getBody()->write(Util::encodeData($dish, "dish", 201));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos
     * @method GET
     * @description Get combos by branch
     */
    $app->get('/combos', function (Request $request, Response $response) {
        $dishController = new DishController();
        $jwt = $request->getAttribute("token");
        $combos = $dishController->getCombosByBranch($jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($combos, "combos"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos/{id}
     * @method GET
     * @description Get combo by id
     */
    $app->get('/combos/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $combo = $dishController->getDishById(intval($args['id']));
        $response->getBody()->write(Util::encodeData($combo, "combo"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos/{id}
     * @method PUT
     * @description Edit a combo
     */
    $app->put('/combos/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $body = $request->getParsedBody();
        $dish = $dishController->editDish(intval($args['id']), $body);
        $response->getBody()->write(Util::encodeData($dish, "dish"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /combos/{id}
     * @method DELETE
     * @description Delete a combo
     */
    $app->delete('/combos/{id}', function (Request $request, Response $response, $args) {
        $dishController = new DishController();
        $wasDeleted = $dishController->deleteDish(intval($args['id']));
        $response->getBody()->write(Util::encodeData($wasDeleted, "response"));
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
        $response->getBody()->write(Util::encodeData($dishes, "dishes"));
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
        $dishes = $dishController->addDishToCombo(intval($args['id']), intval($body['dish_id']));
        $response->getBody()->write(Util::encodeData($dishes, "dishes"));
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
        $dishes = $dishController->deleteDishFromCombo(intval($args['id']), intval($body['dish_id']));
        $response->getBody()->write(Util::encodeData($dishes, "dishes"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
