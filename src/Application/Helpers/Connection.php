<?php

namespace App\Application\Helpers;

use \mysqli;

class Connection
{
    /**
     * @var \mysqli
     */
    private mysqli $mysqli;

    public function __construct()
    {
        date_default_timezone_set('America/Mexico_City');
        setlocale(LC_MONETARY, 'en_ES');

        $this->mysqli = new mysqli($_ENV['HOST_DB'], $_ENV['USER_DB'], $_ENV['PASS_DB'], $_ENV['DATABASE']);
        if ($this->mysqli->connect_errno) {
            echo 'Failed connection : ' . $this->mysqli->connect_error;
            exit();
        }
        $this->mysqli->query("SET time_zone = '-05:00'");
    }

    public function __destruct()
    {
        $this->mysqli->close();
    }

    /**
     * @param string $query
     * @return \mysqli_result|bool
     */
    public function select(string $query): \mysqli_result|bool
    {
        return $this->sentence($query);
    }

    /**
     * @return int
     */
    public function getLastId(): int
    {
        return $this->mysqli->insert_id;
    }

    /**
     * @return int
     */
    public function getConnectionId(): int
    {
        return $this->mysqli->thread_id;
    }

    /**
     * @param string $query
     * @return bool
     */
    public function insert(string $query): bool
    {
        return $this->sentence($query);
    }

    /**
     * @param string $query
     * @return bool
     */
    public function delete(string $query): bool
    {
        return $this->sentence($query);
    }

    /**
     * @param string $query
     * @return bool
     */
    public function update(string $query): bool
    {
        return $this->sentence($query);
    }

    /**
     * @param string $query
     * @return bool|\mysqli_result
     */
    private function sentence(string $query): bool|\mysqli_result
    {
        return $this->mysqli->query($query);
    }
}