<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\Model\Platillo;
use App\Application\Model\Alimento;
use App\Application\DAO\HistoryDAO;
use App\Application\Helper\Util;
use \StdClass;
use function _PHPStan_9a6ded56a\RingCentral\Psr7\str;

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
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getSuppliedFood(int $branchId, ?string $startDate, ?string $endDate): array
    {
        if ((is_null($startDate)) && (is_null($endDate))) {
            $startDate = date('Y-m-d', strtotime("this week"));
            $endDate = date('Y-m-d', strtotime($startDate . "next Sunday"));
        }
        return $this->historyDAO->getSuppliedFood($branchId, $startDate, $endDate);
    }

    /**
     * @param int $branchId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getAlteredFood(int $branchId, ?string $startDate, ?string $endDate): array
    {
        if ((is_null($startDate)) && (is_null($endDate))) {
            $startDate = date('Y-m-d', strtotime("this week"));
            $endDate = date('Y-m-d', strtotime($startDate . "next Sunday"));
        }
        return $this->historyDAO->getAlteredFood($branchId, $startDate, $endDate);
    }

    /**
     * @param int $branchId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return StdClass
     */
    public function getSales(int $branchId, ?string $startDate, ?string $endDate): StdClass
    {
        if ((is_null($startDate)) && (is_null($endDate))) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        }
        return $this->historyDAO->getSales($branchId, $startDate, $endDate);
    }

    /**
     * @param int $branchId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return StdClass
     */
    public function getCourtesies(int $branchId, ?string $startDate, ?string $endDate): StdClass
    {
        if ((is_null($startDate)) && (is_null($endDate))) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        }
        return $this->historyDAO->getCourtesies($branchId, $startDate, $endDate);
    }

    /**
     * @param int $branchId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $reason
     * @return StdClass
     */
    public function getExpenses(int $branchId, ?string $startDate, ?string $endDate, ?string $reason): StdClass
    {
        if ((is_null($startDate)) && (is_null($endDate))) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        }
        if (!isset($reason)) {
            $reason = '';
        }
        return $this->historyDAO->getExpenses($branchId, $startDate, $endDate, $reason);
    }

    /**
     * @param int $branchId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getUsedProducts(int $branchId, ?string $startDate, ?string $endDate): array
    {
        if ((is_null($startDate)) && (is_null($endDate))) {
            $startDate = date('Y-m-d', strtotime("this week"));
            $endDate = date('Y-m-d', strtotime($startDate . "next Sunday"));
        }
        return $this->historyDAO->getUsedProducts($branchId, $startDate, $endDate);
    }
}