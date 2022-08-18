<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Model\Category;
use App\Application\Controllers\DishController;

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

    /**
     * @param int $branchId
     * @return Category[]
     */
    public function getCategoriesWithDishes(int $branchId): array
    {
        $dishesController = new DishController();
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $category->dishes = $dishesController->getDishesByCategory(intval($category->id), $branchId);
        }
        return $categories;
    }
}
