<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\DAO\SucursalDAO;

class SucursalController
{
    private $_sucursalDAO;

    public function __construct()
    {
        $this->_sucursalDAO = new SucursalDAO();
    }

    public function getNumTicket(int $idSucursal): int {            
        return $this->_sucursalDAO->getNumTicket($idSucursal);
    }                

    public function deleteHistoryMonthly(): bool {
        return $this->_sucursalDAO->deleteHistoryMonthly();
    }

    public function getBranch(int $id) 
    {
        return $this->_sucursalDAO->getBranch($id);
    }

    public function getBranches() 
    {
        return $this->_sucursalDAO->getBranches();
    }

    public function getDataFromAllBranches(string $column) : array {
        return $this->_sucursalDAO->getDataFromAllBranches($column);
    }

    public function getData(int $idSucursal, string $column) : string {
        return $this->_sucursalDAO->getData($idSucursal, $column);
    }

    public function updateData(int $idSucursal, string $column, string $newData) : bool {
        return $this->_sucursalDAO->updateData($idSucursal, $column, $newData);
    }
}
