<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Model\Preference;
use App\Application\Helpers\Util;

class PreferenceDAO
{
    private const TABLE_NAME = 'preference';

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
     * @return Preference|null
     */
    public function create(array $data): Preference|null
    {
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->insert($query)) ? $this->getById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return Preference|null
     */
    public function getById(int $id): Preference|null
    {
        return $this->connection
            ->select("SELECT * FROM preference WHERE id = $id")
            ->fetch_object('App\Application\Model\Preference');
    }

    /**
     * @param string $key
     * @param int $branchId
     * @return Preference
     */
    public function getByKey(string $key, int $branchId): Preference
    {
        return $this->connection
            ->select("SELECT * FROM preference WHERE branch_id = $branchId AND `key` = '$key'")
            ->fetch_object('App\Application\Model\Preference');
    }

    /**
     * @return Preference[]
     */
    public function getPreferences(int $branchId): array
    {
        $preferences = [];
        $result = $this->connection->select("SELECT id FROM preference WHERE branch_id = $branchId");
                
        while ($row = $result->fetch_assoc()) {
            $preferences[] = $this->getById(intval($row['id']));
        }
        return $preferences;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Preference|null
     */
    public function edit(int $id, array $data): Preference|null
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
}
