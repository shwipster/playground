<?php

namespace App\Patterns\FactoryMethod\Products;

class Chair implements ProductInterface
{
    public function getPrice(): float
    {
        return 10;
    }

    public function getName(): string
    {
        return 'Chair';
    }
}