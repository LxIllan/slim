<?php

declare(strict_types=1);

namespace App\Application\DAO;

use Exception;
use App\Application\Model\Dish;
use App\Application\Helpers\Util;
use App\Application\Helpers\EmailTemplate;

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
		usort($dishes, fn ($a, $b) => strcmp($a->name, $b->name));
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
			SELECT dishes_in_ticket.dish_id, dish.name, SUM(dishes_in_ticket.quantity) AS qty
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
		usort($dishesSold, fn ($a, $b) => strcmp($a["name"], $b["name"]));
		return $dishesSold;
	}

	/**
	 * @param int $comboId
	 * @param int $qty
	 * @return void
	 * @throws Exception
	 */
	public function extractDishesFromCombo(int $comboId, int $qty, callable $function): void
	{
		$dishes = $this->dishDAO->getDishesByCombo($comboId);
		foreach ($dishes as $dish) {
			if ($dish->is_combo) {
				$this->extractDishesFromCombo(intval($dish->id), $qty, $function);
			} else {
				$serving = $dish->serving * $qty;
				call_user_func($function, intval($dish->food_id), $serving);
				// $this->subtractFood(intval($dish->food_id), $serving);
			}
		}
	}

	/**
	 * @param int $foodId
	 * @param float $qty
	 * @return bool
	 * @throws Exception
	 */
	public function subtractQtyFood(int $foodId, float $qty): bool
	{
		$foodDAO = new FoodDAO($this->connection);
		$food = $foodDAO->getById($foodId);

		$newQty = $food->qty - $qty;
		$dataToUpdate = [
			"qty" => $newQty
		];

		if (($newQty <= $food->qty_notify) && ($food->is_notify_sent == 0)) {
			$branchDAO = new BranchDAO();
			$branch = $branchDAO->getById(intval($food->branch_id));
			$data = [
				'subject' => "Notificación de: $branch->name",
				'food_name' => $food->name,
				'qty' => $newQty,
				'branch_name' => $branch->name,
				'branch_location' => $branch->name,
				'email' => $branch->admin_email
			];
			if (Util::sendMail($data, EmailTemplate::NOTIFICATION_TO_ADMIN)) {
				$dataToUpdate["is_notify_sent"] = true;
			} else {
				throw new Exception('Error to send email notification to admin.');
			}
		}
		return $this->connection->update(Util::prepareUpdateQuery($foodId, $dataToUpdate, 'food'));
	}

	/**
	 * @param int $foodId
	 * @param float $qty
	 * @return bool
	 * @throws Exception
	 */
	public function addQtyFood(int $foodId, float $qty): bool
	{
		$foodDAO = new FoodDAO();
		$food = $foodDAO->getById($foodId, ['qty']);
		$newQty = $food->qty + $qty;
		return $this->connection->update(
			Util::prepareUpdateQuery($foodId, ["qty" => $newQty], 'food')
		);
	}
}
