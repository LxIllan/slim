<?php

namespace App\Application\Helper;

use \mysqli;

class Connection
{

    private const SERVER = "82.180.172.52";
    private const USER = "u775772700_pollos";
    private const PASSWORD = "Bv4MROKO6";
    private const DATABASE = "u775772700_pollos";
//    const USUARIO = "u775772700_plrey";
//    const PASSWORD = "pollo4Rey";
//    const BASE_DE_DATOS = "u775772700_plrey";


    private $mysqli;

    function __construct()
    {
        date_default_timezone_set('America/Mexico_City');
        setlocale(LC_MONETARY, 'en_ES');

        $this->mysqli = new mysqli(self::SERVER, self::USER,
                self::PASSWORD, self::DATABASE);
        if ($this->mysqli->connect_errno) {
            echo 'Conexión Fallida : ' . $this->mysqli->connect_error;
            exit();
        }
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