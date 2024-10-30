<?php

namespace A24\Storage;

use A24\Storage\Parameters\InsertParameter;
use A24\Storage\Parameters\OrderParameter;
use A24\Storage\Parameters\LimitParameter;
use A24\Storage\Parameters\UpdateParameter;
use A24\Storage\Parameters\WhereParameter;
use PDO;

class PDOStorage implements StorageInterface
{
    private ?PDO $connection = null;
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getConnection(): PDO
    {
        if (!$this->connection) {
            //NB my_link checks if reconnect needed but its interval is 30 seconds.
            $this->connection = my_link();
        }

        return $this->connection;
    }

    public function select(string $table, WhereParameter $whereParameter, OrderParameter $orderParameter, LimitParameter $limitParameter): array
    {
        $connection = $this->getConnection();

        $whereSQL = $whereParameter->getPrepareSQL();
        $whereParams = $whereParameter->getPrepareParams();

        $orderSQL = $orderParameter->getPrepareSQL();
        $limitSQL = $limitParameter->getPrepareSQL();

        $query = "SELECT * FROM $table {$whereSQL} {$orderSQL} {$limitSQL}";
        $stmt = $connection->prepare($query);

        foreach ($whereParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt =  my_query($stmt, "on");
        return my_fetch_all($stmt);
    }

    public function insert(string $table, InsertParameter $insertParameter): int
    {

        $connection = $this->getConnection();

        $insertSQL = $insertParameter->getPrepareSQL();
        $insertParams = $insertParameter->getPrepareParams();
        $insertFields = $insertParameter->getFields();

        $queryFields = implode(',',array_keys($insertFields));

        $query = "INSERT INTO $table ($queryFields) {$insertSQL}";
        $stmt = $connection->prepare($query);

        foreach ($insertParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        my_query($stmt);
        return $connection->lastInsertId();
    }

    public function delete(string $table, WhereParameter $whereParameter): int
    {
        $connection = $this->getConnection();

        $whereSQL = $whereParameter->getPrepareSQL();
        $whereParams = $whereParameter->getPrepareParams();

        $stmt = $connection->prepare("DELETE FROM $table WHERE {$whereSQL}");
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt =  my_query($stmt);
        return $stmt->rowCount();
    }

    public function update(string $table, UpdateParameter $updateParameters, WhereParameter $whereParameter): int
    {
        $connection = $this->getConnection();

        $updateSQL = $updateParameters->getPrepareSQL();
        $updateParams = $updateParameters->getPrepareParams();

        $whereSQL = $whereParameter->getPrepareSQL();
        $whereParams = $whereParameter->getPrepareParams();

        $stmt = $connection->prepare("UPDATE $table SET {$updateSQL} {$whereSQL}");

        foreach ($updateParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt = my_query($stmt);
        return $stmt->rowCount();
    }
}