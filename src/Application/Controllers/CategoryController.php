<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Category;
use App\Application\DAO\CategoryDAO;

class CategoryController
{
    /**
     * @var CategoryDAO $categoryDAO
     */
    private CategoryDAO $categoryDAO;

    public function __construct()
    {
        $this->categoryDAO = new CategoryDAO();
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categoryDAO->getCategories();
    }

    /**
     * @param int $branchId
     * @return Category[]
     */
    public function getCategoriesWithDishes(int $branchId): array
    {
        return $this->categoryDAO->getCategoriesWithDishes($branchId);
    }
}
