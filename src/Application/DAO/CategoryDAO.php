<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Model\Category;

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
	public function getAll(): array
	{
		$categories = [];
		$result = $this->connection->select("SELECT id FROM $this->table ORDER BY category");
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
		$dishDAO = new \App\Application\DAO\DishDAO();
		$categories = $this->getAll();
		foreach ($categories as $category) {
			$category->dishes = $dishDAO->getDishesByCategory(intval($category->id), $branchId, $getAll);
		}
		return $categories;
	}
}
