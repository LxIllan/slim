<?php

declare(strict_types=1);

namespace App\Application\DAO;

use StdClass;

class ExpenseDAO extends DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'expense';

	public function __construct()
	{
		parent::__construct();
	}	

	/**
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @param string $reason
	 * @param bool $isDeleted
	 * @return StdClass
	 */
	public function getHistory(int $branchId, string $from, string $to, string $reason, bool $isDeleted): StdClass
	{
		$expenses = new StdClass();
		$expenses->amount = 0;        

		$query = <<<SQL
			SELECT expense.id, expense.date, expense.amount, expense.reason, 
				CONCAT(user.name, ' ' ,user.last_name) AS cashier
			FROM expense
			JOIN user ON expense.user_id = user.id
			WHERE expense.branch_id = $branchId
				AND DATE(expense.date) >= '$from'
				AND DATE(expense.date) <= '$to'
				AND expense.reason LIKE '%$reason%'
				AND expense.is_deleted = false
			ORDER BY date DESC
		SQL;

		if ($isDeleted) {
			$query = str_replace('expense.is_deleted = false', 'expense.is_deleted = true', $query);
		}

		$result = $this->connection->select($query);
		$expenses->length = $result->num_rows;

		if ($expenses->length == 0) {
			$expenses->items = [];
			return $expenses;
		}

		while ($row = $result->fetch_assoc()) {
			$expenses->items[] = $row;
			$expenses->amount += floatval($row['amount']);
		}
		return $expenses;
	}
}
