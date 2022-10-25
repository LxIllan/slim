<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Helpers\Util;
use App\Application\DAO\ExpenseDAO;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class ExpenseController
{
	/**
	 * @var ExpenseDAO $expenseDAO
	 */
	private ExpenseDAO $expenseDAO;

	public function __construct()
	{
		$this->expenseDAO = new ExpenseDAO();
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
		$body['user_id'] = $jwt['user_id'];
		$expense = $this->expenseDAO->create($body);
		$response->getBody()->write(Util::encodeData($expense, "expense", 201));
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
		$expense = $this->expenseDAO->getById(intval($args['id']));
		if ($expense) {
			$response->getBody()->write(Util::encodeData($expense, "expense"));
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
		$params = $request->getQueryParams();
		$from = $params['from'] ?? date("Y-m-d");
		$to = $params['to'] ?? date("Y-m-d");
		$reason = $params['reason'] ?? '';
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$expenses = $this->expenseDAO->getAll($jwt['branch_id'], $from, $to, $reason, $getDeleted);
		$response->getBody()->write(Util::encodeData($expenses, "expenses"));
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
		$expense = $this->expenseDAO->edit(intval($args['id']), $body);
		if ($expense) {
			$response->getBody()->write(Util::encodeData($expense, "expense"));
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
		$wasDeleted = $this->expenseDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
