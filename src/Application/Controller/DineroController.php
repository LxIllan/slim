<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\Model\Gasto;
use App\Application\DAO\DineroDAO;

class AdminDinero {
   
    private $_dineroDAO;

    function __construct() {		
        $this->_dineroDAO = new DineroDAO();
    }

    public function realizarGasto(int $idSucursal, float $cantidad, string $concepto, int $idUsuario) : ?bool {
        return $this->_dineroDAO->realizarGasto($idSucursal, new Gasto(1, $cantidad, date('Y-m-d H:i:s'), $concepto, $idUsuario));
    }

    public function dameGasto(int $idGasto) : ?Gasto {
        return $this->_dineroDAO->dameGasto($idGasto);
    }

    function cancelarGasto(int $idGasto) : bool {
        return $this->_dineroDAO->cancelarGasto($idGasto);
    }

    public function cantidadTotalVentas(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : float {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-d');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-d 23:59:59');
        } else {
            $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));
        }
        return $this->_dineroDAO->cantidadTotalVentas($fecha_inicio, $fecha_fin, $idSucursal);
    }

    public function listarCortesias(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : ?array {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-d');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-d 23:59:59');
        } else {
            $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));
        }
        return $this->_dineroDAO->listarCortesias($fecha_inicio, $fecha_fin, $idSucursal);
    }

    public function cantidadTotalCortesias(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : float {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-d');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-d 23:59:59');
        } else {
            $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));
        }
        return $this->_dineroDAO->cantidadTotalCortesias($fecha_inicio, $fecha_fin, $idSucursal);
    }

    public function listarVentas(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : ?array {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-d');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-d 23:59:59');
        } else {
            $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));
        }
        return $this->_dineroDAO->listarVentas($fecha_inicio, $fecha_fin, $idSucursal);
    }

    public function listarGastos(?string $fecha_inicio, ?string $fecha_fin, ?string $nombre_gasto, int $idSucursal) : ?array {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-d');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-d 23:59:59');
        } else {
            $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));
        }
        if (!isset($nombre_gasto)) {
            $nombre_gasto = '';
        }
        return $this->_dineroDAO->listarGastos($fecha_inicio, $fecha_fin, $nombre_gasto, $idSucursal);
    }

    public function cantidadTotalGastos(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : float
    {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-d');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-d 23:59:59');
        } else {
            $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));
        }
        return $this->_dineroDAO->cantidadTotalGastos($fecha_inicio, $fecha_fin, $idSucursal);
    }

    public function getResumen(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : ?array {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-d', strtotime("this week"));
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-d 23:59:59');
        } else {
            $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));
        }
        return $this->_dineroDAO->getResumen($fecha_inicio, $fecha_fin, $idSucursal);
    }

    public function cantidadTotalGanancias(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : float
    {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-d', strtotime("this week"));
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-d 23:59:59');
        } else {
            $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));
        }
        return $this->_dineroDAO->cantidadTotalGanancias($fecha_inicio, $fecha_fin, $idSucursal);
    }	

    public function getDineroRecibido(int $idSucursal, string $date = null) : float
    {
        return $this->_dineroDAO->getDineroRecibido($idSucursal, $date);
    }

    public function setDineroRecibido(float $dineroRecibido, int $idSucursal)
    {
        return $this->_dineroDAO->setDineroRecibido($dineroRecibido, $idSucursal);
    }

    public function updateDineroRecibido(int $idSucursal, string $date, float $dineroRecibido)
    {
        return $this->_dineroDAO->updateDineroRecibido($idSucursal, $date, $dineroRecibido);
    }

    public function cerrarVenta(int $idSucursal) : bool
    {
        return $this->_dineroDAO->cerrarVenta($idSucursal);
    }

    public function deleteSale(int $saleID) : bool {
        return $this->_dineroDAO->deleteSale($saleID);
    }
}
