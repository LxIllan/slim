<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Product;
use App\Application\DAO\ProductDAO;
use stdClass;
class ProductController
{
	/**
	 * @var ProductDAO
	 */
	private ProductDAO $productDAO;

	public function __construct()
	{
		$this->productDAO = new ProductDAO();
	}

	/**
	 * @param array $data
	 * @return Product|null
	 */
	public function create(array $data): Product|null
	{
		return $this->productDAO->create($data);
	}

	/**
	 * @param int $id
	 * @return Product|null
	 */
	public function getById(int $id): Product|null
	{
		return $this->productDAO->getById($id);
	}

	/**
	 * @param int $branchId
	 * @return array
	 */
	public function getByBranch(int $branchId)
	{
		return $this->productDAO->getByBranch($branchId);
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
		return $this->productDAO->getAltered($branchId, $from, $to, $isDeleted);
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
		return $this->productDAO->getSupplied($branchId, $from, $to, $isDeleted);
	}

	/**
	 * @param int $branchId
	 * @param string|null $from
	 * @param string|null $to
	 * @param bool $isDeleted
	 * @return StdClass
	 */
	public function getUsed(int $branchId, ?string $from, ?string $to, bool $isDeleted = false): StdClass
	{
		if ((is_null($from)) && (is_null($to))) {
			$from = date('Y-m-d', strtotime("this week"));
			$to = date('Y-m-d', strtotime($from . "next Sunday"));
		}
		return $this->productDAO->getUsed($branchId, $from, $to, $isDeleted);
	}

	/**
	 * @param int $id
	 * @param array $data
	 * @return Product|null
	 */
	public function edit(int $id, array $data): Product|null
	{
		return $this->productDAO->edit($id, $data);
	}    

	/**
	 * @param int $productId
	 * @param int $quantity
	 * @param int $userId
	 * @param int $branchId
	 * @return Product|null
	 */
	public function use(int $productId, int $quantity, int $userId): Product|null
	{
		return $this->productDAO->use($productId, $quantity, $userId);
	}

	/**
	 * @param int $id
	 * @return Product|null
	 */
	public function disuse(int $id): Product|null
	{
		return $this->productDAO->disuse($id);
	}

	/**
	 * @param int $productId
	 * @param float $quantity
	 * @param int $userId
	 * @param int $branchId
	 * @return Product
	 */
	public function supply(int $productId, float $quantity, int $userId, int $branchId): Product
	{
		return $this->productDAO->supply($productId, $quantity, $userId, $branchId);
	}

	/**
	 * @param int $productId
	 * @param float $quantity
	 * @param string $reason
	 * @param int $userId
	 * @param int $branchId
	 * @return Product
	 */
	public function alter(int $productId, float $quantity, string $reason, int $userId, int $branchId): Product
	{
		return $this->productDAO->alter($productId, $quantity, $reason, $userId, $branchId);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		return $this->productDAO->delete($id);
	}
}
