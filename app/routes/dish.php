<?php

declare(strict_types=1);

<<<<<<< HEAD
use App\Application\Controller\DishController;
=======
use App\Application\Controller\AlimentoController;
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/dishes', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        
<<<<<<< HEAD
        $dishController = new DishController();
        $dishes = [];

        if (isset($body['comboId'])) {
            $foods = $dishController->listarPlatillosPaquete(intval($body['comboId']));
        }

        $response->getBody()->write(json_encode($dishes));

        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /dishes
     * @method POST
     * @description Create a new dish
     */
    $app->post('/dishes', function (Request $request, Response $response) {
        $body = $request->getParsedBody();

        $dishController = new DishController();

        $dish = $dishController->createDish($body);

        $response->getBody()->write(json_encode($dish));

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

        $response->getBody()->write(json_encode($dish));

        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /dishes/{id}
     * @method GET
     * @description Get dish by id
     */
    $app->get('/category/{id}/dishes', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $dishController = new DishController();

        $dish = $dishController->getDishesByCategory(intval($args['id']), intval($body['branchId']));
        $response->getBody()->write(json_encode($dish));

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
=======
        $alimentoController = new AlimentoController();
        $dishes = [];
        
        if (isset($body['id'])) {
            $dishes = $alimentoController->consultarPlatillo(intval($body['id']));
        }

        if (isset($body['foodId'])) {
            $dishes = $alimentoController->listarPlatillos(intval($body['foodId']));
        }

        if ((isset($body['categoryId'])) && (isset($body['branchId']))) {
            $foods = $alimentoController->listarPlatillosCategoria(intval($body['categoryId']), intval($body['branchId']));
        }

        if (isset($body['comboId'])) {
            $foods = $alimentoController->listarPlatillosPaquete(intval($body['comboId']));
        }

>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
        $response->getBody()->write(json_encode($dishes));

        return $response->withHeader('Content-Type', 'application/json');
    });
<<<<<<< HEAD

    /**
     * @api /dishes/{id}
     * @method PUT
     * @description Edit a dish
     */
    $app->put('/dishes/{id}', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();

        $dishController = new DishController();
        $dish = $dishController->editDish(intval($args['id']), $body);
        $response->getBody()->write(json_encode($dish));

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

        $response->getBody()->write(json_encode($wasDeleted));

        return $response->withHeader('Content-Type', 'application/json');
    });
=======
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
};
