<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Connection;
use App\Application\Helper\Util;
use App\Application\Model\Dish;

class DishDAO
{
    private const TABLE_NAME = 'dish';

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
    public function create(array $data): Dish|null
    {
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->insert($query)) ? $this->getById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Dish
     */
    public function getById(int $id): Dish
    {
        return $this->connection
            ->select("SELECT * FROM dish WHERE id = $id")
            ->fetch_object('App\Application\Model\Dish');
    }

    /**
     * @param int $foodId
     * @return Dish[]
     */
    public function getDishesByFood(int $foodId): array
    {
        $dishes = [];
        $result = $this->connection
            ->select("SELECT id FROM dish WHERE food_id = $foodId AND is_combo = 0 ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $categoryId
     * @param int $branchId
     * @return Dish[]
     */
    public function getDishesByCategory(int $categoryId, int $branchId): array
    {
        $dishes = [];
        $query = "SELECT id FROM dish WHERE category_id = $categoryId AND branch_id = $branchId AND is_showed_in_sales = 1 ORDER BY name";
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
            $dishes[] = $this->getById(intval($row['id']));
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
        return ($this->connection->update($query)) ? $this->getById($id) : null;
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

    /**
     * @param int $branchId
     * @return Dish[]
     */
    public function getCombosByBranch(int $branchId): array
    {
        $dishes = [];
        $result = $this->connection
            ->select("SELECT id FROM dish WHERE branch_id = $branchId AND is_combo = 1 ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $comboId
     * @return Dish[]
     */
    public function getDishesByCombo(int $comboId): array
    {
        $dishes = [];
        $result = $this->connection->select("SELECT dish_id FROM dishes_in_combo WHERE combo_id = $comboId");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['dish_id']));
        }
        return $dishes;
    }

    /**
     * @param int $comboId
     * @param int $dishId
     * @return bool
     */
    public function addDishToCombo(int $comboId, int $dishId): bool
    {
        $dish = $this->getById($comboId);
        if (!$dish->is_combo) {
            return false;
        }

        $dataToInsert = [
            "combo_id" => $comboId,
            "dish_id" => $dishId
        ];

        return $this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'dishes_in_combo'));
    }
}