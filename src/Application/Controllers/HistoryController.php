<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\DAO\HistoryDAO;
use App\Application\Helpers\Util;
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
     * @param string|null $from
     * @param string|null $to
     * @return StdClass
     */
    public function getSuppliedFood(int $branchId, ?string $from, ?string $to): StdClass
    {
        if ((is_null($from)) && (is_null($to))) {
            $from = date('Y-m-d', strtotime("this week"));
            $to = date('Y-m-d', strtotime($from . "next Sunday"));
        }
        return $this->historyDAO->getSuppliedFood($branchId, $from, $to);
    }

    /**
     * @param int $branchId
     * @param string|null $from
     * @param string|null $to
     * @return StdClass
     */
    public function getAlteredFood(int $branchId, ?string $from, ?string $to): StdClass
    {
        if ((is_null($from)) && (is_null($to))) {
            $from = date('Y-m-d', strtotime("this week"));
            $to = date('Y-m-d', strtotime($from . "next Sunday"));
        }
        return $this->historyDAO->getAlteredFood($branchId, $from, $to);
    }

    /**
     * @param int $branchId
     * @param string|null $from
     * @param string|null $to
     * @return StdClass
     */
    public function getSales(int $branchId, ?string $from, ?string $to): StdClass
    {
        if ((is_null($from)) && (is_null($to))) {
            $from = date('Y-m-d');
            $to = date('Y-m-d');
        }
        return $this->historyDAO->getSales($branchId, $from, $to);
    }

    /**
     * @param int $branchId
     * @param string|null $from
     * @param string|null $to
     * @return StdClass
     */
    public function getCourtesies(int $branchId, ?string $from, ?string $to): StdClass
    {
        if ((is_null($from)) && (is_null($to))) {
            $from = date('Y-m-d');
            $to = date('Y-m-d');
        }
        return $this->historyDAO->getCourtesies($branchId, $from, $to);
    }    

    /**
     * @param int $branchId
     * @param string|null $from
     * @param string|null $to
     * @return StdClass
     */
    public function getUsedProducts(int $branchId, ?string $from, ?string $to): StdClass
    {
        if ((is_null($from)) && (is_null($to))) {
            $from = date('Y-m-d', strtotime("this week"));
            $to = date('Y-m-d', strtotime($from . "next Sunday"));
        }
        return $this->historyDAO->getUsedProducts($branchId, $from, $to);
    }

    /**
     * @param int $branchId
     * @param string|null $from
     * @param string|null $to
     * @return StdClass
     */
    public function getFoodsSold(int $branchId, ?string $from, ?string $to): StdClass
    {
        if ((is_null($from)) && (is_null($to))) {
            $from = date('Y-m-d');
            $to = date('Y-m-d');
        }
        return $this->historyDAO->getFoodsSold($branchId, $from, $to);
    }

    /**
     * @param int $branchId
     * @param string $from
     * @param string $to     
     * @return StdClass
     */
    public function getTickets(int $branchId, string $from, string $to): StdClass
    {
        if ((is_null($from)) && (is_null($to))) {
            $from = date('Y-m-d');
            $to = date('Y-m-d');
        }
        return $this->historyDAO->getTickets($branchId, $from, $to);
    }
}
