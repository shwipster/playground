<?php

namespace A24\Storage\Parameters;

class InsertParameter
{
    private array $fields;
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function getPrepareSQL(): string
    {
        $arr = [];
        foreach ($this->fields as $key => $value) {
            $arr[] = ":insert_{$key}";
        }

        $str = implode(',', $arr);

        if (strlen($str) > 0) {
            $str = "VALUES ({$str})";
        }

        return $str;
    }

    public function getPrepareParams(): array
    {
        $params = [];
        foreach ($this->fields as $key => $value) {
            $params[":insert_{$key}"] = $value;
        }
        return $params;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}