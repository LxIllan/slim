<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use Exception;
use App\Application\Helpers\Util;
use App\Application\DAO\CategoryDAO;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryController
{
	/**
	 * @var CategoryDAO $categoryDAO
	 */
	private CategoryDAO $categoryDAO;

	public function __construct()
	{
		$this->categoryDAO = new CategoryDAO();
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function create(Request $request, Response $response): Response
	{
		$body = $request->getParsedBody();
		$category = $this->categoryDAO->create($body);
		$response->getBody()->write(Util::encodeData($category, "category", 201));
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
		$category = $this->categoryDAO->getById(intval($args['id']));
		if (is_null($category)) {
			throw new HttpNotFoundException($request);
		}
		$response->getBody()->write(Util::encodeData($category, "category"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function getAll(Request $request, Response $response): Response
	{
		$params = $request->getQueryParams();
		if (isset($params['dishes']) && Util::strToBool($params['dishes'])) {
			$jwt = $request->getAttribute("token");
			$getAll = isset($params['all']) ? Util::strToBool($params['all']) : false;
			$categories = $this->categoryDAO->getCategoriesWithDishes($jwt['branch_id'], $getAll);
		} else {
			$categories = $this->categoryDAO->getAll();
		}
		$response->getBody()->write(Util::encodeData($categories, "categories"));
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
		$category = $this->categoryDAO->edit(intval($args['id']), $body);
		if (is_null($category)) {
			throw new HttpNotFoundException($request);
		}
		$response->getBody()->write(Util::encodeData($category, "category"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function delete(Request $request, Response $response, array $args): Response
	{
		if ($args['id'] == Util::COMBOS_CATEGORY) {
			throw new Exception("You can't delete this category.");
		}
		$wasDeleted = $this->categoryDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
