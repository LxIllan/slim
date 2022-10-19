<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\DAO\CategoryDAO;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

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
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();        
		$body["branch_id"] = $jwt["branch_id"];
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
		if ($category) {
			$response->getBody()->write(Util::encodeData($category, "category"));
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
		$params = $request->getQueryParams();
		$jwt = $request->getAttribute("token");
		if (isset($params['dishes']) && Util::strToBool($params['dishes'])) {
			$getAll = isset($params['all']) ? Util::strToBool($params['all']) : false;
			$categories = $this->categoryDAO->getCategoriesWithDishes($jwt['branch_id'], $getAll);
		} else {
			$categories = $this->categoryDAO->getAll($jwt['branch_id']);
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
		if ($category) {
			$response->getBody()->write(Util::encodeData($category, "category"));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new HttpNotFoundException($request);
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function delete(Request $request, Response $response, array $args): Response
	{
		$wasDeleted = $this->categoryDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
