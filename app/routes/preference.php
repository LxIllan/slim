<?php

declare(strict_types=1);

use App\Application\Controllers\PreferenceController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\App;

return function (App $app) {
    /**
     * @api /preferences
     * @method POST
     * @description Create a new branch
     */
    $app->post('/preferences', function (Request $request, Response $response) {
        $preferenceController = new PreferenceController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();        
        $body["branch_id"] = $jwt["branch_id"];
        $branch = $preferenceController->create($body);
        $response->getBody()->write(Util::encodeData($branch, "preference", 201));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /preferences
     * @method GET
     * @description Get branches
     */
    $app->get('/preferences', function (Request $request, Response $response) {
        $preferenceController = new PreferenceController();
        $jwt = $request->getAttribute("token");
        $branches = $preferenceController->getPreferences($jwt["branch_id"]);
        $response->getBody()->write(Util::encodeData($branches, "preferences"));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /preferences/num-ticket
     * @method GET
     * @description Get num ticket by branch id
     */
    $app->get('/preferences/{id}', function (Request $request, Response $response, $args) {
        $preferenceController = new PreferenceController();
        $jwt = $request->getAttribute("token");
        if (preg_match('(\d)', $args['id'])) {
            $preference = $preferenceController->getById(intval($args['id']));
        } else {
            $preference = $preferenceController->getByKey($args['id'], $jwt["branch_id"]);
        }
        if ($preference) {
            $response->getBody()->write(Util::encodeData($preference, "preference"));
        return $response->withHeader('Content-Type', 'application/json');
        } else {
            throw new HttpNotFoundException($request);
        }
    });

    /**
     * @api /preferences/{id}
     * @method PUT
     * @description Edit a combo
     */
    $app->put('/preferences/{id}', function (Request $request, Response $response, $args) {
        $preferenceController = new PreferenceController();
        $body = $request->getParsedBody();
        $preference = $preferenceController->edit(intval($args['id']), $body);
        $response->getBody()->write(Util::encodeData($preference, "preference"));
        if ($preference) {
            $response->getBody()->write(Util::encodeData($preference, "preference"));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            throw new HttpNotFoundException($request);
        }
    });

    /**
     * @api /preferences/{id}
     * @method DELETE
     * @description Delete preference by id
     */
    $app->delete('/preferences/{id}', function (Request $request, Response $response, $args) {
        $preferenceController = new PreferenceController();
        $wasDeleted = $preferenceController->delete(intval($args['id']));
        $response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
