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
        return ($this->connection->update($query)) ? $this->getFoodById($this->connection->getLastId()) : null;
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

    public function venderPlatillos(int $idCajero, int $idSucursal, string $concepto, bool $regalo = false)
    {            
        $id_cantidades = self::dameIdCantidadVentaActual($idSucursal);
        
        if (isset($id_cantidades))
        {
            foreach ($id_cantidades as $id_cantidad) {
                $platillo = self::getDishById($id_cantidad->idPlatillo);
                if ($regalo) {
                    self::regalarPlatillo($platillo->getIdPlatillo(), $id_cantidad->cantidad, $concepto, $idCajero, $idSucursal);
                } else {
                    self::venderPlatillo($platillo->getIdPlatillo(), $id_cantidad->cantidad, $idCajero, $idSucursal);
                }

                if ($platillo->getPaquete() == 1) {
                    $platillos_paquete = self::damePlatillosDePaquete($platillo->getIdPlatillo());                        
                    if (isset($platillos_paquete)) {
                        $cantidadVendida = $platillo->getCantidadVendida();
                        $this->connection->update("UPDATE platillo SET cantidad_vendida = " . ($cantidadVendida + $id_cantidad->cantidad) . " WHERE idplatillo = " . $platillo->getIdPlatillo());
                        for ($j = 0; $j < $platillos_paquete->size(); $j++) {
                            self::descontarPlatillo($platillos_paquete->get($j)->getIdPlatillo(), $id_cantidad->cantidad, true);
                        }
                    }                       
                } else {
                    self::descontarPlatillo($platillo->getIdPlatillo(), $id_cantidad->cantidad, false);
                }
            }
        }            
        self::limpiarVentaActual($idSucursal);
    }

    public function listarProductosUsados(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : ?array
    {
        $productos_usados = [];
        $result = $this->connection->select("SELECT producto_usado.idproducto_usado, producto_usado.fecha, alimento.nombre, "
            . "producto_usado.cantidad, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
            . "FROM producto_usado, alimento, usuario " 
            . "WHERE producto_usado.idusuario = usuario.idusuario AND producto_usado.idalimento = alimento.idalimento "
            . "AND producto_usado.idsucursal = '$idSucursal' "
            . "AND producto_usado.fecha >= '$fecha_inicio' AND producto_usado.fecha <= '$fecha_fin' ORDER BY fecha DESC");
        while ($tupla = $result->fetch_array()) {
            array_push($productos_usados, array($tupla[0], $tupla[1], $tupla[2], $tupla[3], $tupla[4]));
        }
        return (count($productos_usados) > 0) ? $productos_usados : null;
    }

    public function usarProducto(int $idAlimento, int $cantidad, int $idCajero, int $idSucursal)
    {            
        return self::descontarAlimento($idAlimento, $cantidad) && 
                $this->connection->insert("INSERT INTO producto_usado(idalimento, cantidad, fecha, idusuario, idsucursal) VALUES ("
                    . $idAlimento . ", "
                    . $cantidad . ", '"
                    . date('Y-m-d H:i:s') . "', "
                    . $idCajero . ", "
                    . $idSucursal . ")");
    }        

    public function descontarPlatillo(int $idPlatillo, int $cantidad, bool $esPaquete)
    {            
        $platillo = self::getDishById($idPlatillo);
        if (!$esPaquete) {
            $cantidadVendida = $platillo->getCantidadVendida();
            $this->connection->update("UPDATE platillo SET cantidad_vendida = " . ($cantidadVendida + $cantidad) . " WHERE idplatillo = " . $idPlatillo);
        }
        $idAlimento = $platillo->getIdAlimento();
        $alimento = self::getFoodById($idAlimento);
        $porcion = $platillo->getPorcion();            
        $cantidadTotal = ($porcion * $cantidad);
        $cantidadVendida = $alimento->getCantidadVendida();
        return self::descontarAlimento($idAlimento, $cantidadTotal) && 
            $this->connection->update("UPDATE alimento SET cantidad_vendida = " . ($cantidadVendida + ($cantidadTotal)) . " WHERE idalimento = " . $idAlimento);
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

    public function limpiarVentaActual(int $idSucursal)
    {
        return $this->connection->delete("DELETE FROM venta_actual WHERE idsucursal = $idSucursal");
    }

    public function eliminarPlatilloDeVentaActual(int $idPlatillo, int $idSucursal)
    {
        return $this->connection->delete("DELETE FROM venta_actual WHERE idsucursal = $idSucursal AND idplatillo = $idPlatillo LIMIT 1");
    }

    public function agregarPlatilloVentaActual(int $idPlatillo, int $cantidad, int $idSucursal)
    {
        return $this->connection->insert("INSERT INTO venta_actual(idplatillo, cantidad, idsucursal) "
            . "VALUES($idPlatillo, $cantidad, $idSucursal)");
    }

    public function dameStrPlatillosVentaActual(int $idSucursal)
    {
        $platillos = [];
        $result = $this->connection->select("SELECT platillo.idplatillo, platillo.nombre, venta_actual.cantidad, platillo.precio FROM venta_actual, platillo "
            . "WHERE venta_actual.idsucursal = $idSucursal AND platillo.idplatillo = venta_actual.idplatillo");
        while ($tupla = $result->fetch_array()) {
            array_push($platillos, array($tupla[0], $tupla[1], $tupla[2], $tupla[3] * $tupla[2]));
        }
        return (count($platillos) > 0) ? $platillos : null;
    }

    public function dameIdCantidadVentaActual(int $idSucursal)
    {
        $id_cantidad = [];
        $result = $this->connection->select("SELECT idplatillo, cantidad FROM venta_actual WHERE idsucursal = $idSucursal");
        while ($tupla = $result->fetch_array()) {
            $id_cantidad[] = new Id_Cantidad($tupla['idplatillo'], $tupla['cantidad']);
        }
        return $id_cantidad;
    }

    public function damePlatillosVentaActual(int $idSucursal) 
    {
        $platillos = [];
        $result = $this->connection->select("SELECT idplatillo FROM venta_actual WHERE idsucursal = $idSucursal");
        while ($tupla = $result->fetch_array()) {
            $platillos[] = self::getDishById($tupla['idplatillo']);
        }
        return $platillos;
    }

    public function eliminarPlatilloDePaquete(int $idPaquete, int $idPlatillo)
    {
        return $this->connection->delete("DELETE FROM platillos_paquete WHERE idpaquete = $idPaquete AND idplatillo = $idPlatillo LIMIT 1");
    }

    public function damePlatillosDePaquete(int $idPaquete): array
    {
        $platillos = [];
        $result = $this->connection->select("SELECT idplatillo FROM platillos_paquete WHERE idpaquete = $idPaquete");
        while ($tupla = $result->fetch_array()) {
            $platillos[] = self::getDishById($tupla['idplatillo']);
        }
        return $platillos;
    }

    public function agregarPlatilloAPaquete(int $idPaquete, int $idPlatillo)
    {
        return $this->connection->insert("INSERT INTO platillos_paquete(idpaquete, idplatillo) "
            . "VALUES ($idPaquete, $idPlatillo)");
    }

    public function dameProductos(int $idSucursal, string $nombre = '')
    {
        $productos = [];
        $result = $this->connection->select("SELECT idalimento, nombre FROM alimento WHERE idsucursal = $idSucursal "
            . "AND idcategoria = " . Util::ID_PRODUCTOS . " AND nombre LIKE '%$nombre%' ORDER BY nombre");
        while ($tupla = $result->fetch_array()) {
            $productos[] = self::getFoodById($tupla['idalimento']);
        }
        return $productos;
    }

    public function damePlatillosSucursal(int $idSucursal)
    {
        $platillos = [];
        $result = $this->connection->select("SELECT idplatillo FROM platillo WHERE idsucursal = $idSucursal ORDER BY nombre");
        while ($tupla = $result->fetch_array()) {
            $platillos[] = self::getDishById($tupla['idplatillo']);
        }
        return $platillos;
    }

    public function damePlatillosCategoria(int $idCategoria, int $idSucursal)
    {
        $platillos = [];
        $result = $this->connection->select("SELECT idplatillo FROM platillo WHERE idcategoria = $idCategoria AND ver_en_venta = 1 AND idsucursal = $idSucursal ORDER BY nombre");
        while ($tupla = $result->fetch_array()) {
            $platillos[] = self::getDishById($tupla['idplatillo']);
        }
        return $platillos;
    }


    public function damePaquetes(int $idSucursal)
    {
        $paquetes = [];
        $result = $this->connection->select("SELECT idplatillo FROM platillo WHERE idsucursal = $idSucursal AND paquete = 1 ORDER BY nombre");
        while ($tupla = $result->fetch_assoc()) {
            $paquetes[] = self::getDishById(intval($tupla['idplatillo']));
        }
        return $paquetes;

    }        

    public function dameVentaDeProductosFechas($fechaInicio, $fechaFin)
    {
        $result = $this->connection->select("SELECT producto.nombre, "
            . "venta_productos.precio, venta_productos.num_piezas, venta_productos.fecha, "
            . "usuario.nombre_pila, usuario.apellido1 "
            . "FROM venta_productos, producto, usuario "
            . "WHERE venta_productos.idproducto = producto.idproducto "
            . "AND venta_productos.idusuario = usuario.idusuario "
            . "AND venta_productos.fecha >= '$fechaInicio' "
            . "AND venta_productos.fecha <= '$fechaFin'"
            . "ORDER BY venta_productos.fecha");
        $ventaDeProductos = array();
        while ($tupla = $result->fetch_array()) {
            array_push($ventaDeProductos, array($tupla[0], $tupla[1], $tupla[2],
                $tupla[3], $tupla[4] . ' ' . $tupla[5]));
        }
        return (count($ventaDeProductos) > 0) ? $ventaDeProductos : null;
    }

    public function listarVentas(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : ?array
    {
        $ventas = [];
        $result = $this->connection->select("SELECT venta.idventa, venta.fecha, platillo.nombre, venta.precio, "
            . "venta.cantidad, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
            . "FROM venta, platillo, usuario " 
            . "WHERE venta.idusuario = usuario.idusuario AND venta.idplatillo = platillo.idplatillo "
            . "AND venta.idsucursal = $idSucursal "
            . "AND venta.fecha >= '$fecha_inicio' AND venta.fecha <= '$fecha_fin' ORDER BY fecha DESC");
        while ($tupla = $result->fetch_array()) {
            array_push($ventas, array($tupla[0], $tupla[1], $tupla[2], $tupla[3], $tupla[4], $tupla[5]));
        }
        return (count($ventas) > 0) ? $ventas : null;
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

    public function registrarCantidadesVendidas(int $idSucursal)
    {
        $alimentos = self::getFoodByBranch($idSucursal);
        foreach ($alimentos as $alimento) {
            $this->connection->insert("INSERT INTO alimentos_vendidos(idalimento, cantidad, fecha) "
                . "VALUES("
                . $alimento->getIdAlimento() . ", "
                . $alimento->getCantidadVendida() . ", '"
                . date('Y-m-d H:i:s') . "')");
        }        

        $platillos = self::damePlatillosSucursal($idSucursal);
        foreach ($platillos as $platillo) {
            $this->connection->insert("INSERT INTO platillos_vendidos(idplatillo, cantidad, fecha) "
            . "VALUES("
            . $platillo->getIdPlatillo() . ", "
            . $platillo->getCantidadVendida() . ", '"
            . date('Y-m-d H:i:s') . "')");
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
