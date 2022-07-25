<?php

declare(strict_types=1);

namespace App\Application\DAO;

<<<<<<< HEAD
use App\Application\Helper\Connection;
=======
use App\Application\Helper\Conexion;
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
use \StdClass;

class HistoryDAO
{
    /**
<<<<<<< HEAD
     * @var Connection
     */
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection();
=======
     * @var Conexion
     */
    private Conexion $connection;

    public function __construct()
    {
        $this->connection = new Conexion();
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param string $name
     * @param int $branchId
     * @return StdClass
     */
    public function getSuppliedFood(string $startDate, string $endDate, string $name, int $branchId): StdClass
    {
        $suppliedFood = new StdClass();
        $result = $this->connection->select("SELECT alimentos_surtidos.idalimento_surtido, alimentos_surtidos.fecha, alimento.nombre, "
<<<<<<< HEAD
            . "alimentos_surtidos.cantidad, alimentos_surtidos.nueva_cantidad, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
=======
            . "alimentos_surtidos.cantidad, alimentos_surtidos.cantidad_actual, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
            . "FROM alimentos_surtidos, alimento, usuario "
            . "WHERE alimentos_surtidos.idusuario = usuario.idusuario AND alimentos_surtidos.idalimento = alimento.idalimento "
            . "AND alimentos_surtidos.idsucursal = '$branchId' AND alimento.nombre LIKE '%$name%'"
            . "AND alimentos_surtidos.fecha >= '$startDate' AND alimentos_surtidos.fecha <= '$endDate' ORDER BY fecha ASC");
        $suppliedFood->qty = $result->num_rows;
        while ($row = $result->fetch_array()) {
            $food = new StdClass();
            $food->id = $row[0]; // idalimento_surtido
            $food->date = $row[1]; // fecha
            $food->food = $row[2]; // alimento
            $food->quantity = $row[3]; // cantidad
            $food->newQuantity = $row[4]; // nueva cantidad
            $food->cashier = $row[5]; // nombre cajero
            $suppliedFood->suppliedFood[] = $food;
        }
        return $suppliedFood;
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param string $name
     * @param int $branchId
     * @return StdClass
     */
    public function getAlteredFood(string $startDate, string $endDate, string $name, int $branchId): StdClass
    {
        $alteredFood = new StdClass();
        $result = $this->connection->select("SELECT alimentos_alterados.idalimento_alterado, alimentos_alterados.fecha, alimento.nombre, "
<<<<<<< HEAD
            . "alimentos_alterados.cantidad, alimentos_alterados.justificacion, alimentos_alterados.nueva_cantidad, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
=======
            . "alimentos_alterados.cantidad, alimentos_alterados.justificacion, alimentos_alterados.cantidad_actual, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
            . "FROM alimentos_alterados, alimento, usuario "
            . "WHERE alimentos_alterados.idusuario = usuario.idusuario AND alimentos_alterados.idalimento = alimento.idalimento "
            . "AND alimentos_alterados.idsucursal = '$branchId' AND alimento.nombre LIKE '%$name%'"
            . "AND alimentos_alterados.fecha >= '$startDate' AND alimentos_alterados.fecha <= '$endDate' ORDER BY fecha ASC");
        $alteredFood->qty = $result->num_rows;
        while ($row = $result->fetch_array()) {
            $food = new \StdClass();
            $food->id = $row[0]; // idalimento_surtido
            $food->date = $row[1]; // fecha
            $food->food = $row[2]; // alimento
            $food->quantity = $row[3]; // cantidad
            $food->reason = $row[4]; // justificacion
            $food->newQuantity = $row[5]; // nueva cantidad
            $food->cashier = $row[6]; // nombre cajero
            $alteredFood->alteredFood[] = $food;
        }
        return $alteredFood;
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param int $branchId
     * @return StdClass
     */
    public function getSales(string $startDate, string $endDate, int $branchId): StdClass
    {
        $sales = new StdClass();

        $sales->total = $this->getSumFromTable('precio','venta', $branchId, $startDate, $endDate);
        if ($sales->total == 0) {
            $sales->sales = [];
            return $sales;
        }

        $result = $this->connection->select("SELECT venta.idventa, venta.fecha, platillo.nombre, venta.precio, "
            . "venta.cantidad, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
            . "FROM venta, platillo, usuario "
            . "WHERE venta.idusuario = usuario.idusuario AND venta.idplatillo = platillo.idplatillo "
            . "AND venta.idsucursal = '$branchId' "
            . "AND venta.fecha >= '$startDate' AND venta.fecha <= '$endDate' ORDER BY fecha DESC");
        $sales->qty = $result->num_rows;
        while ($row = $result->fetch_array()) {
            $sale = new StdClass();
            $sale->id = $row[0]; // id
            $sale->date = $row[1]; // date
            $sale->dish = $row[2]; // dish
            $sale->price = $row[3]; // price
            $sale->quantity = $row[4]; // cantidad
            $sale->cashier = $row[5]; // cajero
            $sales->sales[] = $sale;
        }
        return $sales;
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param int $branchId
     * @return StdClass
     */
    public function getCourtesies(string $startDate, string $endDate, int $branchId) : StdClass
    {
        $courtesies = new StdClass();

        $courtesies->total = $this->getSumFromTable('precio','cortesia', $branchId, $startDate, $endDate);
        if ($courtesies->total == 0) {
            $courtesies->courtesies = [];
            return $courtesies;
        }

        $result = $this->connection->select("SELECT cortesia.idcortesia, cortesia.fecha, platillo.nombre, cortesia.precio, "
            . "cortesia.cantidad, cortesia.concepto, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
            . "FROM cortesia, platillo, usuario "
            . "WHERE cortesia.idusuario = usuario.idusuario AND cortesia.idplatillo = platillo.idplatillo "
            . "AND cortesia.idsucursal = '$branchId' "
            . "AND cortesia.fecha >= '$startDate' AND cortesia.fecha <= '$endDate' ORDER BY fecha DESC");
        $courtesies->qty = $result->num_rows;
        while ($row = $result->fetch_array()) {
            $courtesy = new StdClass();
            $courtesy->id = $row[0]; // id
            $courtesy->date = $row[1]; // date
            $courtesy->dish = $row[2]; // dish
            $courtesy->price = $row[3]; // price
            $courtesy->quantity = $row[4]; // cantidad
            $courtesy->reason = $row[5]; // reason
            $courtesy->cashier = $row[6]; // cajero
            $courtesies->courtesies[] = $courtesy;
        }
        return $courtesies;
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param string $reason
     * @param int $branchId
     * @return StdClass
     */
    public function getExpenses(string $startDate, string $endDate, string $reason, int $branchId) : StdClass
    {
        $expenses = new StdClass();

        $expenses->total = $this->getSumFromTable('cantidad','gasto', $branchId, $startDate, $endDate);
        if ($expenses->total == 0) {
            $expenses->expenses = [];
            return $expenses;
        }

<<<<<<< HEAD
        $query = "SELECT gasto.id, gasto.fecha, gasto.cantidad, gasto.concepto, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre FROM gasto, usuario WHERE gasto.idusuario = usuario.id AND gasto.idsucursal = 1 AND gasto.fecha >= '2022-01-01' AND DATE(gasto.fecha) <= '2022-01-10' ORDER BY fecha DESC";

        $query = <<<EOF
            SELECT 
            gasto.id, gasto.fecha, gasto.cantidad, gasto.concepto, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre
            FROM gasto
            JOIN usuario ON gasto.idusuario = usuario.id
            WHERE gasto.idsucursal = $branchId AND gasto.fecha >= '$startDate' AND DATE(gasto.fecha) <= '$endDate' ORDER BY fecha DESC
        EOF;


        $result = $this->connection->select($query);
=======
        $result = $this->connection->select("SELECT gasto.idgasto, gasto.fecha, gasto.cantidad, "
            . "gasto.concepto, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
            . "FROM gasto, usuario "
            . "WHERE gasto.idusuario = usuario.idusuario "
            . "AND gasto.idsucursal = '$branchId' "
            . "AND gasto.concepto LIKE '%$reason%'"
            . "AND gasto.fecha >= '$startDate' AND gasto.fecha <= '$endDate' ORDER BY fecha DESC");
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
        $expenses->qty = $result->num_rows;
        while ($row = $result->fetch_array()) {
            $expense = new StdClass();
            $expense->id = $row[0]; // id
            $expense->date = $row[1]; // date
            $expense->amount = $row[2]; // cantidad
            $expense->reason = $row[3]; // reason
            $expense->cashier = $row[4]; // cajero
            $expenses->expenses[] = $expense;
        }

        return $expenses;
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
        $row = $this->connection->select("SELECT SUM({$column}) FROM {$table} "
            . "WHERE fecha >= '{$startDate}' AND fecha <= '{$endDate}' "
            . "AND idsucursal = {$branchId}")->fetch_array();
        return floatval($row[0]);
    }
}