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
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @param bool $isDeleted
	 * @return StdClass|array
	 */
	public function getAll(int $branchId, string $from, string $to, bool $isDeleted): StdClass|array
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
