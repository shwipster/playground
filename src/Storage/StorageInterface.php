<?php

namespace A24\Storage;

use A24\Storage\Parameters\InsertParameter;
use A24\Storage\Parameters\OrderParameter;
use A24\Storage\Parameters\LimitParameter;
use A24\Storage\Parameters\UpdateParameter;
use A24\Storage\Parameters\WhereParameter;

interface StorageInterface
{
    public function select(string $table, WhereParameter $whereParameter, OrderParameter $orderParameter, LimitParameter $limitParameter): array;

    public function insert(string $table, InsertParameter $insertParameter): int;

    public function delete(string $table, WhereParameter $whereParameter): int;

    public function update(string $table, UpdateParameter $updateParameters, WhereParameter $whereParameter): int;
}