<?php

namespace Admin\OneKDigital\Storage;

use mysqli;

class MySQLStorage implements StorageInterface
{
    private ?mysqli $connection = null;
    private static ?self $instance = null;

    private function __construct(private $config = null){

        if ($config===null) {
            $this->config['host'] = $_ENV["SQL_HOST"];
            $this->config['user'] = $_ENV["SQL_USER"];
            $this->config['password'] = $_ENV["SQL_PASSWORD"];
            $this->config['database'] = $_ENV["SQL_DATABASE"];
        }
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function getConnection(): mysqli
    {
        if (!$this->connection) {
            $this->connection = new mysqli(
                $this->config['host'],
                $this->config['user'],
                $this->config['password'],
                $this->config['database']
            );
            if ($this->connection->connect_errno) {
                printf("Connect failed: %s<br />", $this->connection->connect_errno);
                exit();
            }
        }

        return $this->connection;
    }

    public function select(string $table, array $fieldFilter): array
    {
        $connection = $this->getConnection();

        $qParts = [];
        foreach ($fieldFilter as $key => $value) {
            $qParts[] = sprintf("%s=?", $key);
        }
        $q = implode(' AND ', $qParts);

        $stmt = $connection->prepare("SELECT * FROM $table WHERE $q");
        $stmt->bind_param(str_repeat('s', count($fieldFilter)), ...array_values($fieldFilter));
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];
        while ( $row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function insert(string $table, array $insertFields): int
    {
        $qFields = implode(',',array_keys($insertFields));
        $qValues = implode(',',array_fill(0, count($insertFields), '?'));

        $connection = $this->getConnection();
        $stmt = $connection->prepare("INSERT INTO $table($qFields) values($qValues)");
        $stmt->bind_param(str_repeat('s', count($insertFields)), ...array_values($insertFields));
        $stmt->execute();

        return $connection->insert_id;
    }


    public function delete(string $table, array $filterFields): int
    {
        $connection = $this->getConnection();

        $qParts = [];
        foreach ($filterFields as $key => $value) {
            $qParts[] = sprintf("%s=?", $key);
        }
        $q = implode(' AND ', $qParts);

        $stmt = $connection->prepare("DELETE FROM $table WHERE $q");
        $stmt->bind_param(str_repeat('s', count($filterFields)), ...array_values($filterFields));
        $stmt->execute();

        return $connection->affected_rows;
    }

    public function update(string $table, array $filterFields, $updateFields): int
    {
        $connection = $this->getConnection();
        $stmt = $connection->prepare("UPDATE $table SET name=?, phone=?, email=?, address=? WHERE user_id=? AND id=?");
        $stmt->bind_param("ssssii",
            $updateFields['name'],
            $updateFields['phone'],
            $updateFields['email'],
            $updateFields['address'],
            $filterFields['user_id'],
            $filterFields['id']
        );
        $stmt->execute();

        return $connection->affected_rows;
    }
}