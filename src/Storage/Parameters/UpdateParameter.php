<?php

namespace A24\Storage\Parameters;

class UpdateParameter
{
    private array $fields;

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function getPrepareSQL(): string
    {
        $ret = [];
        foreach ($this->fields as $key => $value) {
            $ret[] = "{$key}=:update_{$key}";
        }
        return implode(',', $ret);
    }

    public function getPrepareParams(): array
    {
        $params = [];
        foreach ($this->fields as $key => $value) {
            $params[":update_{$key}"] = $value;
        }
        return $params;
    }
}