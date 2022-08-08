<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Connection;
use App\Application\Helper\Util;
use App\Application\Model\Food;

class FoodDAO
{
    private const TABLE_NAME = 'food';

    /**
     * @var Connection $connection
     */
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection();
    }

    /**
     * @param array $data
     * @return Food|null
     */
    public function createFood(array $data): Food|null
    {
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->insert($query)) ? $this->getFoodById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Food
     */
    public function getFoodById(int $id): Food
    {
        return $this->connection->select("SELECT * FROM food WHERE id = $id")->fetch_object('App\Application\Model\Food');
    }

    /**
     * @param int $branchId
     * @return Food[]
     */
    public function getFoodByBranch(int $branchId): array
    {
        $food = [];
        $result = $this->connection->select("SELECT id FROM food WHERE branch_id = $branchId "
            . "AND category_id <= " . Util::ID_EXTRAS . " ORDER BY name");
        while ($row = $result->fetch_array()) {
            $food[] = self::getFoodById(intval($row['id']));
        }
        return $food;
    }

    /**
     * @param int $idSucursal
     * @return Food[]
     */
    public function getFoodToDashboard(int $branchId): array
    {
        $food = [];
        $result = $this->connection->select("SELECT id FROM food WHERE branch_id = $branchId "
            . "AND is_showed_in_index = 1 ORDER BY name");
        while ($row = $result->fetch_array()) {
            $food[] = $this->getFoodById(intval($row['id']));
        }
        return $food;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Food|null
     */
    public function editFood(int $id, array $data): Food|null
    {
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getFoodById($id) : null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteFood(int $id): bool
    {
        $query = Util::prepareDeleteQuery($id, self::TABLE_NAME);
        return $this->connection->delete($query);
    }

    /**
     * @param int $foodId
     * @param float $quantity
     * @param int $userId
     * @param int $branchId
     * @return Food
     */
    public function supply(int $foodId, float $quantity, int $userId, int $branchId): Food
    {
        $food = $this->getFoodById($foodId);
        $newQuantity = $food->quantity + $quantity;
        $cost = $food->cost * $quantity;

        $dataToInsert = [
            "food_id" => $foodId,
            "quantity" => $quantity,
            "new_quantity" => $newQuantity,
            "cost" => $cost,
            "user_id" => $userId,
            "branch_id" => $branchId
        ];

        $this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'supplied_food'));

        $dataToUpdate = [
            "quantity" => $newQuantity
        ];
        return $this->editFood($foodId, $dataToUpdate);
    }

    /**
     * @param int $foodId
     * @param float $quantity
     * @param string $reason
     * @param int $userId
     * @param int $branchId
     * @return Food
     */
    public function alter(int $foodId, float $quantity, string $reason, int $userId, int $branchId): Food
    {
        $food = $this->getFoodById($foodId);
        $newQuantity = $food->quantity + $quantity;
        $cost = $food->cost * $quantity;

        $dataToInsert = [
            "food_id" => $foodId,
            "quantity" => $quantity,
            "reason" => $reason,
            "new_quantity" => $newQuantity,
            "cost" => $cost,
            "user_id" => $userId,
            "branch_id" => $branchId
        ];

        $this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'altered_food'));

        $dataToUpdate = [
            "quantity" => $newQuantity
        ];
        return $this->editFood($foodId, $dataToUpdate);
    }

    private function descontarAlimento(int $idAlimento, float $cantidad): bool {
        $alimento = self::getFoodById($idAlimento);
        $existencias = $alimento->getCantidad();
        $nuevaCantidad = ($existencias - $cantidad);
        return $this->connection->update("UPDATE alimento SET cantidad = $nuevaCantidad WHERE idalimento = $idAlimento");
    }

    public function regalarPlatillo(int $idPlatillo, int $cantidad, string $concepto, int $idCajero, int $idSucursal)
    {
        $platillo = self::getDishById($idPlatillo);
        $porcion = $platillo->getPorcion();
        $precio = $platillo->getPrecio();
        $total = $precio * $cantidad;
        return $this->connection->insert(
                "INSERT INTO cortesia(idplatillo, cantidad, precio, fecha, concepto, idusuario, idsucursal) VALUES ("
                    . $platillo->getIdPlatillo() . ", "
                    . $cantidad . ", "
                    . $total . ", '"
                    . date('Y-m-d H:i:s') . "', '"
                    . $concepto . "', "
                    . $idCajero . ", "
                    . $idSucursal . ")"
                    );
    }

    public function venderPlatillo(int $idPlatillo, int $cantidad, int $idCajero, int $idSucursal)
    {
        $dineroDAO = new DineroDAO();
        $platillo = self::getDishById($idPlatillo);
        $porcion = $platillo->getPorcion();
        $precio = $platillo->getPrecio();
        $total = $precio * $cantidad;
        return $this->connection->insert(
                "INSERT INTO venta(idplatillo, cantidad, precio, fecha, idusuario, idsucursal) VALUES ("
                    . $platillo->getIdPlatillo() . ", "
                    . $cantidad . ", "
                    . $total . ", '"
                    . date('Y-m-d H:i:s') . "', "
                    . $idCajero .", "
                    . $idSucursal . ")"
                    );
    } 


    public function cancelarVenta(int $idVenta, bool $esVenta = true)
    {
        if ($esVenta) {
            $fecha = $this->connection->select("SELECT fecha FROM venta WHERE idventa = $idVenta")->fetch_array()[0];
            $result = $this->connection->select("SELECT idplatillo, cantidad, precio, idsucursal, idventa FROM venta WHERE fecha = '$fecha'");
        } else {
            $fecha = $this->connection->select("SELECT fecha FROM cortesia WHERE idcortesia = $idVenta")->fetch_array()[0];
            $result = $this->connection->select("SELECT idplatillo, cantidad, precio, idsucursal, idcortesia FROM cortesia WHERE fecha = '$fecha'");
        }            

        while ($tupla = $result->fetch_array()) {
            $idPlatillo = $tupla[0];
            $cantidadVendida = $tupla[1];
            $idVenta = $tupla[4];
            $platillo = self::getDishById($idPlatillo);
            if ($platillo->getPaquete() == 1) {
                $paquetesVendidos = $platillo->getCantidadVendida();
                $this->connection->update("UPDATE platillo SET cantidad_vendida = " . ($paquetesVendidos - $cantidadVendida) . " WHERE idplatillo = " . $platillo->getIdPlatillo());
                $platillos_paquete = self::damePlatillosDePaquete($platillo->getIdPlatillo());
                foreach ($platillos_paquete as $platilloPaquete) {
                    self::cancelarPlatillo($platilloPaquete->getIdPlatillo(), $cantidadVendida, true);
                }
            } else {
                self::cancelarPlatillo($idPlatillo, $cantidadVendida, false);
            }
            if ($esVenta) {
                $this->connection->delete("DELETE FROM venta WHERE idventa = $idVenta");
            } else {
                $this->connection->delete("DELETE FROM cortesia WHERE idcortesia = $idVenta");
            }                
        }
        return true;
    }

    private function cancelarPlatillo(int $idPlatillo, int $cantidadVendida, bool $esPaquete)
    {
        $idAlimento = $this->connection->select("SELECT idalimento FROM platillo WHERE idplatillo = $idPlatillo")->fetch_array()[0];
        $porcion = $this->connection->select("SELECT porcion FROM platillo WHERE idplatillo = $idPlatillo")->fetch_array()[0];
        
        $cantidadActual = $this->connection->select("SELECT cantidad FROM alimento WHERE idalimento = $idAlimento")->fetch_array()[0];
        $nuevaCantidad = $cantidadActual + ($porcion * $cantidadVendida);            
        $this->connection->update("UPDATE alimento SET cantidad = $nuevaCantidad WHERE idalimento = $idAlimento");
        
        $alimentosVendidos = $this->connection->select("SELECT cantidad_vendida FROM alimento WHERE idalimento = $idAlimento")->fetch_array()[0];
        $this->connection->update("UPDATE alimento SET cantidad_vendida = " . ($alimentosVendidos - ($porcion * $cantidadVendida)) . " WHERE idalimento = " . $idAlimento);

        if (!$esPaquete) {
            $platillosVendidos = $this->connection->select("SELECT cantidad_vendida FROM platillo WHERE idplatillo = $idPlatillo")->fetch_array()[0];
            $this->connection->update("UPDATE platillo SET cantidad_vendida = " . ($platillosVendidos - $cantidadVendida) . " WHERE idplatillo = " . $idPlatillo);
        }            
    }    

    public function createReportPlatillos(int $idSucursal) {
        
        date_default_timezone_set('America/Mexico_City');

        $folder_name = dirname(__FILE__) . '/weekly-reports';            

        $sucursalDAO = new BranchDAO();
        $nombreSucursal = $sucursalDAO->getNombre($idSucursal);

        /*
            $fecha_inicio, get date of past Monday
        */
        $fecha_inicio = date('Y-m-d', strtotime("this week"));
        // $fecha_inicio = date('Y-m-d', strtotime('-1 week', strtotime($fecha_inicio)));
        /*
            $today, today is Sunday
        */
        $today = date('Y-m-d');
        // $today = date('Y-m-d', strtotime('-1 day', strtotime($today)));

        /* 
        * Create folder weekly-reports if not exist.
        */
        $dir_name = $folder_name;
        if (!is_dir($dir_name)) {
            mkdir($dir_name);                
        }
        
        /* 
        * Create folder $nombreSucursal if not exist.
        */
        $dir_name = $folder_name . '/' . $nombreSucursal;                
        if (!is_dir($dir_name)) {
            mkdir($dir_name);
        }

        
        echo $idSucursal . "\n";
        
        /*
            Create file txt
        */
        $doc = fopen($dir_name . '/' . $idSucursal . '_' . $today . '.txt', 'w');

        fwrite($doc, "# Sucursal: " . $nombreSucursal . PHP_EOL);
        fwrite($doc, "# week " . date('Y-M-d', strtotime($fecha_inicio)) . '  -  ' . date('Y-M-d', strtotime($today)) . PHP_EOL);

        while ($fecha_inicio <= $today) {
            echo $fecha_inicio . "\n";
            fwrite($doc, "# " . date('l', strtotime($fecha_inicio)) . PHP_EOL);
            
            $platillos = self::damePlatillosSucursal($idSucursal);
            $size_platillos = $platillos->size();                
            
            for ($i = 0; $i < $size_platillos; $i++) {                    
                $idPlatillo = $platillos->get($i)->getIdPlatillo();                
                $tupla_cantidad = $this->connection->select("SELECT cantidad FROM platillos_vendidos WHERE idplatillo = $idPlatillo "
                    . "AND fecha = '$fecha_inicio'")->fetch_array();
                $cantidad = (is_null($tupla_cantidad)) ? -1 : floatval($tupla_cantidad[0]);
                fwrite($doc, $platillos->get($i)->getNombre() . "," . $cantidad . PHP_EOL);
            }
            
            $fecha_inicio = date('Y-m-d', strtotime('+1 day', strtotime($fecha_inicio)));
        }            
        fclose($doc);
    }

    public function resetCantidadesVendidas()
    {
        $this->connection->update("UPDATE alimento SET notif_enviada = 0");
    }

    public function listarAlimentosVendidos(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal)
    {
        $alimentos = self::getFoodByBranch($idSucursal);
        foreach ($alimentos as $alimento) {
            $alimento->setCantidad(0);
            $idAlimento = $alimento->getIdAlimento();                
            $tupla_cantidad = $this->connection->select("SELECT SUM(cantidad) FROM alimentos_vendidos WHERE idalimento = $idAlimento "
                . "AND fecha >= '$fecha_inicio' AND fecha <= '$fecha_fin'")->fetch_array();
            $cantidad = (is_null($tupla_cantidad)) ? 0 : floatval($tupla_cantidad[0]);           
            $alimento->setCantidadVendida($cantidad);
        }
        return $alimentos;
    }

    public function listarPlatillosVendidos(string $fecha_inicio, string $fecha_fin, int $idSucursal)
    {
        $platillos = self::damePlatillosSucursal($idSucursal);
        foreach ($platillos as $platillo) {
            $idPlatillo = $platillo->getIdPlatillo();                
            $tupla_cantidad = $this->connection->select("SELECT SUM(cantidad) FROM platillos_vendidos WHERE idplatillo = $idPlatillo "
                . "AND fecha >= '$fecha_inicio' AND fecha <= '$fecha_fin'")->fetch_array();
            $cantidad = (is_null($tupla_cantidad)) ? 0 : floatval($tupla_cantidad[0]);           
            $platillo->setCantidadVendida($cantidad);
        }
        return $platillos;
    }

    public function cancelarAlimentoAlterado(int $idalimento_alterado) {
        return $this->connection->delete("DELETE FROM alimentos_alterados WHERE idalimento_alterado = $idalimento_alterado");
    }

    public function cancelarAlimentoSurtido(int $idalimento_surtido) {
        return $this->connection->delete("DELETE FROM alimentos_surtidos WHERE idalimento_surtido = $idalimento_surtido");
    }
}

class Id_Cantidad
{
    public $idPlatillo;
    public $cantidad;

    public function __construct(int $idPlatillo, int $cantidad)
    {
        $this->idPlatillo = $idPlatillo;
        $this->cantidad = $cantidad;
    }
}
