<?php

declare(strict_types=1);

namespace App\Application\DAO;

use ReallySimpleJWT\Token;
use App\Application\Helpers\Connection;
use Exception;

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
	 * @return string|null
	 */
	public function authenticate(string $email, string $password): string|null
	{
		$query = <<<SQL
			SELECT id, branch_id, hash, root
			FROM $this->table
			WHERE email LIKE '$email'
				AND email = '$email'
				AND is_deleted = 0
		SQL;

		$result = $this->connection->select($query);

		if ($result->num_rows != 1) {
			return null;
		}

		$data = $result->fetch_assoc();
		if (!password_verify($password, $data['hash'])) {
			return null;
		}

		$payload = [
			'iat' => time(),
			'exp' => time() + 99999999,
			'user_id' => intval($data['id']),
			'branch_id' => intval($data['branch_id']),
			'root' => boolval($data['root'])
		];
		$secret = $_ENV["JWT_SECRET"];
		$token = Token::customPayload($payload, $secret);

		return $token;
	}

	/**
	 * @param array $jwt
	 * @param int $branchId
	 * @return string
	 */
	public function switchBranch(array $jwt, int $branchId): string
	{
		$branchDAO = new BranchDAO();

		if (!$branchDAO->exists($branchId)) {
			throw new Exception("Branch does not exist.");
		}

		$payload = [
			'iat' => $jwt['iat'],
			'exp' => $jwt['exp'],
			'user_id' => $jwt['user_id'],
			'branch_id' => $branchId,
			'root' => $jwt['root']
		];

		$secret = $_ENV["JWT_SECRET"];
		$token = Token::customPayload($payload, $secret);

		return $token;
	}
}
