<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;

class AuthDAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'user';

	/**
	 * @var Connection $connection
	 */
	private Connection $connection;

	public function __construct()
	{
		$this->connection = new Connection();
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @return array|null
	 */
	public function authenticate(string $email, string $password): array|null
	{
		$query = <<<SQL
			SELECT id, branch_id, hash, root
			FROM $this->table
			WHERE email LIKE '$email' 
				AND email = '$email' 
				AND is_deleted = 0
		SQL;

		$result = $this->connection->select($query);
		if ($result->num_rows == 1) {
			$data = $result->fetch_assoc();
			if (password_verify($password, $data['hash'])) {
				unset($data['hash']);
				return $data;
			}
			return null;
		}
		return null;
	}
}
