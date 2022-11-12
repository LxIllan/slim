<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Helpers\Util;
use App\Application\DAO\BranchDAO;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BranchController
{
	/**
	 * @var BranchDAO $branchDAO
	 */
	private BranchDAO $branchDAO;

	public function __construct()
	{
		$this->branchDAO = new BranchDAO();
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
		$branch = $this->branchDAO->create($body);
		$response->getBody()->write(Util::encodeData($branch, "branch", 201));
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
		$branch = $this->branchDAO->getById(intval($args['id']));
		if ($branch) {
			$response->getBody()->write(Util::encodeData($branch, "branch"));
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
		if (!Util::isAdmin($jwt)) {
			throw new HttpForbiddenException($request);
		}
		$branches = $this->branchDAO->getAll();
		$response->getBody()->write(Util::encodeData($branches, "branches"));
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

		$uploadedFiles = $request->getUploadedFiles();
		if (!empty($uploadedFiles)) {
			$uploadedFile = $uploadedFiles['image'];
			if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				$photoPath = Util::moveUploadedFile('branch', $uploadedFile);
			}
		}
		if (!isset($photoPath)) {
			$photoPath = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
			$photoPath .= "/public/images/branch/default.jpg";
		}
		unset($body['image']);
		$body["logo"] = $photoPath;

		$branch = $this->branchDAO->edit(intval($args['id']), $body);
		if ($branch) {
			$response->getBody()->write(Util::encodeData($branch, "branch"));
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
		$jwt = $request->getAttribute("token");
		if (!Util::isAdmin($jwt)) {
			throw new HttpForbiddenException($request);
		}
		$wasDeleted = $this->branchDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "response"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
