<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Connection;
use App\Application\Model\Category;

class CategoryDAO
{
    private const TABLE_NAME = 'category';

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
     * @return Category
     */
    public function getById(int $id): Category
    {
        return $this->connection
            ->select("SELECT id, category FROM category WHERE id = $id")
            ->fetch_object('App\Application\Model\Category');
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        $categories = [];
        $result = $this->connection->select("SELECT id FROM category");
                
        while ($row = $result->fetch_assoc()) {
            $categories[] = $this->getById(intval($row['id']));
        }
        return $categories;
    }
}
