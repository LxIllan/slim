<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Model\Ticket;
use StdClass;

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
		
		$query = <<<SQL
			SELECT dish.name, dishes_in_ticket.quantity, dishes_in_ticket.price
			FROM dishes_in_ticket
			JOIN dish ON dishes_in_ticket.dish_id = dish.id
			WHERE dishes_in_ticket.ticket_id = $ticket->id
		SQL;

		$resultDish = $this->connection->select($query);
		while ($rowDish = $resultDish->fetch_assoc()) {
			$ticket->dishes[] = $rowDish;
		}
		
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
			SELECT ticket.id, ticket.ticket_number, ticket.total, ticket.date, CONCAT(user.name, ' ' ,user.last_name) AS cashier
			FROM ticket
			JOIN user ON ticket.user_id = user.id
			WHERE ticket.branch_id = $branchId
				AND DATE(ticket.date) BETWEEN '$from' AND '$to'
				ORDER BY date DESC
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
				SELECT dish.name, dishes_in_ticket.quantity, dishes_in_ticket.price
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
	 * @param string $column
	 * @param string $table
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @return float
	 */
	private function getSumFromTable(string $column, string $table, int $branchId, string $from, string $to): float
	{
		$query = <<<SQL
			SELECT SUM($column) 
			FROM $table 
			WHERE DATE(date) >= '$from'
				AND DATE(date) <= '$to'
				AND branch_id = $branchId
		SQL;
		$row = $this->connection->select($query)->fetch_array();
		return floatval($row[0]);
	}
}
