<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\Model\Platillo;
use App\Application\Model\Alimento;
use App\Application\DAO\HistoryDAO;
use App\Application\Helper\Util;
use \StdClass;

class HistoryController
{
    /**
     * @var HistoryDAO
     */
    private HistoryDAO $historyDAO;

    public function __construct()
    {
        $this->historyDAO = new HistoryDAO();
    }

    /**
     * @param int $branchId
     * @param string $week
     * @return StdClass
     */
    public function getSuppliedFood(int $branchId, string $week = 'this week'): StdClass
    {
        $fecha_inicio = date('Y-m-j', strtotime($week));
        $fecha_fin = date('Y-m-j 23:59:59', strtotime($fecha_inicio . " next Sunday"));
        $nombre = '';
        return $this->historyDAO->getSuppliedFood($fecha_inicio, $fecha_fin, $nombre, $branchId);
    }

    /**
     * @param int $branchId
     * @param string $week
     * @return StdClass
     */
    public function getAlteredFood(int $branchId, string $week = 'this week'): StdClass
    {
        $fecha_inicio = date('Y-m-j', strtotime($week));
        $fecha_fin = date('Y-m-j 23:59:59', strtotime($fecha_inicio . " next Sunday"));
        $nombre = '';
        return $this->historyDAO->getAlteredFood($fecha_inicio, $fecha_fin, $nombre, $branchId);
    }

    /**
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @param int $branchId
     * @return StdClass
     */
    public function getSales(?string $fechaInicio, ?string $fechaFin, int $branchId): StdClass
    {
        if (!isset($fechaInicio)) {
            $fechaInicio = date('Y-m-d');
        }
        if (!isset($fechaFin)) {
            $fechaFin = date('Y-m-d 23:59:59');
        } else {
            $fechaFin = date('Y-m-d 23:59:59', strtotime($fechaFin));
        }
        return $this->historyDAO->getSales($fechaInicio, $fechaFin, $branchId);
    }

    /**
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @param int $branchId
     * @return StdClass
     */
    public function getCourtesies(?string $fechaInicio, ?string $fechaFin, int $branchId): StdClass
    {
        if (!isset($fechaInicio)) {
            $fechaInicio = date('Y-m-d');
        }
        if (!isset($fechaFin)) {
            $fechaFin = date('Y-m-d 23:59:59');
        } else {
            $fechaFin = date('Y-m-d 23:59:59', strtotime($fechaFin));
        }
        return $this->historyDAO->getCourtesies($fechaInicio, $fechaFin, $branchId);
    }

    /**
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @param string|null $reason
     * @param int $branchId
     * @return StdClass
     */
    public function getExpenses(?string $fechaInicio, ?string $fechaFin, ?string $reason, int $branchId) : StdClass
    {
        if (!isset($fechaInicio)) {
            $fechaInicio = date('Y-m-d');
        }
        if (!isset($fechaFin)) {
            $fechaFin = date('Y-m-d 23:59:59');
        } else {
            $fechaFin = date('Y-m-d 23:59:59', strtotime($fechaFin));
        }
        if (!isset($reason)) {
            $reason = '';
        }
        return $this->historyDAO->getExpenses($fechaInicio, $fechaFin, $reason, $branchId);
    }
}