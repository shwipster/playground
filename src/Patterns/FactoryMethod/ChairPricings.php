<?php

namespace App\Patterns\FactoryMethod;

use App\Patterns\FactoryMethod\Products\Chair;
use App\Patterns\FactoryMethod\Products\ProductInterface;

class ChairPricings extends Pricings
{
    public function factoryMethod(): ProductInterface
    {
        return new Chair();
    }
}