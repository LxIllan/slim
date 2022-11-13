<?php

declare(strict_types=1);

namespace App\Application\DAO;

use StdClass;
use App\Application\Helpers\Util;

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
	 * @return StdClass|array
	 */
	public function getAll(int $branchId, string $from, string $to, string $reason, bool $isDeleted): StdClass|array
	{
		$total = Util::getSumFromTable($this->table, 'amount', $branchId, $from, $to, "expense.is_deleted = '$isDeleted'");

		if ($total == 0) {
			return ['length' => 0];
		}

		$query = <<<SQL
			SELECT expense.id, expense.date, expense.amount, expense.reason,
				CONCAT(user.name, ' ' ,user.last_name) AS user
			FROM expense
			JOIN user ON expense.user_id = user.id
			WHERE expense.branch_id = $branchId
				AND DATE(expense.date) BETWEEN '$from' AND '$to'
				AND expense.reason LIKE '%$reason%'
				AND expense.is_deleted = '$isDeleted'
			ORDER BY date DESC
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
