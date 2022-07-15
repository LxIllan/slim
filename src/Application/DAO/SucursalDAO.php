<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Conexion;
use App\Application\Model\Branch;

class SucursalDAO
{
    private $conexion;
    private const TABLE_NAME = 'sucursal';

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    public function getDataFromAllBranches(string $column)
    {
        $sucursales = [];
        $result = $this->conexion->select("SELECT idsucursal, $column FROM sucursal");
        while ($tupla = $result->fetch_array()) {
            $sucursales[$tupla[0]] = $tupla[1]; 
        }
        return $sucursales;
    }

    /**
     * @param int $id
     * @return Branch
     */
    public function getBranch(int $id): Branch
    {
        return $this->conexion->select("SELECT * FROM sucursal WHERE idsucursal = $id")->fetch_object('App\Application\Model\Branch');
    }

    /**
     * @return Branch[]
     */
    public function getBranches(): array
    {
        $branches = [];
        $result = $this->conexion->select("SELECT idsucursal FROM sucursal");
                
        while ($row = $result->fetch_assoc()) {
            $branches[] = $this->getBranch(intval($row['idsucursal']));
        }
        return $branches;
    }

    public function getNumTicket(int $idSucursal): int {
        $num_ticket = $this->conexion->select("SELECT num_ticket FROM sucursal WHERE idsucursal = $idSucursal")->fetch_array()[0];            
        $this->conexion->update("UPDATE sucursal SET num_ticket = " . ($num_ticket + 1) . " WHERE idsucursal = $idSucursal");
        return $num_ticket;
    }            

    public function getData(int $idSucursal, string $column) : string {
        $data = 'null';            
        if ($this->existsColumn(self::TABLE_NAME, $column)) {
            $data = $this->conexion->select("SELECT $column FROM sucursal WHERE idsucursal = $idSucursal")->fetch_array()[0];
        }
        return $data;
    }

    public function updateData(int $idSucursal, string $column, string $newData) : bool {
        if ($this->existsColumn(self::TABLE_NAME, $column)) {
            return $this->conexion->update("UPDATE sucursal SET $column = '$newData' WHERE idsucursal = $idSucursal");
        }
        return false;
    }

    /**
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function existsColumn(string $table, string $column) : bool
    {
        $result = $this->conexion->select("SHOW COLUMNS FROM $table LIKE '{$column}'");
        return (bool) $result->num_rows;
    }

    public function deleteHistoryMonthly(): bool {
        $mesAnterior = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d'))));
        $diaDelMes = intval(date('d'));
        $tablesToDelete = ['cortesia', 'venta', 'producto_usado', 'gasto', 'alimentos_surtidos', 'alimentos_alterados', 'alimentos_vendidos', 'platillos_vendidos'];
        echo "Date $mesAnterior\n";
        foreach ($tablesToDelete as $table) {
            echo "Deleting table $table...\n";
            $this->conexion->delete("DELETE FROM $table WHERE fecha <= '$mesAnterior'");
            echo "Deleted table $table... successfully\n";
            self::reorderIds($table);
        }
        return true;
    }

    private function reorderIds(string $tableName): bool {
        $columnsNames = [];    
        $queryGetAllData = 'SELECT ';
        $queryInsertValues = 'INSERT INTO ' . $tableName . '(';

        $result = $this->conexion->select("SHOW COLUMNS FROM $tableName");
        
        $i = 0;
        while ($tupla = $result->fetch_array()) {
            /* skip primary key */
            if ($i == 0) {
                $i++;
                continue;
            }
            array_push($columnsNames, $tupla['Field']);
            $queryGetAllData .= $tupla['Field'] . ',';
            $queryInsertValues .= $tupla['Field'] . ',';
            $i++;
        }

        $queryGetAllData = rtrim($queryGetAllData, ',');
        $queryGetAllData .= ' FROM ' . $tableName;

        $queryInsertValues = rtrim($queryInsertValues, ',');
        $queryInsertValues .= ') VALUES ';

        $data = [];

        $result = $this->conexion->select($queryGetAllData);
        while ($tupla = $result->fetch_array(MYSQLI_NUM)) {
            array_push($data, $tupla);
        }

        foreach ($data as $row) {
            $queryInsertValues .= '(';
            foreach ($row as $item) {
                $queryInsertValues .= "'$item',";
            }
            $queryInsertValues = rtrim($queryInsertValues, ',');
            $queryInsertValues .= '),';
        }
        $queryInsertValues = rtrim($queryInsertValues, ',');
        
        $this->conexion->update("TRUNCATE TABLE $tableName");
        $this->conexion->insert($queryInsertValues);
        return true;
    }

}