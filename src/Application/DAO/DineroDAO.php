<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Conexion;
use App\Application\Model\Gasto;

class DineroDAO
{
    private $_conexion;

    public function __construct()
    {
        $this->_conexion = new Conexion();
    }

    public function setDineroRecibido(float $dineroRecibido, int $idSucursal) : bool
    {        
        $diaActual = date('Y-m-d');
        $hayDineroRecibido = $this->_conexion->select("SELECT dinero_recibido FROM resumen WHERE fecha = '$diaActual' AND idsucursal = $idSucursal");
        if ($hayDineroRecibido->num_rows == 0) {            
            return $this->_conexion->insert("INSERT INTO resumen(dinero_ventas, dinero_gastos, dinero_recibido, fecha, idsucursal) "
                . "VALUES(0, 0, $dineroRecibido, '$diaActual', $idSucursal)");
        } else {            
            return $this->_conexion->update("UPDATE resumen SET dinero_recibido = $dineroRecibido WHERE fecha = '$diaActual' AND idsucursal = $idSucursal");
        }
    }

    public function updateDineroRecibido(int $idSucursal, string $date, float $dineroRecibido)
    {
        return $this->_conexion->update("UPDATE resumen SET dinero_recibido = $dineroRecibido WHERE fecha = '$date' AND idsucursal = $idSucursal");
    }

    public function getDineroRecibido(int $idSucursal, ?string $date = null) : float {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }        
        $hayDineroRecibido = $this->_conexion->select("SELECT dinero_recibido FROM resumen WHERE fecha = '$date' AND idsucursal = $idSucursal");        
        return ($hayDineroRecibido->num_rows == 0) ? 0 : $hayDineroRecibido->fetch_array()[0];
    }

    public function deleteSale(int $saleID) : bool {
        $filename = '/home/u775772700/public_html/public_html/tests/todays_registration.txt';
        $tupla = $this->_conexion->select("SELECT venta.fecha, platillo.nombre, venta.precio "
        . "FROM venta, platillo WHERE venta.idventa = $saleID AND venta.idplatillo = platillo.idplatillo")->fetch_array();
        $txt = date('d-F-Y', strtotime($tupla[0])) . ", $tupla[1], $$tupla[2]";
        file_put_contents($filename, $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
        return $this->_conexion->delete("DELETE FROM venta WHERE idventa = $saleID");

    }

    private function resetIngreso(int $idSucursal)
    {
        return $this->_conexion->update("UPDATE sucursal SET dinero = 0 WHERE idsucursal = $idSucursal");
    }

    private function resetIdVentaActual()
    {
        $this->_conexion->delete("DELETE FROM venta_actual");
        return $this->_conexion->update("ALTER TABLE venta_actual AUTO_INCREMENT = 1");
    }    

    public function cerrarVenta(int $idSucursal)
    {
        $diaActual = date('Y-m-d');
        $dineroVentas = self::cantidadTotalVentas(null, null, $idSucursal);
        $dineroGastos = self::cantidadTotalGastos(null, null, $idSucursal);
        $this->_conexion->update("UPDATE sucursal SET num_ticket = 1 WHERE idsucursal = $idSucursal");
        self::resetIngreso($idSucursal);
        self::resetIdVentaActual();
        if (($dineroVentas != 0) || ($dineroGastos != 0)) {
            $hayDineroRecibido = $this->_conexion->select("SELECT dinero_recibido FROM resumen WHERE fecha = '$diaActual' AND idsucursal = $idSucursal");
            if ($hayDineroRecibido->num_rows == 0) {
                $dineroRecibido = $dineroVentas - $dineroGastos;
                return $this->_conexion->insert("INSERT INTO resumen(dinero_ventas, dinero_gastos, dinero_recibido, fecha, idsucursal) "
                    . "VALUES($dineroVentas, $dineroGastos, $dineroRecibido, '$diaActual', $idSucursal)");
            } else {
                return $this->_conexion->update("UPDATE resumen SET dinero_ventas = $dineroVentas, dinero_gastos = $dineroGastos WHERE fecha = '$diaActual' AND idsucursal = $idSucursal");
            }
        } else {
            return false;
        }
    }

    public function realizarGasto(int $idSucursal, Gasto $gasto) : bool
    {
        if ($this->_conexion->insert("INSERT INTO gasto(cantidad, fecha, concepto, idsucursal, idusuario) "
            . "VALUES(". $gasto->getCantidad() . ", '"
            . $gasto->getFecha() . "','"
            . $gasto->getConcepto() . "', "
            . $idSucursal . ", "
            . $gasto->getIdUsuario() . ")")) {
            return true;
        } else {
            return false;
        }
    }

    public function dameGasto(int $idGasto) : ?Gasto
    {
        $tupla = $this->_conexion->select("SELECT idgasto, cantidad, fecha, concepto, idusuario FROM gasto WHERE idgasto = " . $idGasto)->fetch_array();
        return (isset($tupla)) ? new Gasto($tupla[0], $tupla[1], $tupla[2], $tupla[3], $tupla[4]) : null;
    }

    public function cancelarGasto(int $idGasto) : bool
    {
        return $this->_conexion->delete("DELETE FROM gasto WHERE idgasto = " . $idGasto);
    }

    public function listarGastos(?string $fechaInicio, ?string $fechaFin, ?string $nombre_gasto, int $idSucursal) : ?array
    {
		$gastos = [];
        $result = $this->_conexion->select("SELECT gasto.idgasto, gasto.fecha, gasto.cantidad, "
            . "gasto.concepto, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
            . "FROM gasto, usuario " 
            . "WHERE gasto.idusuario = usuario.idusuario "
            . "AND gasto.idsucursal = '$idSucursal' "
            . "AND gasto.concepto LIKE '%$nombre_gasto%'"
            . "AND gasto.fecha >= '$fechaInicio' AND gasto.fecha <= '$fechaFin' ORDER BY fecha DESC");        
        while ($tupla = $result->fetch_array()) {
            array_push($gastos, array($tupla[0], $tupla[1], $tupla[2], $tupla[3], $tupla[4]));
		}
		return (count($gastos) > 0) ? $gastos : null;
    }
	
	public function cantidadTotalGastos(?string $fechaInicio, ?string $fechaFin, int $idSucursal) : float
    {
        if (!isset($fechaInicio)) {
    		$fechaInicio = date('Y-m-d');
		}
        if (!isset($fechaFin)) {
            $fechaFin = date('Y-m-d 23:59:59');
        } else {
            $fechaFin = date('Y-m-d 23:59:59', strtotime($fechaFin));
        }
		$tupla = $this->_conexion->select("SELECT SUM(cantidad) FROM gasto "
			. "WHERE fecha >= '$fechaInicio' AND fecha <= '$fechaFin' " 
			. "AND idsucursal = $idSucursal")->fetch_array();
        return floatval($tupla[0]);
    }

    public function getResumen(?string $fechaInicio, ?string $fechaFin, int $idSucursal) : ?array
    {
		$ingresos = [];
        $result = $this->_conexion->select("SELECT fecha, dinero_ventas, dinero_gastos, dinero_recibido FROM resumen " 
            . "WHERE fecha >= '$fechaInicio' AND fecha <= '$fechaFin' "
            . "AND idsucursal = $idSucursal ORDER BY fecha DESC");
        while ($tupla = $result->fetch_array()) {
            array_push($ingresos, array($tupla[0], $tupla[1], $tupla[2], $tupla[3]));
		}
		return (count($ingresos) > 0) ? $ingresos : null;
    }
	
	public function cantidadTotalGanancias(?string $fechaInicio, ?string $fechaFin, int $idSucursal) : float
    {
		$tupla = $this->_conexion->select("SELECT SUM(dinero_ventas - dinero_gastos) FROM resumen WHERE fecha >= '$fechaInicio' AND fecha <= '$fechaFin' " 
			. "AND idsucursal = $idSucursal")->fetch_array();
        return floatval($tupla[0]);
    }

	public function cantidadTotalCortesias(?string $fechaInicio, ?string $fechaFin, int $idSucursal) : float
    {
		$tupla = $this->_conexion->select("SELECT SUM(precio) FROM cortesia "
			. "WHERE fecha >= '$fechaInicio' AND fecha <= '$fechaFin' "
			. "AND idsucursal = $idSucursal")->fetch_array();
        return floatval($tupla[0]);
    }

	public function listarCortesias(?string $fechaInicio, ?string $fechaFin, int $idSucursal) : ?array
    {
		$cortesias = [];
        $result = $this->_conexion->select("SELECT cortesia.idcortesia, cortesia.fecha, platillo.nombre, cortesia.precio, "
            . "cortesia.cantidad, cortesia.concepto, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
            . "FROM cortesia, platillo, usuario "
            . "WHERE cortesia.idusuario = usuario.idusuario AND cortesia.idplatillo = platillo.idplatillo "
            . "AND cortesia.idsucursal = '$idSucursal' "
            . "AND cortesia.fecha >= '$fechaInicio' AND cortesia.fecha <= '$fechaFin' ORDER BY fecha DESC");
        while ($tupla = $result->fetch_array()) {
            array_push($cortesias, array($tupla[0], $tupla[1], $tupla[2], $tupla[3], $tupla[4], $tupla[5], $tupla[6]));
		}
		return (count($cortesias) > 0) ? $cortesias : null;
    }

    public function cantidadTotalVentas(?string $fechaInicio, ?string $fechaFin, int $idSucursal) : float
    {
        if (!isset($fechaInicio)) {
    		$fechaInicio = date('Y-m-d');
		}
        if (!isset($fechaFin)) {
            $fechaFin = date('Y-m-d 23:59:59');
        } else {
            $fechaFin = date('Y-m-d 23:59:59', strtotime($fechaFin));
        }
		$tupla = $this->_conexion->select("SELECT SUM(precio) FROM venta "
			. "WHERE fecha >= '$fechaInicio' AND fecha <= '$fechaFin' "
			. "AND idsucursal = $idSucursal")->fetch_array();
        return floatval($tupla[0]);
    }

    public function listarVentas(?string $fechaInicio, ?string $fechaFin, int $idSucursal) : ?array
    {
		$ventas = [];
        $result = $this->_conexion->select("SELECT venta.idventa, venta.fecha, platillo.nombre, venta.precio, "
            . "venta.cantidad, CONCAT(usuario.nombre_pila, ' ' ,usuario.apellido1) AS nombre "
            . "FROM venta, platillo, usuario "
            . "WHERE venta.idusuario = usuario.idusuario AND venta.idplatillo = platillo.idplatillo "
            . "AND venta.idsucursal = '$idSucursal' "
            . "AND venta.fecha >= '$fechaInicio' AND venta.fecha <= '$fechaFin' ORDER BY fecha DESC");
        while ($tupla = $result->fetch_array()) {
            array_push($ventas, array($tupla[0], $tupla[1], $tupla[2], $tupla[3], $tupla[4], $tupla[5]));
		}
		return (count($ventas) > 0) ? $ventas : null;
    }
}
