<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Helpers\Util;
use App\Application\Helpers\EmailTemplate;
use App\Application\Model\Ticket;
use App\Application\DAO\FoodDAO;
use App\Application\DAO\DishDAO;

use Exception;

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
			$dishToSell = $this->dishDAO->getById($item['dish_id'], ['id', 'is_combo', 'serving', 'food_id']);
			if ($dishToSell->is_combo) {
				$this->extractDishesFromCombo(intval($dishToSell->id), intval($item['quantity']));
			} else {
				$serving = $dishToSell->serving * $item['quantity'];
				$this->subtractFood(intval($dishToSell->food_id), $serving);
			}
		}
		return $result;
	}

	private function calcTotalFromDishes(array $items): float
	{
		$total = 0;
		foreach ($items as $item) {
			$total += $this->dishDAO->getById($item['dish_id'])->price * $item['quantity'];
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
		$TicketController = new \App\Application\Controllers\TicketController();
		$numTicket = $TicketController->getNextNumber($branchId);
		$total = $this->calcTotalFromDishes($items);
		$data = [
			"ticket_number" => $numTicket,
			"total" => $total,
			"branch_id" => $branchId,
			"user_id" => $userId
		];
		$query = Util::prepareInsertQuery($data, 'ticket');
		$this->connection->insert($query);
		$ticket = $TicketController->getById($this->connection->getLastId());
		if ($ticket) {
			$ticketId = $ticket->id;
			foreach ($items as $item) {
				$dish = $this->dishDAO->getById($item['dish_id']);
				$dataToInsert = [
					"ticket_id" => $ticketId,
					"dish_id" => $dish->id,
					"quantity" => $item['quantity'],
					"price" => $dish->price * $item['quantity']
				];
				$query = Util::prepareInsertQuery($dataToInsert, 'dishes_in_ticket');
				if (!$this->connection->insert($query)) {
					return null;
				}
			}
		}
		return $TicketController->getById(intval($ticket->id));
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