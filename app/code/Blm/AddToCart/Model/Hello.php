<?php
namespace Blm\AddToCart\Model;
use Blm\AddToCart\Api\HelloInterface;
 
class Hello implements HelloInterface
{
    /**
     * Returns greeting message to user
     *
     * @api

     * @return string Greeting message with users name.
     */
    public function name() {
        return "Hello, ";
    }

            /**
     * Sum an array of numbers.
     *
     * @api
     * @param int $productId The array of numbers to sum.
     * @param int $quoteId The array of numbers to sum.
     * @param int $type The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function get($productId,$quoteId,$type){
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        $sql="SELECT b.qty
        FROM blm_crontab b
        WHERE b.productId= $productId AND b.quoteId=$quoteId AND b.`type`=$type";

        $result = $connection->fetchAll($sql);

        return json_encode($result);
        

    }
}