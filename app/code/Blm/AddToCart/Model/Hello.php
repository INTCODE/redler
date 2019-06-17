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
     * @param int $qty The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function add($productId,$addressId,$type,$quoteId,$qty){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);

        $quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
        $q = $quoteFactory->create()->load($quoteId);
           //  file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($q->getAllItems(), true));
        foreach ($q->getAllItems() as $key => $value) {

            if($type!=0){
            if($value->getParentItemId()){
                $product = $objectManager->get('Magento\Catalog\Model\Product')->load($value->getProductId());
                $packageId=$product->getCustomAttribute('package_type')->getValue();
                if($packageId==$type){

       
                 $itemChenge=$q->getItemById($value->getParentItemId());
                 $itemChenge->setQty($qty);
                 $itemChenge->save();
                    file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($itemChenge->debug(), true));
                //     file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($itemChenge, true));
                //     file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($value->getParentItemId()), true));
               }
                
            }
        }elseif($type==0){
            if(!$value->getParentItemId() && $value->getProductType()=='simple'){
                if($value->getProductId()==$productId){
                    $itemChenge=$q->getItemById($value->getId());
                    $itemChenge->setQty($qty);
                    $itemChenge->save();
                    file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($value->getId(), true));

                }
                // $itemChenge=$q->getItemById($value->getParentItemId());
                // $itemChenge->setQty($qty);
                // $itemChenge->save();

            }

        }
            
        }

      //  return $product->getId();

        $sql="SELECT b.crontab_id
        FROM blm_crontab b
        WHERE b.productId= $productId AND b.address=$addressId AND b.`type`=$type AND b.quoteId=$quoteId";

        $result = $connection->fetchAll($sql);

        if(isset($result[0])){
            $sql="UPDATE blm_crontab
            SET
                qty='$qty'
            WHERE quoteId=$quoteId AND productId=$productId AND `type`=$type AND address=$addressId ";
        }else{
            $sql="INSERT INTO blm_crontab
            (quoteId, productId, `type`, qty, address)
            VALUES ('$quoteId', '$productId', '$type', '$qty', '$addressId')";
            
        }
     // $connection->query($sql);
           if($connection->query($sql)){
            $lastInsertId = $connection->lastInsertId();
               if(isset($lastInsertId) && $lastInsertId!=0){
                return $lastInsertId;
               }else{
            return 'zaktualizowano';       
               }
           }else{
            return 'error';
           }
        
      
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
     public function get($productId, $addressId, $type, $quoteId){

       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        if($type==0){
    
            $sql="SELECT qty
            FROM blm_crontab b
       WHERE b.quoteId=$quoteId AND b.productId=$productId AND b.`type`=$type AND b.address=$addressId";
        }else{
            $sql="SELECT b.qty
            FROM blm_crontab b
            WHERE b.productId= $productId AND b.address=$addressId AND b.`type`=$type AND b.quoteId=$quoteId";
        }



        $result = $connection->fetchAll($sql);
        
        
        
        if(isset($result[0])){
            return json_encode($result[0]);
        }else{
            return json_encode(array("qty" => 0));
        }

        //return $productId.",".$quoteId.",".$type;

    }


                      /**
     * Sum an array of numbers.
     *
     * @api

     * @param int $addressId The array of numbers to sum.
     * @param int $quoteId The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function getCartByAddress($addressId,$quoteId){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        $sql="SELECT *
        FROM blm_crontab b
        Where b.quoteId=$quoteId AND b.address=$addressId";
        $result = $connection->fetchAll($sql);
        if($result){
            return json_encode($result);

        }else{
            return 'not found';
        }



     }

    /**
     * Sum an array of numbers.
     *
     * @api

     * @param int $quoteId The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function getCart($quoteId){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        $sql="SELECT *
        FROM blm_crontab b
        Where b.quoteId=$quoteId";
        $result = $connection->fetchAll($sql);

        if($result){
            return json_encode($result);

        }else{
            return 'not found';
        }


     }


     /**
     * @api

     * @param string $CartData
     * @return string The sum of the numbers.
     */
     public function getCartQty($CartData){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $CartData=json_decode($CartData);

        //$CartData=array('address'=>6,'quoteid'=>28,'quote'=>array(array('productid'=>34,'type'=>21),array('productid'=>34,'type'=>22),array('productid'=>27,'type'=>0)));

        $products=null;
        $types=null;
        $addressid=$CartData->address;
        $quoteid=$CartData->quoteid;

        foreach ($CartData->quote as $key => $value) {
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========tab=============\n".print_r($value, true));
           $products.='(productId='.$value->productid.' AND '.'type='.$value->type.') OR ';
        }
        $products=rtrim($products,' OR ');

        $sql="SELECT q.productId,q.qty
        FROM blm_crontab q
        WHERE q.quoteId=$quoteid AND q.address=$addressid AND($products)";
        // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========products=============\n".print_r($products, true));
        // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========addressid=============\n".print_r($addressid, true));
        // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========quoteid=============\n".print_r($quoteid, true));
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========sql=============\n".print_r($sql, true));
        $result = $connection->fetchAll($sql);
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========result=============\n".print_r($result, true));

        if($result){
            return json_encode($result);
        }else{
            return "[]";

        }



        // $sql="SELECT *
        // FROM blm_crontab b
        // Where b.quoteId=$quoteId";
        // $result = $connection->fetchAll($sql);

        // if($result){
        //     return json_encode($result);

        // }else{
        //     return 'not found';
        // }


     }
    
    
}