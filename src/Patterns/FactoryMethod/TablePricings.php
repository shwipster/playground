<?php

namespace App\Patterns\FactoryMethod;

use App\Patterns\FactoryMethod\Products\ProductInterface;
use App\Patterns\FactoryMethod\Products\Table;

class TablePricings extends Pricings
{
    public function factoryMethod(): ProductInterface
    {
        return new Table();
    }
}