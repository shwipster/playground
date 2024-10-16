<?php

namespace App\Patterns\FactoryMethod;

use App\Patterns\FactoryMethod\Products\ProductInterface;

abstract class Pricings
{
    public function __construct(){}

    /**
     * Note that the Creator may also provide some default implementation of the
     * factory method.
     */
    abstract public function factoryMethod(): ProductInterface;


    /**
     * Also note that, despite its name, the Creator's primary responsibility is
     * not creating products. Usually, it contains some core business logic that
     * relies on Product objects, returned by the factory method. Subclasses can
     * indirectly change that business logic by overriding the factory method
     * and returning a different type of product from it.
     */
    public function normalPrice(): string
    {
        // Call the factory method to create a Product object.
        $product = $this->factoryMethod();
        // Now, use the product.
        $result = "Price for product {$product->getName()} is {$product->getPrice()}";

        return $result;
    }
}