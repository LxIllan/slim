<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\DAO\BranchDAO;
use App\Application\Model\Branch;

class BranchController
{
	/**
	 * @var BranchDAO $branchDAO
	 */
	private BranchDAO $branchDAO;

	public function __construct()
	{
		$this->branchDAO = new BranchDAO();
	}

	/**
	 * @param array $data
	 * @return Branch|null
	 */
	public function create(array $data): Branch|null
	{
		return $this->branchDAO->create($data);
	}

	/**
	 * @param int $id
	 * @param array $columns
	 * @return Branch|null
	 */
	public function getById(int $id, array $columns = []): Branch|null
	{
		return $this->branchDAO->getById($id, $columns);
	}

	/**
	 * @return Branch[]
	 */
	public function getBranches(): array
	{
		return $this->branchDAO->getAll();
	}

	/**
	 * @param int $id
	 * @param array $data
	 * @return Branch|null
	 */
	public function edit(int $id, array $data): Branch|null
	{
		return $this->branchDAO->edit($id, $data);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		return $this->branchDAO->delete($id);
	}
}
