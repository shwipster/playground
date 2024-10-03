<?php

namespace App\Patterns\FactoryMethod\Products;

class Table implements ProductInterface
{
    public function getPrice(): float
    {
        return 2;
    }

    public function getName(): string
    {
        return 'Table';
    }
}