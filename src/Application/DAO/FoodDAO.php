<?php

declare(strict_types=1);

namespace App\Application\DAO;

use StdClass;
use Exception;
use App\Application\Model\Food;
use App\Application\Helpers\Util;
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
			if ($this->connection->update($query)) {
				$dishDAO = new DishDAO();
				$dishes = $dishDAO->getDishesByFood($id);
				foreach ($dishes as $dish) {
					$dishDAO->delete(intval($dish->id));
				}
				return true;
			} else {
				return false;
			}
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
			SELECT $table.id, $table.date, food.name, $table.qty, $reason
				$table.new_qty, $table.cost, CONCAT(user.name, ' ', user.last_name) AS cashier
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
			->select("SELECT id FROM $this->food WHERE branch_id = $branchId AND show_in_index = 1 ORDER BY name");
		while ($row = $result->fetch_array()) {
			$food[] = $this->getById(intval($row['id']));
		}
		return $food;
	}

	/**
	 * @param int $foodId
	 * @param float $qty
	 * @param int $userId
	 * @param int $branchId
	 * @return Food
	 */
	public function supply(int $foodId, float $qty, int $userId, int $branchId): Food
	{
		$food = $this->getById($foodId);
		$newQty = $food->qty + $qty;
		$cost = $food->cost * $qty;

		$dataToInsert = [
			"food_id" => $foodId,
			"qty" => $qty,
			"new_qty" => $newQty,
			"cost" => $cost,
			"user_id" => $userId,
			"branch_id" => $branchId
		];

		$this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'supplied_food'));

		$dataToUpdate = [
			"qty" => $newQty,
			"is_notify_sent" => false
		];
		return $this->edit($foodId, $dataToUpdate);
	}

	/**
	 * @param int $id
	 * @param string $table
	 * @return bool
	 */
	public function cancelSuppliedOrAltered(int $id, string $table): bool
	{
		$table = "${table}_food";
		$suppliedFood = $this->connection->select("SELECT * FROM $table WHERE id = $id")->fetch_object();
		
		if (is_null($suppliedFood)) {
			throw new Exception("Register not found.");
		}
		
		if ($suppliedFood->is_deleted) {
			throw new Exception("This register has already been cancelled.");
		}
		
		$food = $this->getById(intval($suppliedFood->food_id));

		$suppliedFood->qty = floatval($suppliedFood->qty) * -1;
		$newQty = $food->qty + $suppliedFood->qty;
		
		$dataToUpdate = [
			"qty" => $newQty
		];
		$this->edit(intval($food->id), $dataToUpdate);

		$dataToUpdate = [
			"is_deleted" => 1,
			"deleted_at" => date('Y-m-d H:i:s')
		];
		$query = Util::prepareUpdateQuery($id, $dataToUpdate, $table);
		return $this->connection->update($query);
	}

	/**
	 * @param int $foodId
	 * @param float $qty
	 * @param string $reason
	 * @param int $userId
	 * @param int $branchId
	 * @return Food
	 */
	public function alter(int $foodId, float $qty, string $reason, int $userId, int $branchId): Food
	{
		$food = $this->getById($foodId);
		$newQty = $food->qty + $qty;
		$cost = $food->cost * $qty;

		$dataToInsert = [
			"food_id" => $foodId,
			"qty" => $qty,
			"reason" => $reason,
			"new_qty" => $newQty,
			"cost" => $cost,
			"user_id" => $userId,
			"branch_id" => $branchId
		];

		$this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'altered_food'));

		$dataToUpdate = [
			"qty" => $newQty
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
			$dish = $dishDAO->getById(intval($soldDish['dish_id']), ['name', 'is_combo', 'serving', 'food_id']);
			$dish->qty = $soldDish['qty'];

			if ($dish->is_combo) {
				$foodSold = $this->extractDishesFromCombo(intval($dish->id), intval($dish->qty), $foodSold);
			} else {
				$foodSold = $this->subtractFood(intval($dish->food_id), floatval($dish->qty * $dish->serving), $foodSold);
			}
		}
		return $foodSold;
	}

	/**
	 * @param int $comboId
	 * @param int $qty
	 * @param array $foodSold
	 * @return array
	 * @throws Exception
	 */
	public function extractDishesFromCombo(int $comboId, int $qty, array $foodSold): array
	{
		$dishDAO = new \App\Application\DAO\DishDAO();
		$dishes = $dishDAO->getDishesByCombo($comboId);
		foreach ($dishes as $dish) {
			if ($dish->is_combo) {
				$foodSold = $this->extractDishesFromCombo(intval($dish->id), $qty, $foodSold);
			} else {
				$serving = $dish->serving * $qty;
				$foodSold = $this->subtractFood(intval($dish->food_id), $serving, $foodSold);
			}
		}
		return $foodSold;
	}

	/**
	 * @param int $foodId
	 * @param float $qty
	 * @param array $foodSold
	 * @return array
	 * @throws Exception
	 */
	private function subtractFood(int $foodId, float $qty, array $foodSold): array
	{
		$id = array_search(intval($foodId), array_column($foodSold, 'id'));
		if ($id !== false) {
			$foodSold[$id]["qty"] += $qty;
		} else {
			$food = $this->getById($foodId, ['name']);
			$foodSold[] = [
				"id" => $foodId,
				"name" => $food->name,
				"qty" => $qty
			];
		}
		return $foodSold;
	}
}
