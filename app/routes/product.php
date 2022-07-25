<?php

declare(strict_types=1);

use App\Application\Controller\ProductController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    /**
     * @api /products
     * @method POST
     * @description Create a new dish
     */
    $app->post('/products', function (Request $request, Response $response) {
        $body = $request->getParsedBody();
        $productController = new ProductController();
        $product = $productController->create($body);
        $response->getBody()->write(json_encode($product));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /products
     * @method GET
     * @description Get products by branch
     */
    $app->get('/products', function (Request $request, Response $response) {
        $productController = new ProductController();
        $body = $request->getParsedBody();
        $products = $productController->getByBranch(intval($body['branchId']));
        $response->getBody()->write(json_encode($products));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /products/{id}
     * @method GET
     * @description Get dish by id
     */
    $app->get('/products/{id}', function (Request $request, Response $response, $args) {
        $productController = new ProductController();
        $product = $productController->getById(intval($args['id']));
        $response->getBody()->write(json_encode($product));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /products/{id}
     * @method PUT
     * @description Edit a dish
     */
    $app->put('/products/{id}', function (Request $request, Response $response, $args) {
        $productController = new ProductController();
        $body = $request->getParsedBody();
        $product = $productController->edit(intval($args['id']), $body);
        $response->getBody()->write(json_encode($product));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /products/{id}
     * @method DELETE
     * @description Delete a dish
     */
    $app->delete('/products/{id}', function (Request $request, Response $response, $args) {
        $productController = new ProductController();
        $wasDeleted = $productController->delete(intval($args['id']));
        $response->getBody()->write(json_encode($wasDeleted));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /use-product/{id}
     * @method POST
     * @description Create a new dish
     */
    $app->post('/use-product/{id}', function (Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $productController = new ProductController();
        $product = $productController->useProduct(intval($args['id']), intval($body['quantity']), intval($body['userId']));
        $response->getBody()->write(json_encode($product));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
