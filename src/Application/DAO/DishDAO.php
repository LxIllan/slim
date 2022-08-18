<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Helpers\Util;
use App\Application\Model\Dish;
use App\Application\Controllers\FoodController;

class DishDAO
{
    private const TABLE_NAME = 'dish';

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
     * @return Dish|null
     */
    public function create(array $data): Dish|null
    {
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->insert($query)) ? $this->getById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Dish
     */
    public function getById(int $id): Dish
    {
        return $this->connection
            ->select("SELECT * FROM dish WHERE id = $id")
            ->fetch_object('App\Application\Model\Dish');
    }

    /**
     * @param int $foodId
     * @return Dish[]
     */
    public function getDishesByFood(int $foodId): array
    {
        $dishes = [];
        $result = $this->connection
            ->select("SELECT id FROM dish WHERE food_id = $foodId AND is_combo = 0 ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $categoryId
     * @param int $branchId
     * @return Dish[]
     */
    public function getDishesByCategory(int $categoryId, int $branchId): array
    {
        $dishes = [];
        $query = <<<EOF
            SELECT id 
            FROM dish 
            WHERE category_id = $categoryId 
                AND branch_id = $branchId 
                AND is_showed_in_sales = 1 
                ORDER BY name
        EOF;
        $result = $this->connection->select($query);
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Dish|null
     */
    public function editDish(int $id, array $data): Dish|null
    {
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getById($id) : null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteDish(int $id): bool
    {
        $query = Util::prepareDeleteQuery($id, self::TABLE_NAME);
        return $this->connection->delete($query);
    }

    /**
     * @param int $branchId
     * @return Dish[]
     */
    public function getCombosByBranch(int $branchId): array
    {
        $dishes = [];
        $result = $this->connection
            ->select("SELECT id FROM dish WHERE branch_id = $branchId AND is_combo = 1 ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $comboId
     * @return Dish[]
     */
    public function getDishesByCombo(int $comboId): array
    {
        $dishes = [];
        $result = $this->connection->select("SELECT dish_id FROM dishes_in_combo WHERE combo_id = $comboId");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['dish_id']));
        }
        return $dishes;
    }

    /**
     * @param int $comboId
     * @param int $dishId
     * @return Dish[]
     */
    public function addDishToCombo(int $comboId, int $dishId): array
    {
        $dish = $this->getById($comboId);
        if (!$dish->is_combo) {
            return false;
        }

        $dataToInsert = [
            "combo_id" => $comboId,
            "dish_id" => $dishId
        ];

        if ($this->connection->insert(Util::prepareInsertQuery($dataToInsert, "dishes_in_combo"))) {
            return $this->getDishesByCombo($comboId);
        }
        return [];
    }

    /**
     * @param int $comboId
     * @param int $dishId
     * @return Dish[]
     */
    public function deleteDishFromCombo(int $comboId, int $dishId): array
    {
        $query = <<<EOF
            DELETE FROM dishes_in_combo 
            WHERE combo_id = $comboId 
            AND dish_id = $dishId
            LIMIT 1
        EOF;

        if ($this->connection->delete($query)) {
            return $this->getDishesByCombo($comboId);
        }
        return [];
    }

    /**
     * @param array $items
     * @param int $userId
     * @param int $branchId
     * @return mixed
     */
    public function sell(array $items, int $userId, int $branchId): mixed
    {
        $result = false;
        foreach ($items as $item) {
            $dishToSell = $this->getById($item['dish_id']);
            $result = $this->registerSell(intval($dishToSell->id), intval($item['quantity']), floatval($dishToSell->price), $userId, $branchId);

            if ($dishToSell->is_combo) {
                $dishes = $this->getDishesByCombo(intval($dishToSell->id));
                foreach ($dishes as $dish) {
                    $portion = $dish->portion * $item['quantity'];
                    $this->subtractFood(intval($dish->food_id), $portion);
                }
            } else {
                $portion = $dishToSell->portion * $item['quantity'];
                $this->subtractFood(intval($dishToSell->food_id), $portion);
            }
        }
        return $result;
    }

    private function registerSell(int $dishId, int $quantity, float $price, int $userId, int $branchId): bool
    {
        $dataToInsert = [
            "dish_id" => $dishId,
            "quantity" => $quantity,
            "price" => $price,
            "user_id" => $userId,
            "branch_id" => $branchId
        ];
        $query = Util::prepareInsertQuery($dataToInsert, 'sale');
        return $this->connection->insert($query);
    }

    /**
     * @param int $foodId
     * @param float $quantity
     * @return bool
     */
    private function subtractFood(int $foodId, float $quantity): bool
    {
        $foodController = new FoodController();
        $food = $foodController->getFoodById($foodId);
        $dataToUpdate = [
            "quantity" => ($food->quantity - $quantity)
        ];
        return !is_null($this->connection->update(Util::prepareUpdateQuery($foodId, $dataToUpdate, 'food')));
    }
}