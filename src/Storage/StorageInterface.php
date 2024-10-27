<?php

namespace Admin\OneKDigital\Storage;

interface StorageInterface
{
    public function select(string $table, array $fieldFilter): array;

    public function insert(string $table, array $insertFields): int;

    public function delete(string $table, array $filterFields): int;

    public function update(string $table, array $filterFields, $updateFields): int;
}