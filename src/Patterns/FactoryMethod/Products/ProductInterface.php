<?php

namespace App\Patterns\FactoryMethod\Products;

interface ProductInterface
{
    public function getPrice(): float;

    public function getName(): string;
}