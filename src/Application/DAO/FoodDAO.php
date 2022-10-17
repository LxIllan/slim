<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Util;
use App\Application\Model\Food;
use StdClass;

class FoodDAO extends DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'food';

	public function __construct()
	{
		parent::__construct();
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
			$alteredFood->items[] = $row;
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

	/**
	 * @param int $branchId
	 * @param string|null $from
	 * @param string|null $to
	 * @return array
	 */
	public function getSold(int $branchId, ?string $from, ?string $to): array
	{
		$foodSold = [];
		$dishController = new \App\Application\Controllers\DishController();
		$soldDishes = $dishController->getSold($branchId, $from, $to);		
		
		foreach ($soldDishes as $soldDish) {
			$dish = $dishController->getDishById(intval($soldDish['id']), ['id', 'name', 'is_combo', 'serving', 'food_id']);
			$dish->quantity = $soldDish['quantity'];

			if ($dish->is_combo) {
				$foodSold = $this->extractDishesFromCombo(intval($dish->id), intval($dish->quantity), $foodSold);
			} else {
				$foodSold = $this->subtractFood(intval($dish->food_id), floatval($dish->quantity * $dish->serving), $foodSold);
			}
		}
		return $foodSold;
	}

	/**
	 * @param int $comboId
	 * @param int $quantity
	 * @param array $foodSold
	 * @return array
	 * @throws Exception
	 */
	public function extractDishesFromCombo(int $comboId, int $quantity, array $foodSold): array
	{
		$dishController = new \App\Application\Controllers\DishController();
		$dishes = $dishController->getDishesByCombo($comboId);
		foreach ($dishes as $dish) {
			if ($dish->is_combo) {
				$foodSold = $this->extractDishesFromCombo(intval($dish->id), $quantity, $foodSold);
			} else {
				$serving = $dish->serving * $quantity;
				$foodSold = $this->subtractFood(intval($dish->food_id), $serving, $foodSold);
			}
		}
		return $foodSold;
	}

	/**
	 * @param int $foodId
	 * @param float $quantity
	 * @param array $foodSold
	 * @return array
	 * @throws Exception
	 */
	private function subtractFood(int $foodId, float $quantity, array $foodSold): array
	{
		$id = array_search(intval($foodId), array_column($foodSold, 'id'));
		if ($id !== false) {
			$foodSold[$id]["quantity"] += $quantity;
		} else {
			$food = $this->getById($foodId, ['name']);
			$foodSold[] = [
				"id" => $foodId,
				"name" => $food->name,
				"quantity" => $quantity
			];
		}
		return $foodSold;
	}
}
