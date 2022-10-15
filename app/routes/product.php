<?php

declare(strict_types=1);

use App\Application\Controllers\ProductController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
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
	 * @api /products/altered
	 * @method GET
	 * @description Get history altered products
	 */
	$app->get('/products/altered', function (Request $request, Response $response) {
		$productController = new ProductController();
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$alteredProducts = $productController->getAltered($jwt['branch_id'], $params['from'], $params['to'], $getDeleted);
		$response->getBody()->write(Util::encodeData($alteredProducts, "altered_products"));
		return $response->withHeader('Content-Type', 'application/json');
	});  

	/**
	 * @api /products/supplied
	 * @method GET
	 * @description Get history supplied products
	 */
	$app->get('/products/supplied', function (Request $request, Response $response) {
		$productController = new ProductController();
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$suppliedProducts = $productController->getSupplied($jwt['branch_id'], $params['from'], $params['to'], $getDeleted);
		$response->getBody()->write(Util::encodeData($suppliedProducts, "supplied_products"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /products/used
	 * @method GET
	 * @description Get history used products
	 */
	$app->get('/products/used', function (Request $request, Response $response) {
		$productController = new ProductController();
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$usedProducts = $productController->getUsed($jwt['branch_id'], $params['from'], $params['to'], $getDeleted);
		$response->getBody()->write(Util::encodeData($usedProducts, "used_products"));
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
		if ($product) {
			$response->getBody()->write(Util::encodeData($product, "product"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
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
		if ($product) {
			$response->getBody()->write(Util::encodeData($product, "product"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	});

	/**
	 * @api /products/{id}
	 * @method DELETE
	 * @description Delete a dish
	 */
	$app->delete('/products/{id}', function (Request $request, Response $response, $args) {
		$productController = new ProductController();
		$wasDeleted = $productController->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	});    

	/**
	 * @api /products/{id}/alter
	 * @method PUT
	 * @description Alter product
	 */
	$app->put('/products/{id}/alter', function (Request $request, Response $response, $args) {
		$productController = new ProductController();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$product = $productController->alter(intval($args['id']), floatval($body['quantity']), $body['reason'], $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($product, "product"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /products/{id}/supply
	 * @method PUT
	 * @description Supply product
	 */
	$app->put('/products/{id}/supply', function (Request $request, Response $response, $args) {
		$productController = new ProductController();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$product = $productController->supply(intval($args['id']), floatval($body['quantity']), $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($product, "product"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /products/{id}/use
	 * @method POST
	 * @description Use a product
	 */
	$app->post('/products/{id}/use', function (Request $request, Response $response, $args) {
		$productController = new ProductController();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$product = $productController->use(intval($args['id']), intval($body['quantity']), $jwt['user_id']);
		$response->getBody()->write(Util::encodeData($product, "product"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /products/{id}/disuse
	 * @method POST
	 * @description Disuse a product
	 */
	$app->post('/products/{id}/disuse', function (Request $request, Response $response, $args) {
		$productController = new ProductController();
		$product = $productController->disuse(intval($args['id']));
		$response->getBody()->write(Util::encodeData($product, "product"));
		return $response->withHeader('Content-Type', 'application/json');
	});
};
