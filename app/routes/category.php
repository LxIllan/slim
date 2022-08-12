<?php

declare(strict_types=1);

use App\Application\Controller\CategoryController;
use App\Application\Helper\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    /**
     * @api /categories
     * @method GET
     * @description Get all categories
     */
    $app->get('/categories', function (Request $request, Response $response) {
        $categoryController = new CategoryController();        
        $categories = $categoryController->getCategories();
        $response->getBody()->write(Util::orderReturnData($categories, "categories"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /categories
     * @method GET
     * @description Get all categories
     */
    $app->get('/categories/dishes', function (Request $request, Response $response) {
        $categoryController = new CategoryController();
        $jwt = $request->getAttribute("token");
        $dishes = $categoryController->getCategoriesWithDishes($jwt['branch_id']);
        $response->getBody()->write(Util::orderReturnData($dishes, "categories"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
