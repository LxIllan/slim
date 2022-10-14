<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Helpers\Util;
use App\Application\Model\Food;
use StdClass;

class FoodDAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'food';

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
	public function create(array $data): Food|null
	{
		$query = Util::prepareInsertQuery($data, $this->table);
		return ($this->connection->insert($query)) ? $this->getById($this->connection->getLastId()) : null;
	}

	/**
	 * @param int $id
	 * @return Food|null
	 */
	public function getById(int $id): Food|null
	{
		return $this->connection
			->select("SELECT * FROM $this->food WHERE id = $id")
			->fetch_object('App\Application\Model\Food');
	}

	/**
	 * @param int $branchId
	 * @return Food[]
	 */
	public function getByBranch(int $branchId): array
	{
		$food = [];
		$result = $this->connection
			->select("SELECT id FROM $this->table WHERE branch_id = $branchId ORDER BY name");
		while ($row = $result->fetch_array()) {
			$food[] = $this->getById(intval($row['id']));
		}
		return $food;
	}

	/**
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @param bool $isDeleted
	 * @return StdClass
	 */
	public function getAltered(int $branchId, string $from, string $to, bool $isDeleted): StdClass
	{
		$alteredFood = new StdClass();;

		$query = <<<SQL
			SELECT altered_food.id, altered_food.date, food.name, altered_food.quantity, altered_food.reason,
				altered_food.new_quantity, altered_food.cost, CONCAT(user.name, ' ', user.last_name) AS cashier
			FROM altered_food
			INNER JOIN food ON food.id = altered_food.food_id
			INNER JOIN user ON user.id = altered_food.user_id
			WHERE food.branch_id = $branchId 
				AND DATE(altered_food.date) BETWEEN '$from' AND '$to'
				AND altered_food.is_deleted = false
			ORDER BY altered_food.date DESC
		SQL;
		
		if ($isDeleted) {
			$query = str_replace('AND altered_food.is_deleted = false', 'AND altered_food.is_deleted = true', $query);
		}

		$result = $this->connection->select($query);

		$alteredFood->length = $result->num_rows;
		while ($row = $result->fetch_assoc()) {
			$alteredFood->food[] = $row;
		}
		return $alteredFood;
	}

	/**
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @param bool $isDeleted
	 * @return StdClass
	 */
	public function getSupplied(int $branchId, string $from, string $to, bool $isDeleted): StdClass
	{
		$suppliedFood = new StdClass();

		$query = <<<SQL
			SELECT supplied_food.id, supplied_food.date, food.name, supplied_food.quantity, 
				supplied_food.new_quantity, supplied_food.cost, CONCAT(user.name, ' ', user.last_name) AS cashier
			FROM supplied_food
			INNER JOIN food ON food.id = supplied_food.food_id
			INNER JOIN user ON user.id = supplied_food.user_id
			WHERE food.branch_id = $branchId 
				AND DATE(supplied_food.date) BETWEEN '$from' AND '$to'
				AND supplied_food.is_deleted = false
			ORDER BY supplied_food.date DESC
		SQL;
		
		if ($isDeleted) {
			$query = str_replace('AND supplied_food.is_deleted = false', 'AND supplied_food.is_deleted = true', $query);
		}

		$result = $this->connection->select($query);
		$suppliedFood->length = $result->num_rows;
		while ($row = $result->fetch_assoc()) {
			$suppliedFood->items[] = $row;
		}
		return $suppliedFood;
	}

	/**
	 * @param int $branchId
	 * @return Food[]
	 */
	public function getFoodToDashboard(int $branchId): array
	{
		$food = [];
		$result = $this->connection
			->select("SELECT id FROM $this->food WHERE branch_id = $branchId AND is_showed_in_index = 1 ORDER BY name");
		while ($row = $result->fetch_array()) {
			$food[] = $this->getById(intval($row['id']));
		}
		return $food;
	}

	/**
	 * @param int $id
	 * @param array $data
	 * @return Food|null
	 */
	public function edit(int $id, array $data): Food|null
	{
		$query = Util::prepareUpdateQuery($id, $data, $this->table);
		return ($this->connection->update($query)) ? $this->getById($id) : null;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		$query = Util::prepareDeleteQuery($id, $this->table);
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
		$food = $this->getById($foodId);
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
		return $this->edit($foodId, $dataToUpdate);
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
		$food = $this->getById($foodId);
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
		return $this->edit($foodId, $dataToUpdate);
	}
}
