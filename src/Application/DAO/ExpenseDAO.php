<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Model\Expense;
use App\Application\Helpers\Util;

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
        $query = Util::prepareDeleteQuery($id, self::TABLE_NAME);
        return $this->connection->delete($query);
    }
}
