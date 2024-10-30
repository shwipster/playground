<?php

namespace A24\Storage\Parameters;

class OrderParameter
{
    const DIR_DESC= 'DESC';
    const DIR_ASC = 'ASC';

    private string $field;
    private string $direction;

    public function __construct(string $field = '', string $direction = self::DIR_DESC)
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    public function getPrepareSQL(): string
    {
        if ($this->field) {
            return "ORDER BY {$this->field} {$this->direction}";
        }
        return '';
    }
}