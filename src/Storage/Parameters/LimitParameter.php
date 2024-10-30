<?php

namespace A24\Storage\Parameters;

class LimitParameter
{
    private $limit;
    private $offset;

    public function __construct(int $limit = 0, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getPrepareSQL(): string
    {
        if ($this->limit) {
            return "LIMIT {$this->limit} OFFSET {$this->offset}";
        }
        return '';
    }
}