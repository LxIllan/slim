<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Model\Branch;

class BranchDAO extends DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'branch';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return Branch[]
	 */
	public function getAll(): array
	{
		$branches = [];
		$result = $this->connection->select("SELECT id FROM $this->table");
		while ($row = $result->fetch_assoc()) {
			$branches[] = $this->getById(intval($row['id']));
		}
		$result->free();
		return $branches;
	}
}
