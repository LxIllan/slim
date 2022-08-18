<?php

declare(strict_types=1);

use App\Application\Controllers\ProductController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    /**
     * @api /products
     * @method POST
     * @description Create a new dish
     */
    $app->post('/products', function (Request $request, Response $response) {
        $productController = new ProductController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $body['branch_id'] = $jwt['branch_id'];
        $product = $productController->create($body);
        $response->getBody()->write(Util::encodeData($product, "product", 201));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /products
     * @method GET
     * @description Get products by branch
     */
    $app->get('/products', function (Request $request, Response $response) {        
        $productController = new ProductController();
        $jwt = $request->getAttribute("token");
        $products = $productController->getByBranch($jwt['branch_id']);
        $response->getBody()->write(Util::encodeData($products, "products"));
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
        $response->getBody()->write(Util::encodeData($product, "product"));
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
        $response->getBody()->write(Util::encodeData($product, "product"));
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
        $response->getBody()->write(Util::encodeData($wasDeleted, "response"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /products/{id}/use
     * @method POST
     * @description Create a new dish
     */
    $app->post('/products/{id}/use', function (Request $request, Response $response, $args) {
        $productController = new ProductController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $product = $productController->useProduct(intval($args['id']), intval($body['quantity']), $jwt['user_id']);
        $response->getBody()->write(Util::encodeData($product, "product"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
