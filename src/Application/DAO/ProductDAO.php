<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Helpers\Util;
use App\Application\Helpers\EmailTemplate;
use App\Application\Model\Product;
use StdClass;
use Exception;

class ProductDAO
{
    /**
     * @var string $table
     */
    protected string $table = 'product';

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
    public function create(array $data): Product|null
    {
        $query = Util::prepareInsertQuery($data, $this->table);
        return ($this->connection->update($query)) ? $this->getById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Product
     */
    public function getById(int $id): Product
    {
        return $this->connection
            ->select("SELECT * FROM product WHERE id = $id")
            ->fetch_object('App\Application\Model\Product');
    }

    /**
     * @param int $branchId
     * @return array
     */
    public function getByBranch(int $branchId): array
    {
        $dishes = [];
        $result = $this->connection
            ->select("SELECT id FROM product WHERE branch_id = $branchId ORDER BY name");
        while ($row = $result->fetch_assoc()) {
            $dishes[] = $this->getById(intval($row['id']));
        }
        return $dishes;
    }

    /**
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @param bool $isDeleted
     * @return StdClass
     */
    public function getUsed(int $branchId, string $from, string $to, bool $isDeleted): StdClass
    {
        $usedProducts = new StdClass();

        $query = <<<EOF
            SELECT used_product.id, used_product.date, product.name, used_product.quantity,
                CONCAT(user.name, ' ' , user.last_name) AS cashier
            FROM used_product
            INNER JOIN product ON used_product.product_id = product.id
            INNER JOIN user ON used_product.user_id = user.id
            WHERE used_product.branch_id = $branchId
                AND DATE(used_product.date) >= '$from'
                AND DATE(used_product.date) <= '$to'
                AND used_product.is_deleted = false
            ORDER BY date DESC
        EOF;

        if ($isDeleted) {
            $query = str_replace('used_product.is_deleted = false', 'used_product.is_deleted = true', $query);
        }

        $result = $this->connection->select($query);
        $usedProducts->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $usedProducts->items[] = $row;
        }
        return $usedProducts;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Product|null
     */
    public function edit(int $id, array $data): Product|null
    {
        $query = Util::prepareUpdateQuery($id, $data, $this->table);
        return ($this->connection->update($query)) ? $this->getById($id) : null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $data = [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')        
        ];
        $query = Util::prepareUpdateQuery($id, $data, $this->table);        
        return $this->connection->update($query);
    }

    /**
     * @param int $productId
     * @param int $quantity
     * @param int $userId
     * @return Product|null
     * @throws Exception
     */
    public function use(int $productId, int $quantity, int $userId): Product|null
    {
        $product = $this->getById($productId);

        $newQuantity = $product->quantity - $quantity;

        $dataToUpdateProduct = [
            "quantity" => $newQuantity
        ];

        $product = $this->edit($productId, $dataToUpdateProduct);

        if (($newQuantity <= $product->quantity_notif) && ($product->is_notif_sent == 0)) {
            $branchController = new \App\Application\Controllers\BranchController();
            $branch = $branchController->getById(intval($product->branch_id));
            $data = [
                'subject' => "Notificación de: $branch->location",
                'food_name' => $product->name,
                'quantity' => $newQuantity,
                'branch_name' => $branch->name,
                'branch_location' => $branch->location,
                'email' => $branch->admin_email
            ];
            if (Util::sendMail($data, EmailTemplate::NOTIFICATION_TO_ADMIN)) {
                $dataToUpdateProduct = ["is_notif_sent" => true];
                $product = $this->edit($productId, $dataToUpdateProduct);
            } else {
                throw new Exception('Error to send email notification to admin.');
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

    /**
     * @param int $id
     * @return Product|null
     * @throws Exception
     */
    public function disuse(int $id): Product|null
    {
        $usedProduct = $this->connection
            ->select("SELECT * FROM used_product WHERE id = $id")
            ->fetch_object();
        
        if ($usedProduct->is_deleted) {
            throw new Exception('The product is already disused.');
        }

        $data = [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        
        $query = Util::prepareUpdateQuery($id, $data, 'used_product');
        if ($this->connection->update($query)) {
            $result = $this->connection
                ->select("SELECT product_id, quantity FROM used_product WHERE id = $id")
                ->fetch_assoc();

            $productId = intval($result['product_id']);
            $quantity = intval($result['quantity']);

            $product = $this->getById(intval($result['product_id']));
                
            $newQuantity = $product->quantity + $quantity;

            $dataToUpdateProduct = [
                "quantity" => $newQuantity
            ];

            $product = $this->edit($productId, $dataToUpdateProduct);
            return $product;
        }        
        return null;
    }
}
