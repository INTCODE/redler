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
     * @param int $addressId The array of numbers to sum.
     * @param int $type The array of numbers to sum.
     * @param int $quoteId The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function get($productId,$addressId,$type,$quoteId){
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        $sql="SELECT b.qty
        FROM blm_crontab b
        WHERE b.productId= $productId AND b.address=$addressId AND b.`type`=$type AND b.quoteId=$quoteId";

        $result = $connection->fetchAll($sql);
        
        if(isset($result[0])){
            return json_encode($result[0]);
        }else{
            return json_encode(array("qty" => 0));
        }

        //return $productId.",".$quoteId.",".$type;

    }
}