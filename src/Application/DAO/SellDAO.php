<?php

declare(strict_types=1);

namespace App\Application\DAO;

use StdClass;
use Exception;
use App\Application\DAO\DishDAO;
use App\Application\DAO\FoodDAO;
use App\Application\Helpers\Util;
use App\Application\Model\Ticket;
use App\Application\Helpers\Connection;
use App\Application\Helpers\EmailTemplate;

class SellDAO
{

	/**
	 * @var Connection $connection
	 */
	private Connection $connection;

	/**
	 * @var DishDAO
	 */
	private DishDAO $dishDAO;

	/**
	 * @var FoodDAO
	 */
	private FoodDAO $foodDAO;

	public function __construct()
	{
		$this->connection = new Connection();
		$this->dishDAO = new DishDAO();
		$this->foodDAO = new FoodDAO();
	}

	/**
	 * @param array $items
	 * @param int $userId
	 * @param int $branchId
	 * @return Ticket|null
	 * @throws Exception
	 */
	public function sell(array $items, int $userId, int $branchId): Ticket|null
	{
		$result = $this->sellWithTicket($items, $userId, $branchId);
		foreach ($items as $item) {
			$dishToSell = $this->dishDAO->getById($item['dish_id'], ['is_combo', 'serving', 'food_id']);
			if ($dishToSell->is_combo) {
				$this->extractDishesFromCombo(intval($dishToSell->id), intval($item['qty']));
			} else {
				$serving = $dishToSell->serving * $item['qty'];
				$this->subtractFood(intval($dishToSell->food_id), $serving);
			}
		}
		return $result;
	}

	private function calcTotalFromDishes(array $items): float
	{
		$total = 0;
		foreach ($items as $item) {
			$total += $this->dishDAO->getById($item['dish_id'], ['price'])->price * $item['qty'];
		}
		return $total;
	}

	/**
	 * @param array $items
	 * @param int $userId
	 * @param int $branchId
	 * @return Ticket|null
	 * @throws Exception
	 */
	public function sellWithTicket(array $items, int $userId, int $branchId): Ticket|null
	{
		$TicketDAO = new \App\Application\DAO\TicketDAO();
		$numTicket = $TicketDAO->getNextNumber($branchId);
		$total = $this->calcTotalFromDishes($items);
		$data = [
			"ticket_number" => $numTicket,
			"total" => $total,
			"branch_id" => $branchId,
			"user_id" => $userId
		];
		$query = Util::prepareInsertQuery($data, 'ticket');
		$this->connection->insert($query);
		$ticket = $TicketDAO->getById($this->connection->getLastId());
		if ($ticket) {
			$ticketId = $ticket->id;
			foreach ($items as $item) {
				$dish = $this->dishDAO->getById($item['dish_id'], ['price']);
				$dataToInsert = [
					"ticket_id" => $ticketId,
					"dish_id" => $dish->id,
					"quantity" => $item['qty'],
					"price" => $dish->price * $item['qty']
				];
				$query = Util::prepareInsertQuery($dataToInsert, 'dishes_in_ticket');
				if (!$this->connection->insert($query)) {
					return null;
				}
			}
		}
		return $TicketDAO->getById(intval($ticket->id));
	}

	/**
	 * @param int $comboId
	 * @param int $qty
	 * @return void
	 * @throws Exception
	 */
	public function extractDishesFromCombo(int $comboId, int $qty): void
	{
		$dishes = $this->dishDAO->getDishesByCombo($comboId);
		foreach ($dishes as $dish) {
			if ($dish->is_combo) {
				$this->extractDishesFromCombo(intval($dish->id), $qty);
			} else {
				$serving = $dish->serving * $qty;
				$this->subtractFood(intval($dish->food_id), $serving);
			}
		}
	}

	/**
	 * @param int $foodId
	 * @param float $qty
	 * @return bool
	 * @throws Exception
	 */
	private function subtractFood(int $foodId, float $qty): bool
	{		
		$food = $this->foodDAO->getById($foodId);

		$newQty = $food->qty - $qty;
		$dataToUpdate = [
			"qty" => $newQty
		];

		if (($newQty <= $food->qty_notify) && ($food->is_notify_sent == 0)) {
			$branchDAO = new \App\Application\DAO\BranchDAO();
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
	 * @param array $items
	 * @param string $reason
	 * @param int $userId
	 * @param int $branchId
	 * @return bool
	 */
	public function courtesy(array $items, string $reason, int $userId, int $branchId) {
		$result = false;
		foreach ($items as $item) {
			$dishToSell = $this->dishDAO->getById($item['dish_id'], ['is_combo', 'serving', 'food_id', 'price']);
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
	 * @return StdClass|array
	 */
	public function getAllCourtesies(int $branchId, string $from, string $to, bool $isDeleted): StdClass|array
	{
		$total = Util::getSumFromTable($this->table, 'price', $branchId, $from, $to, "courtesy.is_deleted = '$isDeleted'");

		if ($total == 0) {
			return ['length' => 0];
		}

		$query = <<<SQL
			SELECT courtesy.id, courtesy.date, dish.name, courtesy.quantity, courtesy.price, courtesy.reason,
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
}
