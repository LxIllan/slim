<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Util;
use App\Application\Model\Dish;
use Exception;

class DishDAO extends DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'dish';

	public function __construct()
	{
		parent::__construct();
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
		usort($dishes, fn($a, $b) => strcmp($a->name, $b->name));
		return $dishes;
	}

	/**
	 * @param int $categoryId
	 * @param int $branchId
	 * @param bool $getAll
	 * @return Dish[]
	 */
	public function getDishesByCategory(int $categoryId, int $branchId, bool $getAll): array
	{
		$dishes = [];
		$query = <<<SQL
			SELECT id 
			FROM dish 
			WHERE category_id = $categoryId 
				AND branch_id = $branchId 
				AND sell_individually = true
				ORDER BY name
		SQL;
		if ($getAll) {
			$query = str_replace('AND sell_individually = true', '', $query);
		}
		$result = $this->connection->select($query);
		while ($row = $result->fetch_assoc()) {
			$dishes[] = $this->getById(intval($row['id']));
		}
		return $dishes;
	}

	/**
	 * @param int $branchId
	 * @return Dish[]
	 */
	public function getCombos(int $branchId): array
	{
		$dishes = [];
		$result = $this->connection
			->select("SELECT id FROM dish WHERE branch_id = $branchId AND is_combo = 1 AND is_special_dish = 0 ORDER BY name");
		while ($row = $result->fetch_assoc()) {
			$dishes[] = $this->getById(intval($row['id']));
		}
		return $dishes;
	}

	/**
	 * @param int $branchId
	 * @return Dish[]
	 */
	public function getSpecialDishes(int $branchId): array
	{
		$dishes = [];
		$result = $this->connection
			->select("SELECT id FROM dish WHERE branch_id = $branchId AND is_combo = 1 AND is_special_dish = 1 ORDER BY name");
		while ($row = $result->fetch_assoc()) {
			$dishes[] = $this->getById(intval($row['id']));
		}
		return $dishes;
	}	

	/**
	 * @param int $comboId
	 * @param array $dishes
	 * @return Dish[]
	 * @throws Exception
	 */
	public function addDishToCombo(int $comboId, array $dishes): array
	{
		$dishesFailed = "";
		foreach ($dishes as $dish) {
			$combo = $this->getById($comboId);
			if (!$combo->is_combo) {
				throw new Exception("$combo->name is not a combo.");
			}

			$dataToInsert = [
				"combo_id" => $comboId,
				"dish_id" => $dish['id']
			];

			if (!$this->connection->insert(Util::prepareInsertQuery($dataToInsert, "dishes_in_combo"))) {
				$dishesFailed .= "{$dish['id']},";
			}
		}
		if (strlen($dishesFailed) > 0) {
			throw new Exception("Dishes with id: $dishesFailed failed to be added to combo.");
		}
		return $this->getDishesByCombo($comboId);
	}

	/**
	 * @param int $comboId
	 * @param int $dishId
	 * @return Dish[]
	 */
	public function deleteDishFromCombo(int $comboId, int $dishId): array
	{
		$query = <<<SQL
			DELETE FROM dishes_in_combo 
			WHERE combo_id = $comboId 
			AND dish_id = $dishId
			LIMIT 1
		SQL;

		if ($this->connection->delete($query)) {
			return $this->getDishesByCombo($comboId);
		}
		return [];
	}

	/**
	 * @param int $branchId
	 * @param string|null $from
	 * @param string|null $to
	 * @return array
	 */
	public function getSold(int $branchId, ?string $from, ?string $to): array
	{
		$query = <<<SQL
			SELECT dishes_in_ticket.dish_id, dish.name, SUM(dishes_in_ticket.quantity) AS quantity
			FROM dishes_in_ticket
			INNER JOIN dish ON dish.id = dishes_in_ticket.dish_id
			WHERE dishes_in_ticket.ticket_id IN (
				SELECT id 
				FROM ticket 
				WHERE branch_id = $branchId 
				AND DATE(date) BETWEEN '$from' AND '$to'
			)
			GROUP BY dishes_in_ticket.dish_id
		SQL;

		$result = $this->connection->select($query);
		$dishesSold = $result->fetch_all(MYSQLI_ASSOC);
		$result->free();
		usort($dishesSold, fn($a, $b) => strcmp($a["name"], $b["name"]));
		return $dishesSold;
	}
}
