<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Helpers\Util;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Exception\HttpForbiddenException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AdminMiddleware implements MiddlewareInterface
{
	/**
	 * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
	 * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
	 * @param  callable                                 $next     Next middleware
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function process(Request $request, RequestHandler $handler): Response
	{
		$jwt = $request->getAttribute("token");
		if (!Util::isAdmin($jwt)) {
			throw new HttpForbiddenException($request);
		}
		return $handler->handle($request);
	}
}
