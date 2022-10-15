<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Model\Ticket;
use App\Application\DAO\TicketDAO;
use stdClass;
class TicketController
{
	/**
	 * @var TicketDAO
	 */
	private TicketDAO $ticketDAO;

	public function __construct()
	{
		$this->ticketDAO = new TicketDAO();
	}

	// /**
	//  * @param array $data
	//  * @return Ticket|null
	//  */
	// public function create(array $data): Ticket|null
	// {
	//     return $this->ticketDAO->create($data);
	// }

	/**
	 * @param int $id
	 * @return Ticket|null
	 */
	public function getById(int $id): Ticket|null
	{
		return $this->ticketDAO->getById($id);
	}

	/**
	 * @param int $branchId
	 * @param string $from
	 * @param string $to     
	 * @return StdClass
	 */
	public function getAll(int $branchId, string $from, string $to): StdClass
	{
		if ((is_null($from)) && (is_null($to))) {
			$from = date('Y-m-d');
			$to = date('Y-m-d');
		}
		return $this->ticketDAO->getAll($branchId, $from, $to);
	}

	/**
	 * @param int $branchId
	 * @return int
	 */
	public function getNextNumber(int $branchId): int
	{
		return $this->ticketDAO->getNextNumber($branchId);
	}
	// /**
	//  * @param int $branchId
	//  * @return array
	//  */
	// public function getByBranch(int $branchId)
	// {
	//     return $this->ticketDAO->getByBranch($branchId);
	// }

	// /**
	//  * @param int $id
	//  * @param array $data
	//  * @return Ticket|null
	//  */
	// public function edit(int $id, array $data): Ticket|null
	// {
	//     return $this->ticketDAO->edit($id, $data);
	// }

	// /**
	//  * @param int $id
	//  * @return bool
	//  */
	// public function delete(int $id): bool
	// {
	//     return $this->ticketDAO->delete($id);
	// }
}
