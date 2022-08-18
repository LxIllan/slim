<?php

declare(strict_types=1);

namespace App\Application\Controllers;

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
        return $this->dishDAO->create($data);
    }

    /**
     * @param int $id
     * @return Dish|null
     */
    public function getDishById(int $id): Dish|null
    {
        return $this->dishDAO->getById($id);
    }

    /**
     * @param int $foodId
     * @return Dish[]
     */
    public function getDishesByFood(int $foodId)
    {
        return $this->dishDAO->getDishesByFood($foodId);
    }

    /**
     * @param int $categoryId
     * @param int $branchId
     * @return Dish[]
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

    /**
     * @param int $branchId
     * @return Dish[]
     */
    public function getCombosByBranch(int $branchId): array
    {
        return $this->dishDAO->getCombosByBranch($branchId);
    }

    /**
     * @param int $comboId
     * @return Dish[]
     */
    public function getDishesByCombo(int $comboId): array
    {
        return $this->dishDAO->getDishesByCombo($comboId);
    }

    /**
     * @param int $comboId
     * @param int $dishId
     * @return Dish[]
     */
    public function addDishToCombo(int $comboId, int $dishId): array
    {
        return $this->dishDAO->addDishToCombo($comboId, $dishId);
    }

    /**
     * @param int $comboId
     * @param int $dishId
     * @return Dish[]
     */
    public function deleteDishFromCombo(int $comboId, int $dishId): array
    {
        return $this->dishDAO->deleteDishFromCombo($comboId, $dishId);
    }

    /**
     * @param array $items
     * @param int $userId
     * @param int $branchId
     * @return mixed
     */
    public function sell(array $items, int $userId, int $branchId): mixed
    {
        return $this->dishDAO->sell($items, $userId, $branchId);
    }
}