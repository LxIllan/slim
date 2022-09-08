<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
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
     * @param string $from
     * @param string $to
     * @return StdClass
     */
    public function getSuppliedFood(int $branchId, string $from, string $to): StdClass
    {
        $suppliedFood = new StdClass();

        $result = $this->connection->select("SELECT supplied_food.id, supplied_food.date, food.name, "
            . "supplied_food.quantity, supplied_food.new_quantity, CONCAT(user.name, ' ', user.last_name) AS cashier "
            . "FROM supplied_food, food, user "
            . "WHERE supplied_food.user_id = user.id AND supplied_food.food_id = food.id "
            . "AND supplied_food.branch_id = '$branchId' "
            . "AND DATE(supplied_food.date) >= '$from' AND DATE(supplied_food.date) <= '$to' ORDER BY date ASC");

        $suppliedFood->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $suppliedFood->items[] = $row;
        }
        return $suppliedFood;
    }

    /**
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @return StdClass
     */
    public function getAlteredFood(int $branchId, string $from, string $to): StdClass
    {
        $alteredFood = new StdClass();;

        $result = $this->connection->select("SELECT altered_food.id, altered_food.date, food.name, "
            . "altered_food.quantity, altered_food.reason, altered_food.new_quantity, CONCAT(user.name, ' ' ,user.last_name) AS cashier "
            . "FROM altered_food, food, user "
            . "WHERE altered_food.user_id = user.id AND altered_food.food_id = food.id "
            . "AND altered_food.branch_id = '$branchId' "
            . "AND DATE(altered_food.date) >= '$from' AND DATE(altered_food.date) <= '$to' ORDER BY date ASC");

        $alteredFood->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $alteredFood->food[] = $row;
        }
        return $alteredFood;
    }

    /**
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @return StdClass
     */
    public function getSales(int $branchId, string $from, string $to): StdClass
    {
        $sales = new StdClass();
        $sales->amount = $this->getSumFromTable('price', 'sale', $branchId, $from, $to);

        if ($sales->amount == 0) {
            $sales->length = 0;
            $sales->sales = [];            
            return $sales;
        }

        $result = $this->connection->select("SELECT sale.id, sale.date, dish.name, sale.price, "
            . "sale.quantity, CONCAT(user.name, ' ' ,user.last_name) AS cashier "
            . "FROM sale, dish, user "
            . "WHERE sale.user_id = user.id AND sale.dish_id = dish.id "
            . "AND sale.branch_id = '$branchId' "
            . "AND DATE(sale.date) >= '$from' AND DATE(sale.date) <= '$to' ORDER BY date DESC");

        $sales->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $sales->items[] = $row;
        }
        return $sales;
    }

    /**
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @return StdClass
     */
    public function getCourtesies(int $branchId, string $from, string $to): StdClass
    {
        $courtesies = new StdClass();
        $courtesies->amount = $this->getSumFromTable('price', 'courtesy', $branchId, $from, $to);

        if ($courtesies->amount == 0) {
            $courtesies->length = 0;
            $courtesies->courtesies = [];
            return $courtesies;
        }

        $result = $this->connection->select("SELECT courtesy.id, courtesy.date, dish.name, courtesy.price, "
            . "courtesy.quantity, courtesy.reason, CONCAT(user.name, ' ' ,user.last_name) AS cashier "
            . "FROM courtesy, dish, user "
            . "WHERE courtesy.user_id = user.id AND courtesy.dish_id = dish.id "
            . "AND courtesy.branch_id = '$branchId' "
            . "AND DATE(courtesy.date) >= '$from' AND DATE(courtesy.date) <= '$to' ORDER BY date DESC");

        $courtesies->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $courtesies->items[] = $row;
        }
        return $courtesies;
    }

    /**
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @param string $reason
     * @return StdClass
     */
    public function getExpenses(int $branchId, string $from, string $to, string $reason): StdClass
    {
        $expenses = new StdClass();
        $expenses->amount = $this->getSumFromTable('amount', 'expense', $branchId, $from, $to);

        if ($expenses->amount == 0) {
            $expenses->length = 0;
            $expenses->expenses = [];
            return $expenses;
        }

        $query = <<<EOF
            SELECT expense.id, expense.date, expense.amount, expense.reason, 
                   CONCAT(user.name, ' ' ,user.last_name) AS cashier
            FROM expense
            JOIN user ON expense.user_id = user.id
            WHERE expense.branch_id = $branchId 
              AND DATE(expense.date) >= '$from' 
              AND DATE(expense.date) <= '$to' 
            ORDER BY date DESC
        EOF;

        $result = $this->connection->select($query);
        $expenses->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $expenses->items[] = $row;
        }
        return $expenses;
    }

    /**
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @return StdClass
     */
    public function getUsedProducts(int $branchId, string $from, string $to): StdClass
    {
        $usedProducts = new StdClass();
        $query = <<<EOF
            SELECT used_product.id, used_product.date, product.name, 
                used_product.quantity, CONCAT(user.name, ' ' , user.last_name) AS cashier 
            FROM used_product, product, user 
            WHERE used_product.user_id = user.id AND used_product.product_id = product.id 
                AND used_product.branch_id = $branchId
                AND DATE(used_product.date) >= '$from' AND DATE(used_product.date) <= '$to' ORDER BY date DESC
        EOF;

        $result = $this->connection->select($query);
        $usedProducts->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $usedProducts->items[] = $row;
        }
        return $usedProducts;
    }

    /**
     * @param int $branchId
     * @param string|null $from
     * @param string|null $to
     * @return StdClass
     */
    public function getFoodsSold(int $branchId, ?string $from, ?string $to): StdClass
    {
        $foodController = new \App\Application\Controllers\FoodController();
        $foods = $foodController->getFoodByBranch($branchId);

        $foodsSold = new StdClass();
        foreach ($foods as $food) {
            $foodsSold->items[] = $this->getFoodSold(intval($food->id), $food->name, $from, $to);
        }

        return $foodsSold;
    }

    public function  getFoodSold(int $foodId, string $name, string $from, string $to): StdClass
    {
        $dishController = new \App\Application\Controllers\DishController();

        $dishes = $dishController->getDishesByFood($foodId);

        $foodSold = new StdClass();
        $foodSold->name = $name;
        $foodSold->quantity = 0;
        $sumFood = 0;

        foreach ($dishes as $dish) {
            $dishId = $dish->id;
            $query = <<<EOF
                SELECT SUM(quantity) AS quantity
                FROM sale
                WHERE dish_id = $dishId
                AND DATE(date) >= '$from' AND DATE(date) <= '$to'
            EOF;

            $result = $this->connection->select($query);
            $row = $result->fetch_assoc();
            if (is_null($row['quantity'])) {
                continue;
            }
            $foodSold->dishes[] = [
                'name' => $dish->name,
                'quantity' => $row['quantity']
            ];
            $sumFood += $dish->portion * $row['quantity'];
        }
        $foodSold->quantity = $sumFood;
        file_put_contents(__DIR__ . "/../../../logs/system.log", date("[D, d M Y H:i:s]") . " " .
            '$dish->foodId-> ' . json_encode($foodId) . " " .
            '$dish->quantity-> ' . json_encode($sumFood) . " " .
            "file:" . __DIR__ . '/' . basename(__FILE__) . "\r\n", FILE_APPEND);

        return $foodSold;
    }

    /**
     * @param string $column
     * @param string $table
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @return float
     */
    private function getSumFromTable(string $column, string $table, int $branchId, string $from, string $to): float
    {
        $query = <<<EOF
            SELECT SUM($column) 
            FROM $table 
            WHERE DATE(date) >= '$from' 
              AND DATE(date) <= '$to' 
              AND branch_id = $branchId
        EOF;
        $row = $this->connection->select($query)->fetch_array();
        return floatval($row[0]);
    }
}
