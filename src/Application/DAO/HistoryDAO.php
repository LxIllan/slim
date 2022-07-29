<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Connection;
use StdClass;

class HistoryDAO
{
    /**
     * @var Connection
     */
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection();
    }

    /**
     * @param int $branchId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSuppliedFood(int $branchId, string $startDate, string $endDate): array
    {
        $suppliedFood = [];

        $result = $this->connection->select("SELECT supplied_food.id, supplied_food.date, food.name, "
            . "supplied_food.quantity, supplied_food.new_quantity, CONCAT(user.name, ' ' ,user.last_name) AS cashier "
            . "FROM supplied_food, food, user "
            . "WHERE supplied_food.user_id = user.id AND supplied_food.food_id = food.id "
            . "AND supplied_food.branch_id = '$branchId' "
            . "AND DATE(supplied_food.date) >= '$startDate' AND DATE(supplied_food.date) <= '$endDate' ORDER BY date ASC");

        while ($row = $result->fetch_assoc()) {
            $suppliedFood[] = $row;
        }
        return $suppliedFood;
    }

    /**
     * @param int $branchId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAlteredFood(int $branchId, string $startDate, string $endDate): array
    {
        $alteredFood = [];

        $result = $this->connection->select("SELECT altered_food.id, altered_food.date, food.name, "
            . "altered_food.quantity, altered_food.reason, altered_food.new_quantity, CONCAT(user.name, ' ' ,user.last_name) AS cashier "
            . "FROM altered_food, food, user "
            . "WHERE altered_food.user_id = user.id AND altered_food.food_id = food.id "
            . "AND altered_food.branch_id = '$branchId' "
            . "AND DATE(altered_food.date) >= '$startDate' AND DATE(altered_food.date) <= '$endDate' ORDER BY date ASC");

        while ($row = $result->fetch_assoc()) {
            $alteredFood[] = $row;
        }
        return $alteredFood;
    }

    /**
     * @param int $branchId
     * @param string $startDate
     * @param string $endDate
     * @return StdClass
     */
    public function getSales(int $branchId, string $startDate, string $endDate): StdClass
    {
        $sales = new StdClass();
        $sales->amount = $this->getSumFromTable('price', 'sale', $branchId, $startDate, $endDate);

        if ($sales->amount == 0) {
            $sales->sales = [];
            return $sales;
        }

        $result = $this->connection->select("SELECT sale.id, sale.date, dish.name, sale.price, "
            . "sale.quantity, CONCAT(user.name, ' ' ,user.last_name) AS cashier "
            . "FROM sale, dish, user "
            . "WHERE sale.user_id = user.id AND sale.dish_id = dish.id "
            . "AND sale.branch_id = '$branchId' "
            . "AND DATE(sale.date) >= '$startDate' AND DATE(sale.date) <= '$endDate' ORDER BY date DESC");

        $sales->qty = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $sales->sales[] = $row;
        }
        return $sales;
    }

    /**
     * @param int $branchId
     * @param string $startDate
     * @param string $endDate
     * @return StdClass
     */
    public function getCourtesies(int $branchId, string $startDate, string $endDate): StdClass
    {
        $courtesies = new StdClass();
        $courtesies->amount = $this->getSumFromTable('price', 'courtesy', $branchId, $startDate, $endDate);

        if ($courtesies->amount == 0) {
            $courtesies->courtesies = [];
            return $courtesies;
        }

        $result = $this->connection->select("SELECT courtesy.id, courtesy.date, dish.name, courtesy.price, "
            . "courtesy.quantity, courtesy.reason, CONCAT(user.name, ' ' ,user.last_name) AS cashier "
            . "FROM courtesy, dish, user "
            . "WHERE courtesy.user_id = user.id AND courtesy.dish_id = dish.id "
            . "AND courtesy.branch_id = '$branchId' "
            . "AND DATE(courtesy.date) >= '$startDate' AND DATE(courtesy.date) <= '$endDate' ORDER BY date DESC");

        $courtesies->qty = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $courtesies->courtesies[] = $row;
        }
        return $courtesies;
    }

    /**
     * @param int $branchId
     * @param string $startDate
     * @param string $endDate
     * @param string $reason
     * @return StdClass
     */
    public function getExpenses(int $branchId, string $startDate, string $endDate, string $reason): StdClass
    {
        $expenses = new StdClass();
        $expenses->amount = $this->getSumFromTable('amount', 'expense', $branchId, $startDate, $endDate);

        if ($expenses->amount == 0) {
            $expenses->expenses = [];
            return $expenses;
        }

        $query = <<<EOF
            SELECT expense.id, expense.date, expense.amount, expense.reason, 
                   CONCAT(user.name, ' ' ,user.last_name) AS cashier
            FROM expense
            JOIN user ON expense.user_id = user.id
            WHERE expense.branch_id = $branchId 
              AND DATE(expense.date) >= '$startDate' 
              AND DATE(expense.date) <= '$endDate' 
            ORDER BY date DESC
        EOF;

        $result = $this->connection->select($query);
        $expenses->qty = $result->num_rows;

        while ($row = $result->fetch_assoc()) {
            $expenses->expenses[] = $row;
        }
        return $expenses;
    }

    /**
     * @param int $branchId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getUsedProducts(int $branchId, string $startDate, string $endDate): array
    {
        $usedProducts = [];
        $query = <<<EOF
            SELECT used_product.id, used_product.date, product.name, 
                used_product.quantity, CONCAT(user.name, ' ' , user.last_name) AS cashier 
            FROM used_product, product, user 
            WHERE used_product.user_id = user.id AND used_product.product_id = product.id 
                AND used_product.branch_id = $branchId
                AND DATE(used_product.date) >= '$startDate' AND DATE(used_product.date) <= '$endDate' ORDER BY date DESC
        EOF;

        $result = $this->connection->select($query);

        while ($row = $result->fetch_assoc()) {
            $usedProducts[] = $row;
        }
        return $usedProducts;
    }

    /**
     * @param string $column
     * @param string $table
     * @param int $branchId
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    private function getSumFromTable(string $column, string $table, int $branchId, string $startDate, string $endDate): float
    {
        $query = <<<EOF
            SELECT SUM($column) 
            FROM $table 
            WHERE DATE(date) >= '$startDate' 
              AND DATE(date) <= '$endDate' 
              AND branch_id = $branchId
        EOF;
        $row = $this->connection->select($query)->fetch_array();
        return floatval($row[0]);
    }
}
