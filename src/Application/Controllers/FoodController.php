<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Food;
use App\Application\DAO\FoodDAO;
use App\Application\Controllers\DishController;
use Exception;
use StdClass;
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
	public function create(array $data): array
	{
		$food = $this->foodDAO->create($data);
		if ($food == null) {
			throw new Exception("Error creating food.");
		}

		$dishData = [
			"name" => $food->name,
			"price" => $food->cost,
			"food_id" => $food->id,
			"serving" => 1,
			"sell_individually" => 0,
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
	 * @param array $columns
	 * @return Food|null
	 */
	public function getById(int $id, array $columns = []): Food|null
	{
		return $this->foodDAO->getById($id, $columns);
	}

	/**
	 * @param int $branchId
	 * @return Food[]
	 */
	public function getByBranch(int $branchId): array
	{
		return $this->foodDAO->getByBranch($branchId);
	}

	/**
	 * @param int $branchId
	 * @param string|null $from
	 * @param string|null $to
	 * @param bool $isDeleted
	 * @return StdClass
	 */
	public function getAltered(int $branchId, ?string $from, ?string $to, bool $isDeleted): StdClass
	{
		if ((is_null($from)) && (is_null($to))) {
			$from = date('Y-m-d', strtotime("this week"));
			$to = date('Y-m-d', strtotime($from . "next Sunday"));
		}
		return $this->foodDAO->getAltered($branchId, $from, $to, $isDeleted);
	}

	/**
	 * @param int $branchId
	 * @param string|null $from
	 * @param string|null $to
	 * @param bool $isDeleted
	 * @return StdClass
	 */
	public function getSupplied(int $branchId, ?string $from, ?string $to, bool $isDeleted): StdClass
	{
		if ((is_null($from)) && (is_null($to))) {
			$from = date('Y-m-d', strtotime("this week"));
			$to = date('Y-m-d', strtotime($from . "next Sunday"));
		}
		return $this->foodDAO->getSupplied($branchId, $from, $to, $isDeleted);
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
		return $this->foodDAO->getSold($branchId, $from, $to);
	}

	/**
	 * @param int $id
	 * @param array $data
	 * @return Food|null
	 */
	public function edit(int $id, array $data): Food|null
	{
		return $this->foodDAO->edit($id, $data);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		return $this->foodDAO->delete($id);
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
}
