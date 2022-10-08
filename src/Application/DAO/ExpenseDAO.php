<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Model\Expense;
use App\Application\Helpers\Util;
use StdClass;

class ExpenseDAO
{
    private const TABLE_NAME = 'expense';

    /**
     * @var Connection $connection
     */
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection();
    }

    /**
     * @param array $data
     * @return Expense|null
     */
    public function create(array $data): Expense|null
    {
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->insert($query)) ? $this->getById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Expense|null
     */
    public function getById(int $id): Expense|null
    {
        return $this->connection
            ->select("SELECT * FROM expense WHERE id = $id")
            ->fetch_object('App\Application\Model\Expense');
    }

    /**
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @param string $reason
     * @param bool $isDeleted
     * @return StdClass
     */
    public function getHistory(int $branchId, string $from, string $to, string $reason, bool $isDeleted): StdClass
    {
        $expenses = new StdClass();
        $expenses->amount = Util::getSumFromTable('amount', 'expense', $branchId, $from, $to);

        if ($expenses->amount == 0) {
            $expenses->length = 0;
            $expenses->expenses = [];
            return $expenses;
        }

        $query = <<<EOF
            SELECT expense.id, expense.date, expense.amount, expense.reason, 
                   CONCAT(user.name, ' ' ,user.last_name) AS cashier
            FROM expense
            JOIN user ON expense.user_id = user.id
            WHERE expense.branch_id = $branchId
                AND DATE(expense.date) >= '$from'
                AND DATE(expense.date) <= '$to'
                AND expense.reason LIKE '%$reason%'
                AND expense.is_deleted = false
            ORDER BY date DESC
        EOF;

        if ($isDeleted) {
            $query = str_replace('expense.is_deleted = false', 'expense.is_deleted = true', $query);
        }

        $result = $this->connection->select($query);
        $expenses->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $expenses->items[] = $row;
        }
        return $expenses;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Expense|null
     */
    public function edit(int $id, array $data): Expense|null
    {
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getById($id) : null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $data = [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')        
        ];
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);        
        return $this->connection->update($query);
    }
}
