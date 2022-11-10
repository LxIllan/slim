<?php

declare(strict_types=1);

namespace App\Application\DAO;

use StdClass;
use Exception;
use App\Application\Helpers\Util;
use App\Application\Model\Ticket;
use App\Application\Helpers\Connection;

class TicketDAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'ticket';

	/**
	 * @var Connection
	 */
	private Connection $connection;

	public function __construct()
	{
		$this->connection = new Connection();
	}

	/**
	 * @param int $id
	 * @return Ticket|null
	 */
	public function getById(int $id): Ticket|null
	{
		$ticket = $this->connection
			->select("SELECT * FROM $this->table WHERE id = $id")
			->fetch_object('App\Application\Model\Ticket');
		
		if (is_null($ticket)) {
			throw new Exception("Ticket not found.");
		}
		
		$query = <<<SQL
			SELECT dish.id, dish.name, dishes_in_ticket.qty, dishes_in_ticket.price
			FROM dishes_in_ticket
			JOIN dish ON dishes_in_ticket.dish_id = dish.id
			WHERE dishes_in_ticket.ticket_id = $ticket->id
		SQL;

		$result = $this->connection->select($query);
		$ticket->dishes = $result->fetch_all(MYSQLI_ASSOC);
		$result->free();
		return $ticket;
	}

	/**
	 * @param int $branchId
	 * @return int
	 */
	public function getNextNumber(int $branchId): int
	{
		$num_ticket = $this->connection
			->select("SELECT ticket_number FROM branch WHERE id = $branchId")
			->fetch_assoc()["ticket_number"];
		$this->connection->update("UPDATE branch SET ticket_number = ($num_ticket + 1) WHERE id = $branchId");
		return intval($num_ticket);
	}
	
	/**
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @return StdClass
	 */
	public function getAll(int $branchId, string $from, string $to): StdClass
	{
		$tickets = new StdClass();
		$tickets->total = 0;

		$query = <<<SQL
			SELECT ticket.id, ticket.ticket_number, ticket.total, 
				ticket.date, CONCAT(user.name, ' ' ,user.last_name) AS cashier
			FROM ticket
			JOIN user ON ticket.user_id = user.id
			WHERE ticket.branch_id = $branchId
				AND DATE(ticket.date) BETWEEN '$from' AND '$to'
				ORDER BY ticket.date DESC
		SQL;

		$result = $this->connection->select($query);
		$tickets->length = $result->num_rows;
		if ($tickets->length == 0) {
			$tickets->items = [];
			return $tickets;
		}
		while ($row = $result->fetch_assoc()) {
			$item = $row;
			$ticketId = $row['id'];
			$tickets->total += $row['total'];
			$query = <<<SQL
				SELECT dish.id, dish.name, dishes_in_ticket.qty, dishes_in_ticket.price
				FROM dishes_in_ticket
				JOIN dish ON dishes_in_ticket.dish_id = dish.id
				WHERE dishes_in_ticket.ticket_id = $ticketId
			SQL;
			$resultDish = $this->connection->select($query);
			while ($rowDish = $resultDish->fetch_assoc()) {
				$item['dishes'][] = $rowDish;
			}
			$tickets->items[] = $item;
		}
		return $tickets;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function cancel(int $id): bool
	{
		$ticket = $this->getById($id);

		if ($ticket->is_deleted) {
			throw new Exception("This register has already been canceled.");
		}

		$dishDAO = new DishDAO();
		foreach ($ticket->dishes as $dishInTicket) {
			$dish = $dishDAO->getById(intval($dishInTicket['id']), ['is_combo', 'serving', 'food_id']);
			if ($dish->is_combo) {
				$this->extractDishesFromCombo(intval($dish->id), intval($dishInTicket['qty']));
			} else {
				$this->addQtyFood(intval($dish->food_id), floatval($dish->serving * $dishInTicket['qty']));
			}
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
