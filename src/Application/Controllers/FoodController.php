<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\DAO\FoodDAO;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Exception;

class FoodController
{
	/**
	 * @var FoodDAO $foodDAO
	 */
	private FoodDAO $foodDAO;

	public function __construct()
	{
		$this->foodDAO = new FoodDAO();
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

		$food = $this->foodDAO->create($body);
		if ($food == null) {
			throw new Exception("Error creating food.");
		}

		$dishDAO = new \App\Application\DAO\DishDAO();
		$dish = $dishDAO->create([
			"name" => $food->name,
			"price" => $food->cost,
			"food_id" => $food->id,
			"serving" => 1,
			"sell_individually" => 0,
			"category_id" => $food->category_id,
			"branch_id" => $food->branch_id
		]);
		
		$response->getBody()->write(Util::encodeData(["food" => $food, "dish" => $dish], "food", 201));
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
		$food = $this->foodDAO->getById(intval($args['id']));        
		if ($food) {
			$response->getBody()->write(Util::encodeData($food, "food"));
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
		$foods = $this->foodDAO->getAll($jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($foods, "foods"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response	 
	 * @return Response
	 */
	public function getAltered(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$from = $params['from'] ?? date('Y-m-d', strtotime("this week"));
		$to = $params['to'] ?? date('Y-m-d', strtotime($from . "next Sunday"));
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$alteredFoods = $this->foodDAO->getAltered($jwt['branch_id'], $from, $to, $getDeleted);
		$response->getBody()->write(Util::encodeData($alteredFoods, "altered_foods"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response	 
	 * @return Response
	 */
	public function getSupplied(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$from = $params['from'] ?? date('Y-m-d', strtotime("this week"));
		$to = $params['to'] ?? date('Y-m-d', strtotime($from . "next Sunday"));
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$suppliedFoods = $this->foodDAO->getAltered($jwt['branch_id'], $from, $to, $getDeleted);
		$response->getBody()->write(Util::encodeData($suppliedFoods, "supplied_foods"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response	 
	 * @return Response
	 */
	public function getSold(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$params = $request->getQueryParams();
		$from = $params['from'] ?? date("Y-m-d");
		$to = $params['to'] ?? date("Y-m-d");
		$foods = $this->foodDAO->getSold($jwt['branch_id'], $from, $to);
		$response->getBody()->write(Util::encodeData($foods, "foods"));
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
		$food = $this->foodDAO->edit(intval($args['id']), $body);
		if ($food) {
			$response->getBody()->write(Util::encodeData($food, "food"));
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
		$wasDeleted = $this->foodDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function supply(Request $request, Response $response, array $args): Response
	{
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$food = $this->foodDAO->supply(intval($args['id']), floatval($body['quantity']), $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($food, "food"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function alter(Request $request, Response $response, array $args): Response
	{
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$food = $this->foodDAO->alter(intval($args['id']), floatval($body['quantity']), $body['reason'], $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($food, "food"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
