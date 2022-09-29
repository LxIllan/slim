<?php

declare(strict_types=1);

use App\Application\Controllers\ExpenseController;
use App\Application\Helpers\Util;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\App;


return function (App $app) {
    /**
     * @api /expenses
     * @method POST
     * @description Create a new expense
     */
    $app->post('/expenses', function (Request $request, Response $response) {
        $expenseController = new ExpenseController();
        $jwt = $request->getAttribute("token");
        $body = $request->getParsedBody();
        $body['branch_id'] = $jwt['branch_id'];
        $body['user_id'] = $jwt['user_id'];
        $expense = $expenseController->create($body);
        $response->getBody()->write(Util::encodeData($expense, "expense", 201));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * @api /expenses/{id}
     * @method GET
     * @description Get expense by id
     */
    $app->get('/expenses/{id}', function (Request $request, Response $response, $args) {
        $expenseController = new ExpenseController();
        $expense = $expenseController->getById(intval($args['id']));
        if ($expense) {
            $response->getBody()->write(Util::encodeData($expense, "expense"));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            throw new HttpNotFoundException($request);
        }
    });

    /**
     * @api /expenses/{id}
     * @method PUT
     * @description Edit expense by id
     */
    $app->put('/expenses/{id}', function (Request $request, Response $response, $args) {
        $expenseController = new ExpenseController();
        $body = $request->getParsedBody();
        $expense = $expenseController->edit(intval($args['id']), $body);
        if ($expense) {
            $response->getBody()->write(Util::encodeData($expense, "expense"));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            throw new HttpNotFoundException($request);
        }
    });

    /**
     * @api /expenses/{id}
     * @method DELETE
     * @description Delete expense by id
     */
    $app->delete('/expenses/{id}', function (Request $request, Response $response, $args) {
        $expenseController = new ExpenseController();
        $wasDeleted = $expenseController->delete(intval($args['id']));
        $response->getBody()->write(Util::encodeData($wasDeleted, "deleted"));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
