<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\Model\Dish;
use App\Application\DAO\DishDAO;

class DishController
{
    /**
     * @var DishDAO
     */
    private DishDAO $dishDAO;

    public function __construct()
    {
        $this->dishDAO = new DishDAO();
    }

    /**
     * @param array $data
     * @return Dish|null
     */
    public function createDish(array $data): Dish|null
    {
        return $this->dishDAO->createDish($data);
    }

    /**
     * @param int $id
     * @return Dish|null
     */
    public function getDishById(int $id): Dish|null
    {
        return $this->dishDAO->getDishById($id);
    }

    /**
     * @param int $foodId
     * @return array
     */
    public function getDishesByFood(int $foodId)
    {
        return $this->dishDAO->getDishesByFood($foodId);
    }

    /**
     * @param int $categoryId
     * @param int $branchId
     * @return array
     */
    public function getDishesByCategory(int $categoryId, int $branchId): array
    {
        return $this->dishDAO->getDishesByCategory($categoryId, $branchId);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Dish|null
     */
    public function editDish(int $id, array $data): Dish|null
    {
        return $this->dishDAO->editDish($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteDish(int $id): bool
    {
        return $this->dishDAO->deleteDish($id);
    }


}