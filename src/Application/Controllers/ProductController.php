<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Product;
use App\Application\DAO\ProductDAO;

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
     * @param int $id
     * @param array $data
     * @return Product|null
     */
    public function edit(int $id, array $data): Product|null
    {
        return $this->productDAO->edit($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->productDAO->delete($id);
    }

    /**
     * @param int $productId
     * @param int $quantity
     * @param int $userId
     * @param int $branchId
     * @return Product|null
     */
    public function useProduct(int $productId, int $quantity, int $userId): Product|null
    {
        return $this->productDAO->useProduct($productId, $quantity, $userId);
    }
}