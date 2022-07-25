<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Connection;
use App\Application\Model\Branch;
use App\Application\Helper\Util;

class BranchDAO
{
    private const TABLE_NAME = 'sucursal';

    /**
     * @var Connection $connection
     */
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection();
    }

    /**
     * @param int $id
     * @return Branch
     */
    public function getById(int $id): Branch
    {
        return $this->connection->select("SELECT * FROM sucursal WHERE id = $id")->fetch_object('App\Application\Model\Branch');
    }

    /**
     * @return Branch[]
     */
    public function getBranches(): array
    {
        $branches = [];
        $result = $this->connection->select("SELECT id FROM sucursal");
                
        while ($row = $result->fetch_assoc()) {
            $branches[] = $this->getById(intval($row['id']));
        }
        return $branches;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Branch|null
     */
    public function edit(int $id, array $data): Branch|null
    {
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getById($id) : null;
    }

    /**
     * @param int $branchId
     * @return int
     */
    public function getNumTicket(int $branchId): int
    {
        $num_ticket = intval($this->connection->select("SELECT num_ticket FROM sucursal WHERE id = $branchId")->fetch_array()[0]);
        $this->connection->update("UPDATE sucursal SET num_ticket = ($num_ticket + 1) WHERE id = $branchId");
        return $num_ticket;
    }
}
