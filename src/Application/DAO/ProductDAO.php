<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helper\Connection;
use App\Application\Helper\Util;
use App\Application\Model\Product;

class ProductDAO
{
    private const TABLE_NAME = 'product';

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
     * @return Product|null
     */
    public function create(array $data) : Product|null
    {
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Product
     */
    public function getById(int $id): Product
    {
        return $this->connection->select("SELECT * FROM product WHERE id = $id")->fetch_object('App\Application\Model\Product');
    }

    /**
     * @param int $branchId
     * @return array
     */
    public function getByBranch(int $branchId): array
    {
        $dishes = [];
        $result = $this->connection->select("SELECT id FROM product WHERE branch_id = $branchId ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Product|null
     */
    public function edit(int $id, array $data): Product|null
    {
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getById($id) : null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $query = Util::prepareDeleteQuery($id, self::TABLE_NAME);
        return $this->connection->delete($query);
    }

    /**
     * @param int $productId
     * @param int $quantity
     * @param int $userId
     * @return Product|null
     */
    public function useProduct(int $productId, int $quantity, int $userId): Product|null
    {
        $product = $this->getById($productId);

        $newQuantity = $product->quantity - $quantity;

        $dataToUpdateProduct = [
            "quantity" => $newQuantity
        ];

        $product = $this->edit($productId, $dataToUpdateProduct);

        if (($newQuantity <= $product->quantity_notif) && ($product->is_notif_sent == 0)) {
            $branchController = new \App\Application\Controller\BranchController();
            $branch = $branchController->getById($product->branch_id);
            $data = [
                "food_name" => $product->name,
                "quantity" => $newQuantity,
                "branch_name" => $branch->name,
                "branch_location" => $branch->location,
                "email" => $branch->email
            ];
            if (Util::sendNotificationToAdmin($data)) {
                $dataToUpdateProduct = [
                    "is_notif_sent" => true
                ];
                $product = $this->edit($productId, $dataToUpdateProduct);
            }
        }

        $dataToInsertToUsedProduct = [
            "product_id" => $productId,
            "quantity" => $quantity,
            "user_id" => $userId,
            "branch_id" => $product->branch_id
        ];

        $query = Util::prepareInsertQuery($dataToInsertToUsedProduct, 'used_product');
        $this->connection->insert($query);

        return $product;
    }
}