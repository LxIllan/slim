<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Model\Branch;
use App\Application\Helpers\Util;

class BranchDAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'branch';

	/**
	 * @var Connection $connection
	 */
	private Connection $connection;

	public function __construct()
	{
		$this->connection = new Connection();
	}

	/**
	 * @param array $data
	 * @return Branch|null
	 */
	public function create(array $data): Branch|null
	{
		$query = Util::prepareInsertQuery($data, $this->table);
		return ($this->connection->insert($query)) ? $this->getById($this->connection->getLastId()) : null;
	}

	/**
	 * @param int $id
	 * @return Branch|null
	 */
	public function getById(int $id): Branch|null
	{
		return $this->connection
			->select("SELECT * FROM $this->table WHERE id = $id")
			->fetch_object('App\Application\Model\Branch');
	}

	/**
	 * @return Branch[]
	 */
	public function getBranches(): array
	{
		$branches = [];
		$result = $this->connection->select("SELECT id FROM $this->table");
				
		while ($row = $result->fetch_assoc()) {
			$branches[] = $this->getById(intval($row['id']));
		}
		return $branches;
	}

	/**
	 * @param int $id
	 * @param array $data
	 * @return Branch|null
	 */
	public function edit(int $id, array $data): Branch|null
	{
		$query = Util::prepareUpdateQuery($id, $data, $this->table);
		return ($this->connection->update($query)) ? $this->getById($id) : null;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		$query = Util::prepareDeleteQuery($id, $this->table);
		return $this->connection->delete($query);
	}
}
