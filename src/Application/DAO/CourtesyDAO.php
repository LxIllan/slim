<?php

declare(strict_types=1);

namespace App\Application\DAO;

use StdClass;
use Exception;
use App\Application\Helpers\Util;
use App\Application\Helpers\Connection;
use App\Application\Helpers\EmailTemplate;

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
	 * @param array $items
	 * @param string $reason
	 * @param int $userId
	 * @param int $branchId
	 * @return bool
	 */
	public function courtesy(array $items, string $reason, int $userId, int $branchId) {
		$result = false;
		foreach ($items as $item) {
			$dishToSell = $this->dishDAO->getById($item['dish_id'], ['id', 'is_combo', 'serving', 'food_id', 'price']);
			$result = $this->registerCourtesy(intval($dishToSell->id), intval($item['quantity']), floatval($dishToSell->price), $reason, $userId, $branchId);
			if ($dishToSell->is_combo) {
				$this->extractDishesFromCombo(intval($dishToSell->id), intval($item['quantity']));
			} else {
				$serving = $dishToSell->serving * $item['quantity'];
				$this->subtractFood(intval($dishToSell->food_id), $serving);
			}
		}
		return $result;
	}

	/**
	 * @param int $dishId
	 * @param int $quantity
	 * @param float $price
	 * @param string $reason
	 * @param int $userId
	 * @param int $branchId
	 * @return bool
	 */
	private function registerCourtesy(int $dishId, int $quantity, float $price, string $reason, int $userId, int $branchId): bool
	{
		$dataToInsert = [
			"dish_id" => $dishId,
			"quantity" => $quantity,
			"price" => $price,
			"reason" => $reason,
			"user_id" => $userId,
			"branch_id" => $branchId
		];
		$query = Util::prepareInsertQuery($dataToInsert, 'courtesy');
		return $this->connection->insert($query);
	}

	/**
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @param bool $isDeleted
	 * @return StdClass
	 */
	public function getAll(int $branchId, string $from, string $to, bool $isDeleted): StdClass
	{
		$courtesies = new StdClass();

		$query = <<<SQL
			SELECT courtesy.id, courtesy.date, dish.name, courtesy.quantity, courtesy.price, courtesy.reason,
				CONCAT(user.name, ' ' , user.last_name) AS cashier
			FROM courtesy
			INNER JOIN dish ON courtesy.dish_id = dish.id
			INNER JOIN user ON courtesy.user_id = user.id
			WHERE courtesy.branch_id = $branchId
				AND DATE(courtesy.date) BETWEEN '$from' AND '$to'
				AND courtesy.is_deleted = false
			ORDER BY courtesy.date DESC
		SQL;

		if ($isDeleted) {
			$query = str_replace('courtesy.is_deleted = false', 'courtesy.is_deleted = true', $query);
		}

		$result = $this->connection->select($query);
		$courtesies->length = $result->num_rows;
		while ($row = $result->fetch_assoc()) {
			$courtesies->items[] = $row;
		}
		return $courtesies;
	}
	/**
	 * @param int $comboId
	 * @param int $quantity
	 * @return void
	 * @throws Exception
	 */
	public function extractDishesFromCombo(int $comboId, int $quantity): void
	{
		$dishes = $this->dishDAO->getDishesByCombo($comboId);
		foreach ($dishes as $dish) {
			if ($dish->is_combo) {
				$this->extractDishesFromCombo(intval($dish->id), $quantity);
			} else {
				$serving = $dish->serving * $quantity;
				$this->subtractFood(intval($dish->food_id), $serving);
			}
		}
	}

	/**
	 * @param int $foodId
	 * @param float $quantity
	 * @return bool
	 * @throws Exception
	 */
	private function subtractFood(int $foodId, float $quantity): bool
	{		
		$food = $this->foodDAO->getById($foodId);

		$newQuantity = $food->quantity - $quantity;
		$dataToUpdate = [
			"quantity" => $newQuantity
		];

		if (($newQuantity <= $food->quantity_notif) && ($food->is_notif_sent == 0)) {
			$branchDAO = new \App\Application\DAO\BranchDAO();
			$branch = $branchDAO->getById(intval($food->branch_id));
			$data = [
				'subject' => "Notificación de: $branch->location",
				'food_name' => $food->name,
				'quantity' => $newQuantity,
				'branch_name' => $branch->name,
				'branch_location' => $branch->location,
				'email' => $branch->admin_email
			];
			if (Util::sendMail($data, EmailTemplate::NOTIFICATION_TO_ADMIN)) {
				$dataToUpdate["is_notif_sent"] = true;
			} else {
				throw new Exception('Error to send email notification to admin.');
			}
		}
		return $this->connection->update(Util::prepareUpdateQuery($foodId, $dataToUpdate, 'food'));
	}
}
