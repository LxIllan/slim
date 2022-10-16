<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Conexion;
use App\Application\Helpers\Util;
use App\Application\Model\Platillo;
use App\Application\Model\Alimento;

class AlimentoDAO
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    public function getSiguienteId(): int {            ;            
        $tupla = $this->conexion->select("SELECT AUTO_INCREMENT FROM "
            . "INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'alimento'")->fetch_array();            
        return $tupla[0];
    }

    public function venderPlatillos(int $idCajero, int $idSucursal, string $concepto, bool $regalo = false)
    {            
        $id_cantidades = self::dameIdCantidadVentaActual($idSucursal);
        
        if (isset($id_cantidades))
        {
            foreach ($id_cantidades as $id_cantidad) {
                $platillo = self::damePlatillo($id_cantidad->idPlatillo);
                if ($regalo) {
                    self::regalarPlatillo($platillo->getIdPlatillo(), $id_cantidad->cantidad, $concepto, $idCajero, $idSucursal);
                } else {
                    self::venderPlatillo($platillo->getIdPlatillo(), $id_cantidad->cantidad, $idCajero, $idSucursal);
                }

                if ($platillo->getPaquete() == 1) {
                    $platillos_paquete = self::damePlatillosDePaquete($platillo->getIdPlatillo());                        
                    if (isset($platillos_paquete)) {
                        $cantidadVendida = $platillo->getCantidadVendida();
                        $this->conexion->update("UPDATE platillo SET cantidad_vendida = " . ($cantidadVendida + $id_cantidad->cantidad) . " WHERE idplatillo = " . $platillo->getIdPlatillo());
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

    public function regalarPlatillo(int $idPlatillo, int $cantidad, string $concepto, int $idCajero, int $idSucursal)
    {
        $platillo = self::damePlatillo($idPlatillo);
        $porcion = $platillo->getPorcion();
        $precio = $platillo->getPrecio();
        $total = $precio * $cantidad;
        return $this->conexion->insert(
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
        $platillo = self::damePlatillo($idPlatillo);
        $porcion = $platillo->getPorcion();
        $precio = $platillo->getPrecio();
        $total = $precio * $cantidad;
        return $this->conexion->insert(
                "INSERT INTO venta(idplatillo, cantidad, precio, fecha, idusuario, idsucursal) VALUES ("
                    . $platillo->getIdPlatillo() . ", "
                    . $cantidad . ", "
                    . $total . ", '"
                    . date('Y-m-d H:i:s') . "', "
                    . $idCajero .", "
                    . $idSucursal . ")"
                    );
    }

    public function dameVentaDeProductosFechas($fechaInicio, $fechaFin)
    {
        $result = $this->conexion->select("SELECT producto.nombre, "
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

    public function cancelarVenta(int $idVenta, bool $esVenta = true)
    {
        if ($esVenta) {
            $fecha = $this->conexion->select("SELECT fecha FROM venta WHERE idventa = $idVenta")->fetch_array()[0];
            $result = $this->conexion->select("SELECT idplatillo, cantidad, precio, idsucursal, idventa FROM venta WHERE fecha = '$fecha'");
        } else {
            $fecha = $this->conexion->select("SELECT fecha FROM cortesia WHERE idcortesia = $idVenta")->fetch_array()[0];
            $result = $this->conexion->select("SELECT idplatillo, cantidad, precio, idsucursal, idcortesia FROM cortesia WHERE fecha = '$fecha'");
        }            

        while ($tupla = $result->fetch_array()) {
            $idPlatillo = $tupla[0];
            $cantidadVendida = $tupla[1];
            $idVenta = $tupla[4];
            $platillo = self::damePlatillo($idPlatillo);
            if ($platillo->getPaquete() == 1) {
                $paquetesVendidos = $platillo->getCantidadVendida();
                $this->conexion->update("UPDATE platillo SET cantidad_vendida = " . ($paquetesVendidos - $cantidadVendida) . " WHERE idplatillo = " . $platillo->getIdPlatillo());
                $platillos_paquete = self::damePlatillosDePaquete($platillo->getIdPlatillo());
                foreach ($platillos_paquete as $platilloPaquete) {
                    self::cancelarPlatillo($platilloPaquete->getIdPlatillo(), $cantidadVendida, true);
                }
            } else {
                self::cancelarPlatillo($idPlatillo, $cantidadVendida, false);
            }
            if ($esVenta) {
                $this->conexion->delete("DELETE FROM venta WHERE idventa = $idVenta");
            } else {
                $this->conexion->delete("DELETE FROM cortesia WHERE idcortesia = $idVenta");
            }                
        }
        return true;
    }

    private function cancelarPlatillo(int $idPlatillo, int $cantidadVendida, bool $esPaquete)
    {
        $idAlimento = $this->conexion->select("SELECT idalimento FROM platillo WHERE idplatillo = $idPlatillo")->fetch_array()[0];
        $porcion = $this->conexion->select("SELECT porcion FROM platillo WHERE idplatillo = $idPlatillo")->fetch_array()[0];
        
        $cantidadActual = $this->conexion->select("SELECT cantidad FROM alimento WHERE idalimento = $idAlimento")->fetch_array()[0];
        $nuevaCantidad = $cantidadActual + ($porcion * $cantidadVendida);            
        $this->conexion->update("UPDATE alimento SET cantidad = $nuevaCantidad WHERE idalimento = $idAlimento");
        
        $alimentosVendidos = $this->conexion->select("SELECT cantidad_vendida FROM alimento WHERE idalimento = $idAlimento")->fetch_array()[0];
        $this->conexion->update("UPDATE alimento SET cantidad_vendida = " . ($alimentosVendidos - ($porcion * $cantidadVendida)) . " WHERE idalimento = " . $idAlimento);

        if (!$esPaquete) {
            $platillosVendidos = $this->conexion->select("SELECT cantidad_vendida FROM platillo WHERE idplatillo = $idPlatillo")->fetch_array()[0];
            $this->conexion->update("UPDATE platillo SET cantidad_vendida = " . ($platillosVendidos - $cantidadVendida) . " WHERE idplatillo = " . $idPlatillo);
        }            
    }

    public function createReportPlatillos(int $idSucursal) {
        
        date_default_timezone_set('America/Mexico_City');

        $folder_name = dirname(__FILE__) . '/weekly-reports';            

        $sucursalDAO = new SucursalDAO();
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
                $tupla_cantidad = $this->conexion->select("SELECT cantidad FROM platillos_vendidos WHERE idplatillo = $idPlatillo " 
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
        return $this->conexion->update("UPDATE alimento SET notif_enviada = 0");
    }

    public function cancelarAlimentoAlterado(int $idalimento_alterado) {
        return $this->conexion->delete("DELETE FROM alimentos_alterados WHERE idalimento_alterado = $idalimento_alterado");
    }

    public function cancelarAlimentoSurtido(int $idalimento_surtido) {
        return $this->conexion->delete("DELETE FROM alimentos_surtidos WHERE idalimento_surtido = $idalimento_surtido");
    }
}
