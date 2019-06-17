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
     * @param int $quoteId The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function get($productId, $addressId, $type, $quoteId);


                 /**
     * Sum an array of numbers.
     *
     * @api
     * @param int $productId The array of numbers to sum.
     * @param int $addressId The array of numbers to sum.
     * @param int $type The array of numbers to sum.
     * @param int $quoteId The array of numbers to sum.
     * @param int $qty The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function add($productId,$addressId,$type,$quoteId,$qty);


                      /**
     * Sum an array of numbers.
     *
     * @api

     * @param int $addressId The array of numbers to sum.
     * @param int $quoteId The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function getCartByAddress($addressId,$quoteId);


                           /**
     * Sum an array of numbers.
     *
     * @api

     * @param int $quoteId The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function getCart($quoteId);


    /**
     * @api
     * @param string $CartData.
     * @return string The sum of the numbers.
     */
     public function getCartQty($CartData);

}