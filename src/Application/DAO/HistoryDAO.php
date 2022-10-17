<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use StdClass;

class HistoryDAO
{
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
	 * @return StdClass
	 */
	public function getCourtesies(int $branchId, string $from, string $to): StdClass
	{
		$courtesies = new StdClass();
		$courtesies->amount = $this->getSumFromTable('price', 'courtesy', $branchId, $from, $to);

		if ($courtesies->amount == 0) {
			$courtesies->length = 0;
			$courtesies->courtesies = [];
			return $courtesies;
		}

		$result = $this->connection->select("SELECT courtesy.id, courtesy.date, dish.name, courtesy.price, "
			. "courtesy.quantity, courtesy.reason, CONCAT(user.name, ' ' ,user.last_name) AS cashier "
			. "FROM courtesy, dish, user "
			. "WHERE courtesy.user_id = user.id AND courtesy.dish_id = dish.id "
			. "AND courtesy.branch_id = '$branchId' "
			. "AND DATE(courtesy.date) >= '$from' AND DATE(courtesy.date) <= '$to' ORDER BY date DESC");

		$courtesies->length = $result->num_rows;
		while ($row = $result->fetch_assoc()) {
			$courtesies->items[] = $row;
		}
		return $courtesies;
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
		$query = <<<EOF
			SELECT SUM($column) 
			FROM $table 
			WHERE DATE(date) >= '$from'
				AND DATE(date) <= '$to'
				AND branch_id = $branchId
		EOF;
		$row = $this->connection->select($query)->fetch_array();
		return floatval($row[0]);
	}
}
