<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Dish;
use App\Application\Model\Food;
use App\Application\Model\Platillo;
use App\Application\Model\Alimento;
use App\Application\DAO\FoodDAO;
use App\Application\Helpers\Util;
use App\Application\Controllers\DishController;

class FoodController
{
    /**
     * @var FoodDAO $foodDAO
     */
    private FoodDAO $foodDAO;

    public function __construct()
    {
        $this->foodDAO = new FoodDAO();
    }

    /**
     * @param array $data
     * @return array
     */
    public function createFood(array $data): array
    {
        $food = $this->foodDAO->createFood($data);
        if ($food == null) {
            return ["message" => "Error creating food"];
        }

        $dishData = [
            "name" => $food->name,
            "price" => $food->cost,
            "food_id" => $food->id,
            "category_id" => $food->category_id,
            "branch_id" => $food->branch_id
        ];

        $dishController = new DishController();
        $dish = $dishController->createDish($dishData);
        if ($dish == null) {
            return [
                "food" => $food,
                "dish" => "Error creating dish"
            ];
        }

        return ["food" => $food, "dish" => $dish];
    }

    /**
     * @param int $id
     * @return Food|null
     */
    public function getFoodById(int $id): Food|null
    {
        return $this->foodDAO->getFoodById($id);
    }

    /**
     * @param int $branchId
     * @return Food[]
     */
    public function getFoodByBranch(int $branchId): array
    {
        return $this->foodDAO->getFoodByBranch($branchId);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Food|null
     */
    public function editFood(int $id, array $data): Food|null
    {
        return $this->foodDAO->editFood($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteFood(int $id): bool
    {
        return $this->foodDAO->deleteFood($id);
    }

    /**
     * @param int $foodId
     * @param float $quantity
     * @param int $userId
     * @param int $branchId
     * @return Food
     */
    public function supply(int $foodId, float $quantity, int $userId, int $branchId): Food
    {
        return $this->foodDAO->supply($foodId, $quantity, $userId, $branchId);
    }

    /**
     * @param int $foodId
     * @param float $quantity
     * @param string $reason
     * @param int $userId
     * @param int $branchId
     * @return Food
     */
    public function alter(int $foodId, float $quantity, string $reason, int $userId, int $branchId): Food
    {
        return $this->foodDAO->alter($foodId, $quantity, $reason, $userId, $branchId);
    }
}
