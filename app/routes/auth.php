<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Helpers\Util;
use App\Application\Controllers\AuthController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
	$app->post('/login', AuthController::class . ':authenticate');

	$app->get('/logout', function (Request $request, Response $response) {
		$response->getBody()->write(json_encode('log out'));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /branches/num-ticket
	 * @method GET
	 * @description Get num ticket by branch id
	 */
	$app->get('/branches/check-jwt', function (Request $request, Response $response) {
		$jwt = $request->getAttribute("token");
		$response->getBody()->write(Util::encodeData($jwt, "jwt"));
		return $response->withHeader('Content-Type', 'application/json');
	});

	/**
	 * @api /branches/photo
	 * @method POST
	 */
	$app->post('/branches/photo', function (Request $request, Response $response) {
		$folder = 'user';

		$body = $request->getParsedBody();
		$id = 1;


		Util::log('body', $body);

		$uploadedFiles = $request->getUploadedFiles();
		Util::log('uploadedFiles', $body);
		$uploadedFile = $uploadedFiles['file'];
		$photoPath = 'Error';
		if ($uploadedFile->getSize() > 0) {
			if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				$photoPath = Util::moveUploadedFile($folder, $uploadedFile);
			}
		}
		Util::log('file', $photoPath);
		$response->getBody()->write(Util::encodeData($photoPath, "photo_path"));
		return $response->withHeader('Content-Type', 'application/json');
	});
};
