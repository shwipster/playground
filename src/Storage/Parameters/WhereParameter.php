<?php

namespace A24\Storage\Parameters;

class WhereParameter
{
    const PARAM_EQUAL = '=';
    const PARAM_NOT_EQUAL = '<>';
    const PARAM_GREATER = '>';
    const PARAM_LESS = '<';
    const PARAM_LESS_EQUAL = '<=';
    const PARAM_GREATER_EQUAL = '>=';
    const PARAM_LIKE = 'LIKE';

    const OPEN_GROUP = '(';
    const CLOSE_GROUP = ')';


    private array $conditions = [];
    public function __construct(){}

    public function addCondition(string $field, string $operator, string $value): self
    {
        //Convenience hack to allow sequential addCondition calls
        //Adds AND between automatically
        $last = end($this->conditions);
        if ( count($this->conditions) && !isset($last["insert"]) ) {
            $this->and();
        }

        //Add AND if last condition is CLOSE_GROUP
        if (isset($last["insert"]) && $last["insert"] === self::CLOSE_GROUP) {
            $this->and();
        }

        //Same field may be used with different values. Fields in prepre statement must be unigue
        $key = ":where_{$field}";
        $newKey = $key;
        $num = 0;

        while (array_key_exists($newKey, $this->conditions)) {
            $num++;
            $newKey = "{$key}_{$num}";
        }

        $this->conditions[$newKey] = ['field' => $field, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    public function and(): self
    {
        $this->conditions[uniqid()] = ['insert' => "AND"];
        return $this;
    }

    public function or(): self
    {
        $this->conditions[uniqid()] = ['insert' => "OR"];
        return $this;
    }

    public function insert(string $insert): self
    {
        $this->conditions[uniqid()] = ['insert' => $insert];
        return $this;
    }

    public function getPrepareSQL(): string
    {
        $query = '';
        foreach ($this->conditions as $key => $condition) {
            if (isset($condition["insert"])) {
                $query .= "{$condition["insert"]} ";
            } else {
                $query .= "{$condition['field']} {$condition['operator']} {$key} ";
            }
        }

        if (strlen($query) > 0) {
            $query = "WHERE {$query}";
        }
        return $query;
    }

    public function getPrepareParams(): array
    {
        $params = [];
        foreach ($this->conditions as $key => $condition) {
            if (isset($condition["insert"])) {
                continue;
            }

            $value = $condition['value'];
            if ($condition['operator'] === self::PARAM_LIKE) {
                $value = "%{$condition['value']}%";
            }
            $params[$key] = $value;
        }
        return $params;
    }
}