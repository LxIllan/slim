<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\DAO\DishDAO;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
class DishController
{
	/**
	 * @var DishDAO
	 */
	private DishDAO $dishDAO;

	public function __construct()
	{
		$this->dishDAO = new DishDAO();
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
		$dish = $this->dishDAO->create($body);
		$response->getBody()->write(Util::encodeData($dish, "dish", 201));
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
		$dish = $this->dishDAO->getById(intval($args['id']));        
		if ($dish) {
			$response->getBody()->write(Util::encodeData($dish, "dish"));
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
	public function getDishesByFood(Request $request, Response $response, array $args): Response
	{
		$dishes = $this->dishDAO->getDishesByFood(intval($args['id']));
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getDishesByCategory(Request $request, Response $response, array $args): Response
	{
		$jwt = $request->getAttribute("token");
		$dishes = $this->dishDAO->getDishesByCategory(intval($args['id']), $jwt['branch_id'], false);
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
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
		$dishes = $this->dishDAO->getSold($jwt['branch_id'], $from, $to);
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
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
		$dish = $this->dishDAO->edit(intval($args['id']), $body);
		if ($dish) {
			$response->getBody()->write(Util::encodeData($dish, "dish"));
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
	public function delete(Request $request, Response $response, array $args): Response
	{
		$wasDeleted = $this->dishDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function getCombos(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$combos = $this->dishDAO->getCombos($jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($combos, "combos"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function getSpecialDishes(Request $request, Response $response): Response
	{
		$jwt = $request->getAttribute("token");
		$specialDishes = $this->dishDAO->getSpecialDishes($jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($specialDishes, "special_dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getDishesByCombo(Request $request, Response $response, array $args): Response
	{
		$dishes = $this->dishDAO->getDishesByCombo(intval($args['id']));
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function addDishToCombo(Request $request, Response $response, array $args): Response
	{
		$body = $request->getParsedBody();
		$dishes = $this->dishDAO->addDishToCombo(intval($args['id']), $body['dishes']);
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function deleteDishFromCombo(Request $request, Response $response, array $args): Response
	{
		$dishes = $this->dishDAO->deleteDishFromCombo(intval($args['id']), intval($args['dish_id']));
		$response->getBody()->write(Util::encodeData($dishes, "dishes"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function sell(Request $request, Response $response): Response
	{
		$sellDAO = new \App\Application\DAO\SellDAO();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$result = $sellDAO->sell($body['items'], $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($result, "response"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function courtesy(Request $request, Response $response): Response
	{
		$courtesyDAO = new \App\Application\DAO\courtesyDAO();
		$jwt = $request->getAttribute("token");
		$body = $request->getParsedBody();
		$result = $courtesyDAO->courtesy($body['items'], $body['reason'], $jwt['user_id'], $jwt['branch_id']);
		$response->getBody()->write(Util::encodeData($result, "response"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}