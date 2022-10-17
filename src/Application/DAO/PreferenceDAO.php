<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Model\Preference;

class PreferenceDAO extends DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'preference';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param string $key
	 * @param int $branchId
	 * @return Preference
	 */
	public function getByKey(string $key, int $branchId): Preference
	{
		return $this->connection
			->select("SELECT * FROM $this->table WHERE branch_id = $branchId AND `key` = '$key'")
			->fetch_object('App\Application\Model\Preference');
	}

	/**
	 * @return Preference[]
	 */
	public function getAll(int $branchId): array
	{
		$preferences = [];
		$result = $this->connection->select("SELECT id FROM $this->table WHERE branch_id = $branchId");

		while ($row = $result->fetch_assoc()) {
			$preferences[] = $this->getById(intval($row['id']));
		}
		return $preferences;
	}	
}
