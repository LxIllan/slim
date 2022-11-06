<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use Exception;
use App\Application\DAO\UserDAO;
use App\Application\Helpers\Util;
use App\Application\DAO\BranchDAO;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use App\Application\Helpers\EmailTemplate;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
	/**
	 * @var UserDAO $userDAO
	 */
	private UserDAO $userDAO;

	public function __construct()
	{
		$this->userDAO = new UserDAO();
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function create(Request $request, Response $response): Response
	{
		$body = $request->getParsedBody();
		if ($this->userDAO->existEmail($body['email']) != 0) {
			throw new Exception('Email already exists.');
		}
		$password = Util::generatePassword();
		$jwt = $request->getAttribute("token");
		$body["branch_id"] = $jwt["branch_id"];
		$body["hash"] = password_hash($password, PASSWORD_DEFAULT);

		$uploadedFiles = $request->getUploadedFiles();
		
		if (!empty($uploadedFiles)) {
			$uploadedFile = $uploadedFiles['image'];
			if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				$photoPath = Util::moveUploadedFile('user', $uploadedFile);
			}
		} else {
			$photoPath = 'default.png';
			$photoPath = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
			$photoPath .= "/public/images/user/default.jpg";
		}
		$body["photo_path"] = $photoPath;

		$user = $this->userDAO->create($body);
		if ($user) {
			unset($user->hash);
			$branchDAO = new BranchDAO();
			$branch = $branchDAO->getById(intval($body['branch_id']));
			$dataToSendEmail = [
				'subject' => "Bienvenido a $branch->name",
				'email' => $user->email,
				'branch_name' => $branch->name,
				'password' => $password,
				'username' => "$user->name $user->last_name"
			];
			if (!Util::sendMail($dataToSendEmail, EmailTemplate::PASSWORD_TO_NEW_USER)) {
				throw new Exception('Error to send password to new user.');
			}
			$response->getBody()->write(Util::encodeData($user, "user", 201));
			return $response->withHeader('Content-Type', 'application/json');
		} else {
			throw new Exception('Error to create user.');
		}
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getById(Request $request, Response $response, array $args): Response
	{
		$user = $this->userDAO->getById(intval($args['id']));        
		if ($user) {
			unset($user->hash);
			$response->getBody()->write(Util::encodeData($user, "user"));
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
	public function edit(Request $request, Response $response, array $args): Response
	{
		$jwt = $request->getAttribute("token");
		if (($jwt['user_id'] != intval($args['id'])) || !(Util::isAdmin($jwt))) {
			throw new HttpForbiddenException($request);
		}

		$body = $request->getParsedBody();
		if (isset($body['email']) && ($this->userDAO->existEmail($body['email']) != intval($args['id']))) {
			throw new Exception('Email already exists.');
		}

		if (isset($body['password'])) {
			$body['hash'] = password_hash($body['password'], PASSWORD_DEFAULT);
			unset($body['password']);
		}

		$uploadedFiles = $request->getUploadedFiles();
		if (!empty($uploadedFiles)) {
			$uploadedFile = $uploadedFiles['image'];
			if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				$body["photo_path"] = Util::moveUploadedFile('user', $uploadedFile);
			}
		}

		$user = $this->userDAO->edit(intval($args['id']), $body);
		
		if ($user) {
			unset($user->hash);
			$response->getBody()->write(Util::encodeData($user, "user"));
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
		$wasDeleted = $this->userDAO->delete(intval($args['id']));
		$response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
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
		$getDeleted = isset($params['deleted']) ? Util::strToBool($params['deleted']) : false;
		$token = $request->getAttribute("token");
		$users = $this->userDAO->getAll($token['branch_id'], $getDeleted);
		$response->getBody()->write(Util::encodeData($users, "users"));
		return $response->withHeader('Content-Type', 'application/json');
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function resetPassword(Request $request, Response $response, array $args): Response
	{
		$password = Util::generatePassword();
		$user = $this->userDAO->resetPassword(intval($args['id']), $password);
		if ($user) {
			$branchDAO = new BranchDAO();
			$branch = $branchDAO->getById(intval($user->branch_id));
			$dataToSendEmail = [
				'subject' => "Restablecer contraseña - $branch->name",
				'email' => $user->email,
				'branch_name' => $branch->name,
				'password' => $password,
				'username' => "$user->name"
			];
			if (!Util::sendMail($dataToSendEmail, EmailTemplate::RESET_PASSWORD)) {
				throw new Exception('Error to send password to new user.');
			}
			$wasUpdated = true;
		}
		$wasUpdated = false;
		$response->getBody()->write(Util::encodeData($wasUpdated, "response"));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
