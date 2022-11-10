<?php

declare(strict_types=1);

namespace App\Application\DAO;

use StdClass;
use Exception;
use App\Application\Helpers\Util;
use App\Application\Helpers\Connection;

class CourtesyDAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'courtesy';

	/**
	 * @var Connection
	 */
	private Connection $connection;

	public function __construct()
	{
		$this->connection = new Connection();
	}

	/**
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @param bool $isDeleted
	 * @return StdClass|array
	 */
	public function getAll(int $branchId, string $from, string $to, bool $isDeleted): StdClass|array
	{
		$total = Util::getSumFromTable($this->table, 'price', $branchId, $from, $to, "courtesy.is_deleted = '$isDeleted'");

		if ($total == 0) {
			return ['length' => 0];
		}

		$query = <<<SQL
			SELECT courtesy.id, courtesy.date, dish.name, courtesy.qty, courtesy.price, courtesy.reason,
				CONCAT(user.name, ' ' , user.last_name) AS cashier
			FROM courtesy
			INNER JOIN dish ON courtesy.dish_id = dish.id
			INNER JOIN user ON courtesy.user_id = user.id
			WHERE courtesy.branch_id = $branchId
				AND DATE(courtesy.date) BETWEEN '$from' AND '$to'
				AND courtesy.is_deleted = '$isDeleted'
			ORDER BY courtesy.date DESC
		SQL;
		
		$std = new StdClass();
		$result = $this->connection->select($query);
		$std->length = $result->num_rows;
		$std->total = $total;
		$std->items = $result->fetch_all(MYSQLI_ASSOC);
		$result->free();
		return $std;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function cancel(int $id): bool
	{
		$courtesy = $this->connection
			->select("SELECT * FROM $this->table WHERE id = $id")
			->fetch_object();

		if (is_null($courtesy)) {
			throw new Exception("Register not found.");
		}

		if ($courtesy->is_deleted) {
			throw new Exception("This register has already been canceled.");
		}

		$dishDAO = new DishDAO();
		$dish = $dishDAO->getById(intval($courtesy->dish_id), ['is_combo', 'serving', 'food_id']);

		if ($dish->is_combo) {
			$this->extractDishesFromCombo(intval($dish->id), intval($courtesy->qty));
		} else {
			$this->addQtyFood(intval($dish->food_id), floatval($dish->serving * $courtesy->qty));
		}

		$dataToUpdate = [
			"is_deleted" => 1,
			"deleted_at" => date('Y-m-d H:i:s')
		];
		
		return $this->connection->update(
			Util::prepareUpdateQuery($id, $dataToUpdate, $this->table)
		);
	}

	/**
	 * @param int $comboId
	 * @param int $qty
	 * @return void
	 */
	public function extractDishesFromCombo(int $comboId, int $qty): void
	{
		$dishes = $this->dishDAO->getDishesByCombo($comboId);
		foreach ($dishes as $dish) {
			if ($dish->is_combo) {
				$this->extractDishesFromCombo(intval($dish->id), $qty);
			} else {
				$this->addQtyFood(intval($dish->food_id), floatval($dish->serving * $qty));
			}
		}
	}

	/**
	 * @param int $foodId
	 * @param float $qty
	 * @return bool
	 * @throws Exception
	 */
	private function addQtyFood(int $foodId, float $qty): bool
	{
		$foodDAO = new FoodDAO();
		$food = $foodDAO->getById($foodId, ['qty']);
		$newQty = $food->qty + $qty;
		return $this->connection->update(
			Util::prepareUpdateQuery($foodId, ["qty" => $newQty], 'food')
		);
	}
}
