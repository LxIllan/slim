<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Connection;
use App\Application\Helper\Util;
use App\Application\Model\Dish;

class DishDAO
{
    private const TABLE_NAME = 'platillo';

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
     * @return Dish|null
     */
    public function createDish(array $data) : Dish|null
    {
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getDishById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Dish
     */
    public function getDishById(int $id): Dish
    {
        return $this->connection->select("SELECT * FROM platillo WHERE id = $id")->fetch_object('App\Application\Model\Dish');
    }

    /**
     * @param int $foodId
     * @return array
     */
    public function getDishesByFood(int $foodId): array
    {
        $dishes = [];
        $result = $this->connection->select("SELECT id FROM platillo WHERE idalimento = $foodId AND is_combo = 0 ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getDishById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $categoryId
     * @param int $branchId
     * @return array
     */
    public function getDishesByCategory(int $categoryId, int $branchId): array
    {
        $dishes = [];
        $query = "SELECT id FROM platillo WHERE idcategoria = $categoryId AND idsucursal = $branchId AND is_showed_in_sales = 1 ORDER BY name";
//        $query = <<<EOF
//            SELECT
//                platillo.id, platillo.nombre, platillo.precio, platillo.porcion, platillo.descripcion, platillo.is_combo,
//                platillo.cantidad_vendida, platillo.is_showed_in_sales
//            FROM platillo
//            JOIN alimento ON alimento.id = platillo.idalimento
//            WHERE alimento.idsucursal = $branchId ORDER BY platillo.nombre
//        EOF;
        $result = $this->connection->select($query);
        while ($row = $result->fetch_array()) {
            $dishes[] = $this->getDishById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Dish|null
     */
    public function editDish(int $id, array $data): Dish|null
    {
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getDishById($id) : null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteDish(int $id): bool
    {
        $query = Util::prepareDeleteQuery($id, self::TABLE_NAME);
        return $this->connection->delete($query);
    }
}