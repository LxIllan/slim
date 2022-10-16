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
	public function getAltered(int $branchId, string $from, string $to, bool $isDeleted): StdClass
	{
		$alteredProduct = new StdClass();;

		$query = <<<SQL
			SELECT altered_product.id, altered_product.date, product.name, altered_product.quantity, altered_product.reason,
				altered_product.new_quantity, altered_product.cost, CONCAT(user.name, ' ', user.last_name) AS cashier
			FROM altered_product
			INNER JOIN product ON product.id = altered_product.product_id
			INNER JOIN user ON user.id = altered_product.user_id
			WHERE product.branch_id = $branchId 
				AND DATE(altered_product.date) BETWEEN '$from' AND '$to'
				AND altered_product.is_deleted = false
			ORDER BY altered_product.date DESC
		SQL;
		
		if ($isDeleted) {
			$query = str_replace('AND altered_product.is_deleted = false', 'AND altered_product.is_deleted = true', $query);
		}

		$result = $this->connection->select($query);

		$alteredProduct->length = $result->num_rows;
		while ($row = $result->fetch_assoc()) {
			$alteredProduct->items[] = $row;
		}
		return $alteredProduct;
	}

	/**
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @param bool $isDeleted
	 * @return StdClass
	 */
	public function getSupplied(int $branchId, string $from, string $to, bool $isDeleted): StdClass
	{
		$suppliedProduct = new StdClass();

		$query = <<<SQL
			SELECT supplied_product.id, supplied_product.date, product.name, supplied_product.quantity, 
				supplied_product.new_quantity, supplied_product.cost, CONCAT(user.name, ' ', user.last_name) AS cashier
			FROM supplied_product
			INNER JOIN product ON product.id = supplied_product.product_id
			INNER JOIN user ON user.id = supplied_product.user_id
			WHERE product.branch_id = $branchId 
				AND DATE(supplied_product.date) BETWEEN '$from' AND '$to'
				AND supplied_product.is_deleted = false
			ORDER BY supplied_product.date DESC
		SQL;
		
		if ($isDeleted) {
			$query = str_replace('AND supplied_product.is_deleted = false', 'AND supplied_product.is_deleted = true', $query);
		}

		$result = $this->connection->select($query);
		$suppliedProduct->length = $result->num_rows;
		while ($row = $result->fetch_assoc()) {
			$suppliedProduct->items[] = $row;
		}
		return $suppliedProduct;
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

		$query = <<<SQL
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
		SQL;

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
		$query = Util::prepareDeleteQuery($id, $this->table);
		return $this->connection->delete($query);
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
				'subject' => "Notificación de: $branch->name",
				'food_name' => $product->name,
				'quantity' => $newQuantity,
				'branch_name' => $branch->name,
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
		$product = $this->getById($productId);
		$newQuantity = $product->quantity + $quantity;
		$cost = $product->cost * $quantity;

		$dataToInsert = [
			"product_id" => $productId,
			"quantity" => $quantity,
			"reason" => $reason,
			"new_quantity" => $newQuantity,
			"cost" => $cost,
			"user_id" => $userId,
			"branch_id" => $branchId
		];

		$this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'altered_product'));

		$dataToUpdate = [
			"quantity" => $newQuantity
		];
		return $this->edit($productId, $dataToUpdate);
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
		$product = $this->getById($productId);
		$newQuantity = $product->quantity + $quantity;
		$cost = $product->cost * $quantity;

		$dataToInsert = [
			"product_id" => $productId,
			"quantity" => $quantity,
			"new_quantity" => $newQuantity,
			"cost" => $cost,
			"user_id" => $userId,
			"branch_id" => $branchId
		];

		$this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'supplied_product'));

		$dataToUpdate = [
			"quantity" => $newQuantity
		];
		return $this->edit($productId, $dataToUpdate);
	}
}
