<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Util;
use App\Application\Helpers\EmailTemplate;
use App\Application\Model\Product;
use StdClass;
use Exception;

class ProductDAO extends DAO
{
	/**
	 * @var string $table
	 */
	protected string $table = 'product';

	public function __construct()
	{
		parent::__construct();
	}	

	/**
	 * @param int $branchId
	 * @return array
	 */
	public function getAll(int $branchId): array
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
	 * @param string $table
	 * @return StdClass
	 */
	public function getSuppliedOrAlteredOrUsed(int $branchId, string $from, string $to, bool $isDeleted, string $table): StdClass
	{
		$table = "${table}_product";
		$reason = (str_contains($table, 'altered')) ? "$table.reason," : '';
		$newQty = (str_contains($table, 'used')) ? '' : "$table.new_qty,";
		$cost = (str_contains($table, 'used')) ? '' : "$table.cost,";

		$query = <<<SQL
			SELECT $table.id, $table.date, product.name, $table.qty, $reason
			$newQty $cost CONCAT(user.name, ' ', user.last_name) AS cashier
			FROM $table
			INNER JOIN product ON product.id = $table.product_id
			INNER JOIN user ON user.id = $table.user_id
			WHERE product.branch_id = $branchId 
				AND DATE($table.date) BETWEEN '$from' AND '$to'
				AND $table.is_deleted = '$isDeleted'
			ORDER BY $table.date DESC
		SQL;

		$std = new StdClass();
		$result = $this->connection->select($query);
		$std->length = $result->num_rows;
		$std->items = $result->fetch_all(MYSQLI_ASSOC);
		$result->free();
		return $std;
	}

	/**
	 * @param int $productId
	 * @param float $qty
	 * @param string $reason
	 * @param int $userId
	 * @param int $branchId
	 * @return Product
	 */
	public function alter(int $productId, float $qty, string $reason, int $userId, int $branchId): Product
	{
		$product = $this->getById($productId);
		$newQty = $product->qty + $qty;
		$cost = $product->cost * $qty;

		$dataToInsert = [
			"product_id" => $productId,
			"qty" => $qty,
			"reason" => $reason,
			"new_qty" => $newQty,
			"cost" => $cost,
			"user_id" => $userId,
			"branch_id" => $branchId
		];

		$this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'altered_product'));

		$dataToUpdate = [
			"qty" => $newQty
		];
		return $this->edit($productId, $dataToUpdate);
	}

	/**
	 * @param int $productId
	 * @param float $qty
	 * @param int $userId
	 * @param int $branchId
	 * @return Product
	 */
	public function supply(int $productId, float $qty, int $userId, int $branchId): Product
	{
		$product = $this->getById($productId);
		$newQty = $product->qty + $qty;
		$cost = $product->cost * $qty;

		$dataToInsert = [
			"product_id" => $productId,
			"qty" => $qty,
			"new_qty" => $newQty,
			"cost" => $cost,
			"user_id" => $userId,
			"branch_id" => $branchId
		];

		$this->connection->insert(Util::prepareInsertQuery($dataToInsert, 'supplied_product'));

		$dataToUpdate = [
			"qty" => $newQty
		];
		return $this->edit($productId, $dataToUpdate);
	}

	/**
	 * @param int $productId
	 * @param int $qty
	 * @param int $userId
	 * @return Product|null
	 * @throws Exception
	 */
	public function use(int $productId, int $qty, int $userId): Product|null
	{
		$product = $this->getById($productId);

		$newQty = $product->qty - $qty;

		$dataToUpdateProduct = [
			"qty" => $newQty
		];

		$product = $this->edit($productId, $dataToUpdateProduct);

		if (($newQty <= $product->qty_notify) && ($product->is_notify_sent == 0)) {
			$branchDAO = new \App\Application\DAO\BranchDAO();
			$branch = $branchDAO->getById(intval($product->branch_id));
			$data = [
				'subject' => "Notificación de: $branch->name",
				'food_name' => $product->name,
				'qty' => $newQty,
				'branch_name' => $branch->name,
				'email' => $branch->admin_email
			];
			if (Util::sendMail($data, EmailTemplate::NOTIFICATION_TO_ADMIN)) {
				$dataToUpdateProduct = ["is_notify_sent" => true];
				$product = $this->edit($productId, $dataToUpdateProduct);
			} else {
				throw new Exception('Error to send email notification to admin.');
			}
		}

		$dataToInsertToUsedProduct = [
			"product_id" => $productId,
			"qty" => $qty,
			"user_id" => $userId,
			"branch_id" => $product->branch_id
		];

		$query = Util::prepareInsertQuery($dataToInsertToUsedProduct, 'used_product');
		$this->connection->insert($query);

		return $product;
	}

	/**
	 * @param int $id
	 * @param string $table
	 * @return Product|null
	 */
	public function cancelSuppliedOrAlteredOrUsed(int $id, string $table): Product|null
	{
		$table = "{$table}_product";

		$suppliedProduct = $this->connection
			->select("SELECT * FROM $table WHERE id = $id")
			->fetch_object();
		
		if (is_null($suppliedProduct)) {
			throw new Exception("Register not found.");
		}
		
		if ($suppliedProduct->is_deleted) {
			throw new Exception("This register has already been canceled.");
		}
		
		$data = [
			"is_deleted" => 1,
			"deleted_at" => date('Y-m-d H:i:s')
		];

		$query = Util::prepareUpdateQuery($id, $data, $table);
		if ($this->connection->update($query)) {
			$product = $this->getById(intval($suppliedProduct->product_id));
			
			$suppliedProduct->qty *= (str_contains($table, 'used')) ? 1 : -1;
			$newQty = $product->qty + $suppliedProduct->qty;

			$dataToUpdateProduct = [
				"qty" => $newQty
			];
			return $this->edit(intval($product->id), $dataToUpdateProduct);
		}
		return null;
	}
}
