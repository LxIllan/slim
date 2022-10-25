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
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		if ($id == 1) {
			$data = [
				'is_deleted' => 1,
				'deleted_at' => date('Y-m-d H:i:s')
			];
			$query = Util::prepareUpdateQuery($id, $data, $this->table);
			return $this->connection->update($query);
		} else {
			$query = Util::prepareDeleteQuery($id, $this->table);
			return $this->connection->delete($query);
		}
	}

	/**
	 * @param int $branchId
	 * @return Food[]
	 */
	public function getAll(int $branchId): array
	{
		$food = [];
		$result = $this->connection
			->select("SELECT id FROM $this->table WHERE branch_id = $branchId AND is_deleted = 0 ORDER BY name");
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
	 * @param string $table
	 * @return StdClass
	 */
	public function getSuppliedOrAltered(int $branchId, string $from, string $to, bool $isDeleted, string $table): StdClass
	{
		$table = "${table}_food";
		$reason = (str_contains($table, 'altered')) ? 'altered_food.reason,' : '';

		$query = <<<SQL
			SELECT $table.id, $table.date, food.name, $table.quantity, $reason
				$table.new_quantity, $table.cost, CONCAT(user.name, ' ', user.last_name) AS cashier
			FROM $table
			INNER JOIN food ON food.id = $table.food_id
			INNER JOIN user ON user.id = $table.user_id
			WHERE food.branch_id = $branchId 
				AND DATE($table.date) BETWEEN '$from' AND '$to'
				AND $table.is_deleted = '$isDeleted'
			ORDER BY $table.date DESC
		SQL;

		$std = new StdClass();
		$result = $this->connection->select($query);
		$std->length = $result->num_rows;
		$std->items = $result->fetch_all(MYSQLI_ASSOC);
		$result->free();
		return $std;
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
		$dishDAO = new \App\Application\DAO\DishDAO();
		$soldDishes = $dishDAO->getSold($branchId, $from, $to);
		
		foreach ($soldDishes as $soldDish) {
			$dish = $dishDAO->getById(intval($soldDish['id']), ['id', 'name', 'is_combo', 'serving', 'food_id']);
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
		$dishDAO = new \App\Application\DAO\DishDAO();
		$dishes = $dishDAO->getDishesByCombo($comboId);
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
