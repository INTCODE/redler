<?php
namespace Blm\AddToCart\Api;
 
interface HelloInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     
     * @return string Greeting message with users name.
     */
    public function name();


            /**
     * Sum an array of numbers.
     *
     * @api
     * @param int $productId The array of numbers to sum.
     * @param int $addressId The array of numbers to sum.
     * @param int $type The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function get($productId,$addressId,$type);
}