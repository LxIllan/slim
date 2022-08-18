<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Platillo;
use App\Application\Model\Alimento;
use App\Application\DAO\AlimentoDAO;
use App\Application\Helpers\Util;

class AlimentoController
{
    private $_alimentoDAO;

    public function __construct()
    {
        $this->_alimentoDAO = new AlimentoDAO();
    }

    public function agregarAlimento($nombre, $cantidad, $cantidadNotif, $costo, $precio, $idCategoria, $idSucursal) : bool
    {
        $idAlimento = $this->_alimentoDAO->getSiguienteId();
        $cantidadVendida = 0;
        $notifEnviada = 0;
        $verEnInicio = 0;
        if ($this->_alimentoDAO->agregarAlimento(new Alimento(
            $idAlimento,
            $nombre,
            $cantidad,
            $cantidadVendida,
            $cantidadNotif,
            $notifEnviada,
            $costo,
            $verEnInicio,
            $idCategoria,
            $idSucursal
            ))
            ) {
            $porcion = 1;                
            return self::agregarPlatillo($nombre, $precio, $porcion, $idAlimento, $idCategoria, $idSucursal);
        } else {
            return false;
        }
    }

    public function agregarPlatillo($nombre, $precio, $porcion, $idAlimento, $idCategoria, $idSucursal) : bool
    {
        $idPlatillo = 1;            
        $descripcion = '';
        $paquete = 0;
        $cantidadVendida = 0;
        $verEnVenta = 1;
        return $this->_alimentoDAO->agregarPlatillo(new Platillo(
            $idPlatillo,
            $nombre,
            $precio,
            $porcion,
            $descripcion,
            $paquete,
            $cantidadVendida,
            $verEnVenta,
            $idAlimento,
            $idCategoria,
            $idSucursal
        ));
    }
    
    public function agregarProducto($nombre, $cantidad, $cantidadNotif, $costo, $idSucursal) : bool
    {
        $idAlimento = 1;
        $cantidadVendida = 0;
        $notifEnviada = 0;
        $verEnInicio = 0;
        $idCategoria = Util::ID_PRODUCTOS;
        return $this->_alimentoDAO->agregarAlimento(new Alimento(
            $idAlimento,
            $nombre,
            $cantidad,
            $cantidadVendida,
            $cantidadNotif,
            $notifEnviada,
            $costo,
            $verEnInicio,
            $idCategoria,
            $idSucursal
        ));
    }

    public function agregarPaquete($nombre, $precio, $descripcion, $idSucursal) : bool
    {
        $idPlatillo = 1;
        $idAlimento = 1;
        $porcion = 0;
        $paquete = 1;
        $cantidadVendida = 0;
        $verEnVenta = 1;
        $idCategoria = Util::ID_PAQUETE;
        return $this->_alimentoDAO->agregarPlatillo(new Platillo(
            $idPlatillo,
            $nombre,
            $precio,
            $porcion,
            $descripcion,
            $paquete,
            $cantidadVendida,
            $verEnVenta,
            $idAlimento,
            $idCategoria,
            $idSucursal
        ));
    }

    public function agregarPlatilloAPaquete(int $idPaquete, int $idPlatillo)
    {
        return $this->_alimentoDAO->agregarPlatilloAPaquete($idPaquete, $idPlatillo);
    }

    public function usarProducto(int $idAlimento, int $cantidad, int $idCajero, int $idSucursal)
    {
        return $this->_alimentoDAO->usarProducto($idAlimento, $cantidad, $idCajero, $idSucursal);
    }

    public function listarProductosUsados(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) : ?array {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-j');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-j 23:59:59');
        } else {
            $fecha_fin = date('Y-m-j 23:59:59', strtotime($fecha_fin));
        }
        return $this->_alimentoDAO->listarProductosUsados($fecha_inicio, $fecha_fin, $idSucursal);
    }

    public function surtirAlimento(int $idAlimento, float $cantidad, float $cantidadActual, float $costo, int $idCajaero, int $idSucursal)
    {
        return $this->_alimentoDAO->surtirAlimento($idAlimento, $cantidad, $cantidadActual, $costo, $idCajaero, $idSucursal);
    }

    public function alterarAlimento(int $idAlimento, float $cantidad, string $justificacion, float $cantidadActual, float $costo, int $idCajaero, int $idSucursal)
    {
        return $this->_alimentoDAO->alterarAlimento($idAlimento, $cantidad, $justificacion, $cantidadActual, $costo, $idCajaero, $idSucursal);
    }

    public function editarPlatillo(Platillo $platillo)
    {
        return $this->_alimentoDAO->editarPlatillo($platillo);
    }

    public function editarAlimento(Alimento $alimento)
    {
        return $this->_alimentoDAO->editarAlimento($alimento);
    }        

    public function eliminarPlatilloDePaquete(int $idPaquete, int $idPlatillo)
    {
        return $this->_alimentoDAO->eliminarPlatilloDePaquete($idPaquete, $idPlatillo);
    }

    public function eliminarPlatillo(int $idPlatillo)
    {
        return $this->_alimentoDAO->eliminarPlatillo($idPlatillo);
    }

    public function eliminarAlimento(int $idAlimento)
    {
        return $this->_alimentoDAO->eliminarAlimento($idAlimento);
    }

    public function consultarPlatillo(int $idPlatillo)
    {
        return $this->_alimentoDAO->damePlatillo($idPlatillo);
    }

    public function consultarAlimento(int $idAlimento)
    {
        return $this->_alimentoDAO->dameAlimento($idAlimento);
    }

    public function listarAlimentos(int $idSucursal)
    {            
        return $this->_alimentoDAO->dameAlimentos($idSucursal);
    }

    public function listarAlimentosInicio(int $idSucursal)
    {            
        return $this->_alimentoDAO->dameAlimentosInicio($idSucursal);
    }

    public function listarProductos(int $idSucursal, string $nombre = '')
    {
        return $this->_alimentoDAO->dameProductos($idSucursal, $nombre);
    }

    public function listarPlatillos(int $idAlimento)
    {
        return $this->_alimentoDAO->damePlatillos($idAlimento);
    }

    public function listarPlatillosCategoria(int $idCategoria, int $idSucursal)
    {
        return $this->_alimentoDAO->damePlatillosCategoria($idCategoria, $idSucursal);
    }

    public function listarPlatillosPaquete(int $idPaquete)
    {
        return $this->_alimentoDAO->damePlatillosDePaquete($idPaquete);
    }

    public function listarPaquetes(int $idSucursal)
    {
        return $this->_alimentoDAO->damePaquetes($idSucursal);
    }

    /* Venta actual y regalias */
    public function venderPlatillos(int $idCajero, int $idSucursal, string $concepto, bool $regalo = false)
    {
        return $this->_alimentoDAO->venderPlatillos($idCajero, $idSucursal, $concepto, $regalo);
    }

    public function limpiarVentaActual(int $idSucursal)
    {
        return $this->_alimentoDAO->limpiarVentaActual($idSucursal);
    }

    public function eliminarPlatilloDeVentaActual(int $idPlatillo, int $idSucursal)
    {
        return $this->_alimentoDAO->eliminarPlatilloDeVentaActual($idPlatillo, $idSucursal);
    }

    public function agregarPlatilloVentaActual(int $idPlatillo, int $cantidad, int $idSucursal)
    {
        return $this->_alimentoDAO->agregarPlatilloVentaActual($idPlatillo, $cantidad, $idSucursal);
    }

    public function strPlatillosVentaActual(int $idSucursal)
    {
        return $this->_alimentoDAO->dameStrPlatillosVentaActual($idSucursal);
    }

    public function listarPlatillosVentaActual(int $idSucursal)
    {
        return $this->_alimentoDAO->damePlatillosVentaActual($idSucursal);
    }

    public function listarPlatillosSucursal(int $idSucursal)
    {
        return $this->_alimentoDAO->damePlatillosSucursal($idSucursal);
    }

    public function getNumTicket(int $idSucursal): int {            
        return $this->_alimentoDAO->getNumTicket($idSucursal);
    }

    public function registrarCantidadesVendidas(int $idSucursal)
    {
        return $this->_alimentoDAO->registrarCantidadesVendidas($idSucursal);
    }

    public function resetCantidadesVendidas()
    {
        return $this->_alimentoDAO->resetCantidadesVendidas();
    }

    public function listarAlimentosVendidos(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-j');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-j 23:59:59');
        } else {
            $fecha_fin = date('Y-m-j 23:59:59', strtotime($fecha_fin));
        }
        return $this->_alimentoDAO->listarAlimentosVendidos($fecha_inicio, $fecha_fin, $idSucursal);
    }

    public function listarPlatillosVendidos(?string $fecha_inicio, ?string $fecha_fin, int $idSucursal) {
        if (!isset($fecha_inicio)) {
            $fecha_inicio = date('Y-m-j');
        }
        if (!isset($fecha_fin)) {
            $fecha_fin = date('Y-m-j 23:59:59');
        } else {
            $fecha_fin = date('Y-m-j 23:59:59', strtotime($fecha_fin));
        }
        return $this->_alimentoDAO->listarPlatillosVendidos($fecha_inicio, $fecha_fin, $idSucursal);
    }
    
    public function cancelarVenta(int $idVenta, bool $esVenta = true)
    {
        return $this->_alimentoDAO->cancelarVenta($idVenta, $esVenta);
    }

    public function createReportPlatillos(int $idSucursal) {
        return $this->_alimentoDAO->createReportPlatillos($idSucursal);
    }

    public function cancelarAlimentoAlterado(int $idalimento_alterado) {
        return $this->_alimentoDAO->cancelarAlimentoAlterado($idalimento_alterado);
    }

    public function cancelarAlimentoSurtido(int $idalimento_surtido) {
        return $this->_alimentoDAO->cancelarAlimentoSurtido($idalimento_surtido);
    }
}