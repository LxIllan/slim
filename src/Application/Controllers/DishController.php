<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Dish;
use App\Application\DAO\DishDAO;
use stdClass;
class DishController
{
	/**
	 * @var DishDAO
	 */
	private DishDAO $dishDAO;

	public function __construct()
	{
		$this->dishDAO = new DishDAO();
	}

	/**
	 * @param array $data
	 * @return Dish|null
	 */
	public function createDish(array $data): Dish|null
	{
		return $this->dishDAO->create($data);
	}

	/**
	 * @param int $id
	 * @param array $columns
	 * @return Dish|null
	 */
	public function getDishById(int $id, array $columns = []): Dish|null
	{
		return $this->dishDAO->getById($id, $columns);
	}

	/**
	 * @param int $foodId
	 * @return Dish[]
	 */
	public function getDishesByFood(int $foodId)
	{
		return $this->dishDAO->getDishesByFood($foodId);
	}

	/**
	 * @param int $categoryId
	 * @param int $branchId
	 * @param bool $getAll
	 * @return Dish[]
	 */
	public function getDishesByCategory(int $categoryId, int $branchId, bool $getAll): array
	{
		return $this->dishDAO->getDishesByCategory($categoryId, $branchId, $getAll);
	}

	/**
	 * @param int $branchId
	 * @param string|null $from
	 * @param string|null $to
	 * @return array
	 */
	public function getSold(int $branchId, ?string $from, ?string $to): array
	{
		if ((is_null($from)) && (is_null($to))) {
			$from = date('Y-m-d');
			$to = date('Y-m-d');
		}
		return $this->dishDAO->getSold($branchId, $from, $to);
	}

	/**
	 * @param int $id
	 * @param array $data
	 * @return Dish|null
	 */
	public function editDish(int $id, array $data): Dish|null
	{
		return $this->dishDAO->edit($id, $data);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteDish(int $id): bool
	{
		return $this->dishDAO->delete($id);
	}

	/**
	 * @param int $branchId
	 * @return Dish[]
	 */
	public function getCombosByBranch(int $branchId): array
	{
		return $this->dishDAO->getCombosByBranch($branchId);
	}

	/**
	 * @param int $branchId
	 * @return Dish[]
	 */
	public function getSpecialDishesByBranch(int $branchId): array
	{
		return $this->dishDAO->getSpecialDishesByBranch($branchId);
	}

	/**
	 * @param int $comboId
	 * @return Dish[]
	 */
	public function getDishesByCombo(int $comboId): array
	{
		return $this->dishDAO->getDishesByCombo($comboId);
	}

	/**
	 * @param int $comboId
	 * @param array $dishes
	 * @return Dish[]
	 */
	public function addDishToCombo(int $comboId, array $dishes): array
	{
		return $this->dishDAO->addDishToCombo($comboId, $dishes);
	}

	/**
	 * @param int $comboId
	 * @param int $dishId
	 * @return Dish[]
	 */
	public function deleteDishFromCombo(int $comboId, int $dishId): array
	{
		return $this->dishDAO->deleteDishFromCombo($comboId, $dishId);
	}

	/**
	 * @param array $items
	 * @param int $userId
	 * @param int $branchId
	 * @return mixed
	 */
	public function sell(array $items, int $userId, int $branchId): mixed
	{
		$sellDAO = new \App\Application\DAO\SellDAO();
		return $sellDAO->sell($items, $userId, $branchId);
	}
}