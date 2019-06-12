<?php
namespace Blm\AddToCart\Api;
 
interface CartManagementInterface {
    /**
     * Updates the specified cart with the specified products.
     *
     * @api
     * @param int $cartId
     * @param \Blm\AddToCart\Api\CartProductInformationInterface[] $products
     * @return boolean
     */
    public function updateCart($cartId, $products = null);
}