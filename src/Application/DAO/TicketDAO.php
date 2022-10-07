<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use StdClass;

class TicketDAO
{
    /**
     * @var Connection
     */
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection();
    }
    
    /**
     * @param int $branchId
     * @param string $from
     * @param string $to     
     * @return StdClass
     */
    public function getTickets(int $branchId, string $from, string $to): StdClass
    {
        $tickets = new StdClass();
        $tickets->amount = $this->getSumFromTable('amount', 'expense', $branchId, $from, $to);

        if ($tickets->amount == 0) {
            $tickets->length = 0;
            $tickets->expenses = [];
            return $tickets;
        }

        $query = <<<EOF
            SELECT ticket.id, ticket.ticket_number, ticket.date, CONCAT(user.name, ' ' ,user.last_name) AS cashier
            FROM ticket
            JOIN user ON ticket.user_id = user.id
            WHERE ticket.branch_id = 1
            ORDER BY date DESC;
        EOF;

        $result = $this->connection->select($query);
        $tickets->length = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $tickets->items[] = $row;
        }
        return $tickets;
    } 

    /**
     * @param string $column
     * @param string $table
     * @param int $branchId
     * @param string $from
     * @param string $to
     * @return float
     */
    private function getSumFromTable(string $column, string $table, int $branchId, string $from, string $to): float
    {
        $query = <<<EOF
            SELECT SUM($column) 
            FROM $table 
            WHERE DATE(date) >= '$from' 
              AND DATE(date) <= '$to' 
              AND branch_id = $branchId
        EOF;
        $row = $this->connection->select($query)->fetch_array();
        return floatval($row[0]);
    }
}
