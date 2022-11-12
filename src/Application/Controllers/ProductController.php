<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Helpers\Util;
use App\Application\DAO\ProductDAO;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductController
{
	/**
	 * @var ProductDAO
	 */
	private ProductDAO $productDAO;

	public function __construct()
	{
		$this->productDAO = new ProductDAO();
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function create(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$body['branch_id'] = $jwt['branch_id'];
		$product = $this->productDAO->create($body);
		$response->getBody()->write(Util::encodeData($product, "product", 201));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getById(Request $request, Response $response, array $args): Response
	{
		$product = $this->productDAO->getById(intval($args['id']));
		if ($product) {
			$response->getBody()->write(Util::encodeData($product, "product"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function getAll(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$products = $this->productDAO->getAll($jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($products, "products"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function getSuppliedOrAlteredOrUsed(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		// $from = $params['from'] ?? date('Y-m-d', strtotime("this week"));
		// $to = $params['to'] ?? date('Y-m-d', strtotime($from . "next Sunday"));
		$from = $params['from'] ?? date("Y-m-d");
		$to = $params['to'] ?? date("Y-m-d");
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$uri = explode('/', $request->getUri()->getPath());
		$table = end($uri);
		$suppliedProducts = $this->productDAO->getSuppliedOrAlteredOrUsed($jwt['branch_id'], $from, $to, $getDeleted, $table);
		$response->getBody()->write(Util::encodeData($suppliedProducts, "${table}_products"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function edit(Request $request, Response $response, array $args): Response
	{
		$body = $request->getParsedBody();
		$product = $this->productDAO->edit(intval($args['id']), $body);
		if ($product) {
			$response->getBody()->write(Util::encodeData($product, "product"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function alter(Request $request, Response $response, array $args): Response
	{
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$uri = explode('/', $request->getUri()->getPath());
		$method = end($uri);
		Util::log('method', $method);
		$product = $this->productDAO->alter(intval($args['id']), floatval($body['qty']), $body['reason'], $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($product, "product"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function supply(Request $request, Response $response, array $args): Response
	{
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$product = $this->productDAO->supply(intval($args['id']), floatval($body['qty']), $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($product, "product"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function use(Request $request, Response $response, array $args): Response
	{
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$product = $this->productDAO->use(intval($args['id']), intval($body['qty']), $jwt['user_id']);
		$response->getBody()->write(Util::encodeData($product, "product"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function cancelSuppliedOrAlteredOrUsed(Request $request, Response $response, array $args): Response
	{
		$table = explode('/', $request->getUri()->getPath())[2];
		$product = $this->productDAO->cancelSuppliedOrAlteredOrUsed(intval($args['id']), $table);
		$response->getBody()->write(Util::encodeData($product, "product"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function delete(Request $request, Response $response, array $args): Response
	{
		$wasDeleted = $this->productDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
