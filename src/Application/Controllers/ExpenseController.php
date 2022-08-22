<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\DAO\ExpenseDAO;
use App\Application\Model\Expense;

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
     * @param array $data
     * @return Expense|null
     */
    public function create(array $data): Expense|null
    {
        return $this->expenseDAO->create($data);
    }

    /**
     * @param int $id
     * @return Expense|null
     */
    public function getById(int $id): Expense|null
    {
        return $this->expenseDAO->getById($id);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Expense|null
     */
    public function edit(int $id, array $data): Expense|null
    {
        return $this->expenseDAO->edit($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->expenseDAO->delete($id);
    }
}
