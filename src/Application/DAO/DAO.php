<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Helpers\Util;
use StdClass;

class DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = '';

	/**
	 * @var Connection $connection
	 */
	protected Connection $connection;

	public function __construct()
	{
		$this->connection = new Connection();
	}

	/**
	 * @param array $data
	 * @return StdClass|null
	 */
	public function create(array $data): StdClass|null
	{
		$query = Util::prepareInsertQuery($data, $this->table);
		return ($this->connection->insert($query)) ? $this->getById($this->connection->getLastId()) : null;
	}

	/**
	 * @param int $id
	 * @param array $columns
	 * @return StdClass|null
	 */
	public function getById(int $id, array $columns = []): StdClass|null
	{
		$columns = (empty($columns)) ? [] : array_merge(['id'], $columns);
		$columns = (empty($columns)) ? '*' : implode(',', $columns);
		return $this->connection
			->select("SELECT $columns FROM $this->table WHERE id = $id")
			->fetch_object('App\Application\Model\\' . ucfirst($this->table));
	}    

	/**
	 * @param int $id
	 * @param array $data
	 * @return StdClass|null
	 */
	public function edit(int $id, array $data): StdClass|null
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
		if (Util::existColumn($this->table, 'is_deleted')) {
			$data = [
				'is_deleted' => 1,
				'deleted_at' => date('Y-m-d H:i:s')
			];
			$query = Util::prepareUpdateQuery($id, $data, $this->table);
			return $this->connection->update($query);
		} else {
			$query = Util::prepareDeleteQuery($id, $this->table);
			return $this->connection->delete($query);
		}
	}
}
