<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Model\User;
use App\Application\Helpers\Util;

class UserDAO extends DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'user';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param int $branchId
	 * @param bool $getDeleted
	 * @return User[]
	 */
	public function getAll(int $branchId, bool $getDeleted): array
	{
		$users = [];
		$query = <<<SQL
			SELECT id
			FROM user
			WHERE branch_id = $branchId OR root = 1
				AND is_deleted = '$getDeleted'
		SQL;

		$result = $this->connection->select($query);
		while ($row = $result->fetch_array()) {
			$users[] = $this->getById(intval($row['id']));
		}

		return $users;
	}

	/**
	 * @param int $userId
	 * @param string $password
	 * @return User|null
	 */
	public function resetPassword(int $userId, string $password): User|null
	{
		$data = [];
		$data["hash"] = password_hash($password, PASSWORD_DEFAULT);
		return $this->edit($userId, $data);
	}

	/**
	 * @param string $email
	 * @return int
	 */
	public function existEmail(string $email): int
	{
		$row = $this->connection->select("SELECT id, email FROM user WHERE email = '$email'")->fetch_assoc();
		return (isset($row) && Util::validateEmail($row['email'])) ? intval($row['id']) : 0;
	}
}
