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
	 * @param array $items
	 * @param int $userId
	 * @param int $branchId
	 * @return Ticket|null
	 * @throws Exception
	 */
	public function create(array $items, int $userId, int $branchId): Ticket|null
	{
		$dishDAO = new DishDAO();
		$result = $this->sellWithTicket($items, $userId, $branchId);
		foreach ($items as $item) {
			$dishToSell = $dishDAO->getById($item['dish_id'], ['is_combo', 'serving', 'food_id']);
			if ($dishToSell->is_combo) {
				$dishDAO->extractDishesFromCombo(intval($dishToSell->id), intval($item['qty']), 'subtractQtyFood');
			} else {
				$serving = $dishToSell->serving * $item['qty'];
				$dishDAO->subtractQtyFood(intval($dishToSell->food_id), $serving);
			}
		}
		return $result;
	}

	private function calcTotalFromDishes(array $items): float
	{
		$dishDAO = new DishDAO();
		$total = 0;
		foreach ($items as $item) {
			$total += $dishDAO->getById($item['dish_id'], ['price'])->price * $item['qty'];
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
		$dishDAO = new DishDAO();
		$numTicket = $this->getNextNumber($branchId);
		$total = $this->calcTotalFromDishes($items);
		$data = [
			"ticket_number" => $numTicket,
			"total" => $total,
			"branch_id" => $branchId,
			"user_id" => $userId
		];
		$query = Util::prepareInsertQuery($data, 'ticket');
		$this->connection->insert($query);
		$ticket = $this->getById($this->connection->getLastId());
		if ($ticket) {
			$ticketId = $ticket->id;
			foreach ($items as $item) {
				$dish = $dishDAO->getById($item['dish_id'], ['price']);
				$dataToInsert = [
					"ticket_id" => $ticketId,
					"dish_id" => $dish->id,
					"qty" => $item['qty'],
					"price" => $dish->price * $item['qty']
				];
				$query = Util::prepareInsertQuery($dataToInsert, 'dishes_in_ticket');
				if (!$this->connection->insert($query)) {
					return null;
				}
			}
		}
		return $this->getById(intval($ticket->id));
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
				$dishDAO->extractDishesFromCombo(intval($dish->id), intval($dishInTicket['qty']), 'addQtyFood');
			} else {
				$dishDAO->addQtyFood(intval($dish->food_id), floatval($dish->serving * $dishInTicket['qty']));
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
}
