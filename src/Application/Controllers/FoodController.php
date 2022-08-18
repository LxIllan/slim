<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Dish;
use App\Application\Model\Food;
use App\Application\Model\Platillo;
use App\Application\Model\Alimento;
use App\Application\DAO\FoodDAO;
use App\Application\Helpers\Util;
use App\Application\Controllers\DishController;

class FoodController
{
    /**
     * @var FoodDAO $foodDAO
     */
    private FoodDAO $foodDAO;

    public function __construct()
    {
        $this->foodDAO = new FoodDAO();
    }

    /**
     * @param array $data
     * @return array
     */
    public function createFood(array $data): array
    {
        $food = $this->foodDAO->createFood($data);
        if ($food == null) {
            return ["message" => "Error creating food"];
        }

        $dishData = [
            "name" => $food->name,
            "price" => $food->cost,
            "food_id" => $food->id,
            "category_id" => $food->category_id,
            "branch_id" => $food->branch_id
        ];

        $dishController = new DishController();
        $dish = $dishController->createDish($dishData);
        if ($dish == null) {
            return [
                "food" => $food,
                "dish" => "Error creating dish"
            ];
        }

        return ["food" => $food, "dish" => $dish];
    }

    /**
     * @param int $id
     * @return Food
     */
    public function getFoodById(int $id): Food
    {
        return $this->foodDAO->getFoodById($id);
    }

    /**
     * @param int $branchId
     * @return Food[]
     */
    public function getFoodByBranch(int $branchId): array
    {
        return $this->foodDAO->getFoodByBranch($branchId);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Food|null
     */
    public function editFood(int $id, array $data): Food|null
    {
        return $this->foodDAO->editFood($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteFood(int $id): bool
    {
        return $this->foodDAO->deleteFood($id);
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
        return $this->foodDAO->supply($foodId, $quantity, $userId, $branchId);
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
        return $this->foodDAO->alter($foodId, $quantity, $reason, $userId, $branchId);
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
    }

    public function agregarPlatilloAPaquete(int $idPaquete, int $idPlatillo)
    {
        return $this->foodDAO->agregarPlatilloAPaquete($idPaquete, $idPlatillo);
    }


    public function eliminarPlatilloDePaquete(int $idPaquete, int $idPlatillo)
    {
        return $this->foodDAO->eliminarPlatilloDePaquete($idPaquete, $idPlatillo);
    }

    public function listarAlimentosInicio(int $idSucursal)
    {            
        return $this->foodDAO->dameAlimentosInicio($idSucursal);
    }

    /* Venta actual y regalias */
    public function venderPlatillos(int $idCajero, int $idSucursal, string $concepto, bool $regalo = false)
    {
        return $this->foodDAO->venderPlatillos($idCajero, $idSucursal, $concepto, $regalo);
    }

    public function limpiarVentaActual(int $idSucursal)
    {
        return $this->foodDAO->limpiarVentaActual($idSucursal);
    }

    public function eliminarPlatilloDeVentaActual(int $idPlatillo, int $idSucursal)
    {
        return $this->foodDAO->eliminarPlatilloDeVentaActual($idPlatillo, $idSucursal);
    }

    public function agregarPlatilloVentaActual(int $idPlatillo, int $cantidad, int $idSucursal)
    {
        return $this->foodDAO->agregarPlatilloVentaActual($idPlatillo, $cantidad, $idSucursal);
    }

    public function strPlatillosVentaActual(int $idSucursal)
    {
        return $this->foodDAO->dameStrPlatillosVentaActual($idSucursal);
    }

    public function listarPlatillosVentaActual(int $idSucursal)
    {
        return $this->foodDAO->damePlatillosVentaActual($idSucursal);
    }

    public function listarPlatillosSucursal(int $idSucursal)
    {
        return $this->foodDAO->damePlatillosSucursal($idSucursal);
    }

    public function resetCantidadesVendidas()
    {
        return $this->foodDAO->resetCantidadesVendidas();
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
        return $this->foodDAO->listarAlimentosVendidos($fecha_inicio, $fecha_fin, $idSucursal);
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
        return $this->foodDAO->listarPlatillosVendidos($fecha_inicio, $fecha_fin, $idSucursal);
    }
    
    public function cancelarVenta(int $idVenta, bool $esVenta = true)
    {
        return $this->foodDAO->cancelarVenta($idVenta, $esVenta);
    }

    public function createReportPlatillos(int $idSucursal) {
        return $this->foodDAO->createReportPlatillos($idSucursal);
    }

    public function cancelarAlimentoAlterado(int $idalimento_alterado) {
        return $this->foodDAO->cancelarAlimentoAlterado($idalimento_alterado);
    }

    public function cancelarAlimentoSurtido(int $idalimento_surtido) {
        return $this->foodDAO->cancelarAlimentoSurtido($idalimento_surtido);
    }
}