<?php

declare(strict_types=1);

namespace App\Application\Controller;

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
     * @param int $id
     * @return Branch
     */
    public function getById(int $id): Branch
    {
        return $this->branchDAO->getById($id);
    }

    /**
     * @return Branch[]
     */
    public function getBranches(): array
    {
        return $this->branchDAO->getBranches();
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
     * @param int $branchId
     * @return int
     */
    public function getNumTicket(int $branchId): int
    {
        return $this->branchDAO->getNumTicket($branchId);
    }
}
