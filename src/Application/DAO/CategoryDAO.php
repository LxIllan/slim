<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Model\Category;
use App\Application\Controllers\DishController;

class CategoryDAO extends DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'category';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return Category[]
	 */
	public function getCategories(): array
	{
		$categories = [];
		$result = $this->connection->select("SELECT id FROM $this->table");
		while ($row = $result->fetch_assoc()) {
			$categories[] = $this->getById(intval($row['id']));
		}
		return $categories;
	}

	/**
	 * @param int $branchId
	 * @param bool $getAll
	 * @return Category[]
	 */
	public function getCategoriesWithDishes(int $branchId, bool $getAll): array
	{
		$dishesController = new DishController();
		$categories = $this->getCategories();
		foreach ($categories as $category) {
			$category->dishes = $dishesController->getDishesByCategory(intval($category->id), $branchId, $getAll);
		}
		return $categories;
	}
}
