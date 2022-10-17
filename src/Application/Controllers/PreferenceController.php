<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\DAO\PreferenceDAO;
use App\Application\Model\Preference;

class PreferenceController
{
	/**
	 * @var PreferenceDAO $preferenceDAO
	 */
	private PreferenceDAO $preferenceDAO;

	public function __construct()
	{
		$this->preferenceDAO = new PreferenceDAO();
	}

	/**
	 * @param array $data
	 * @return Preference|null
	 */
	public function create(array $data): Preference|null
	{
		return $this->preferenceDAO->create($data);
	}

	/**
	 * @param int $id
	 * @return Preference|null
	 */
	public function getById(int $id): Preference|null
	{
		return $this->preferenceDAO->getById($id);
	}

	/**
	 * @param string $key
	 * @param int $branchId
	 * @return Preference
	 */
	public function getByKey(string $key, int $branchId): Preference
	{
		return $this->preferenceDAO->getByKey($key, $branchId);
	}

	/**
	 * @param int $branchId
	 * @return Preference[]
	 */
	public function getAll(int $branchId): array
	{
		return $this->preferenceDAO->getAll($branchId);
	}

	/**
	 * @param int $id
	 * @param array $data
	 * @return Preference|null
	 */
	public function edit(int $id, array $data): Preference|null
	{
		return $this->preferenceDAO->edit($id, $data);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		return $this->preferenceDAO->delete($id);
	}
}
