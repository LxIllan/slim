<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Helpers\Util;
use App\Application\Model\Food;

class FoodDAO
{
    private const TABLE_NAME = 'food';

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
     * @return Food|null
     */
    public function createFood(array $data): Food|null
    {
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->insert($query)) ? $this->getFoodById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Food
     */
    public function getFoodById(int $id): Food
    {
        return $this->connection
            ->select("SELECT * FROM food WHERE id = $id")
            ->fetch_object('App\Application\Model\Food');
    }

    /**
     * @param int $branchId
     * @return Food[]
     */
    public function getFoodByBranch(int $branchId): array
    {
        $food = [];
        $result = $this->connection
            ->select("SELECT id FROM food WHERE branch_id = $branchId ORDER BY name");
        while ($row = $result->fetch_array()) {
            $food[] = self::getFoodById(intval($row['id']));
        }
        return $food;
    }

    /**
     * @param int $branchId
     * @return Food[]
     */
    public function getFoodToDashboard(int $branchId): array
    {
        $food = [];
        $result = $this->connection
            ->select("SELECT id FROM food WHERE branch_id = $branchId AND is_showed_in_index = 1 ORDER BY name");
        while ($row = $result->fetch_array()) {
            $food[] = $this->getFoodById(intval($row['id']));
        }
        return $food;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Food|null
     */
    public function editFood(int $id, array $data): Food|null
    {
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getFoodById($id) : null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteFood(int $id): bool
    {
        $query = Util::prepareDeleteQuery($id, self::TABLE_NAME);
        return $this->connection->delete($query);
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
        $food = $this->getFoodById($foodId);
        $newQuantity = $food->quantity + $quantity;
        $cost = $food->cost * $quantity;

        $dataToInsert = [
            "food_id" => $foodId,
            "quantity" => $quantity,
            "new_quantity" => $newQuantity,
            "cost" => $cost,
            "user_id" => $userId,
            "branch_id" => $branchId
        ];

        $this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'supplied_food'));

        $dataToUpdate = [
            "quantity" => $newQuantity
        ];
        return $this->editFood($foodId, $dataToUpdate);
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
        $food = $this->getFoodById($foodId);
        $newQuantity = $food->quantity + $quantity;
        $cost = $food->cost * $quantity;

        $dataToInsert = [
            "food_id" => $foodId,
            "quantity" => $quantity,
            "reason" => $reason,
            "new_quantity" => $newQuantity,
            "cost" => $cost,
            "user_id" => $userId,
            "branch_id" => $branchId
        ];

        $this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'altered_food'));

        $dataToUpdate = [
            "quantity" => $newQuantity
        ];
        return $this->editFood($foodId, $dataToUpdate);
    }
}
