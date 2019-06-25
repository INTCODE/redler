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
            if($qty==0){
                $sql="DELETE FROM blm_crontab WHERE quoteId=$quoteId AND productId=$productId AND `type`=$type AND address=$addressId";
            }else{
                $sql="UPDATE blm_crontab
                SET
                    qty='$qty'
                WHERE quoteId=$quoteId AND productId=$productId AND `type`=$type AND address=$addressId ";
            }
     
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

        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');


        $configProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

        if($type==0){
           $stock=$StockState->getStockQty($configProduct->getId(), $configProduct->getStore()->getWebsiteId());
            $sql="SELECT qty
            FROM blm_crontab b
       WHERE b.quoteId=$quoteId AND b.productId=$productId AND b.`type`=$type AND b.address=$addressId";
        }else{

            $_children = $configProduct->getTypeInstance()->getUsedProducts($configProduct);

            foreach ($_children as $key => $child) {
                $packageId=$child->getCustomAttribute('package_type')->getValue();
                if($type==$packageId){
                    $stock=$StockState->getStockQty($child->getId(), $child->getStore()->getWebsiteId());
                }
            }

            $sql="SELECT b.qty
            FROM blm_crontab b
            WHERE b.productId=$productId AND b.address=$addressId AND b.`type`=$type AND b.quoteId=$quoteId";
        }



        $result = $connection->fetchAll($sql);



        if(isset($result[0])){
            $arr = array("qty" => $result[0]['qty'], "productId" => $productId,"stock"=>$stock);
            return json_encode($arr);
        }else{
            return json_encode(array("qty" => 0, "productId" => $productId));
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

        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');



    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    $root= $storeManager->getStore()->getBaseUrl();


        $rootPath=$root.'/pub/media/catalog/product';
       // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============AddressCost=============\n".print_r($rootPath, true));
       // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============root=============\n".print_r($root, true));

        $totalCost=null;
        $addressCost=null;
        $totalQty=null;
        $addressQty=null;
        $res=array();
        $addressRes=array();

        // $sql="SELECT *
        // FROM blm_crontab b
        // Where b.quoteId=$quoteId AND b.address=$addressId";
        // $result = $connection->fetchAll($sql);


        $sqlTotal="SELECT *
        FROM blm_crontab b
        Where b.quoteId=$quoteId";
        $result = $connection->fetchAll($sqlTotal);







        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        foreach ($result as $key => $value) {

            $configProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($value['productId']);

            $type=$value['type'];



            if($type!=0){
                $_children = $configProduct->getTypeInstance()->getUsedProducts($configProduct);

                foreach ($_children as $k => $v) {
                    //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============AddressCost=============\n".print_r($v->debug(), true));

                $packageId=$v->getCustomAttribute('package_type')->getValue();

                    if($type==$packageId){
                        $url=$v->getProductUrl();
                        $image=$v->getData('image');
                        $result[$key]['url']=$url;

                        $totalCost+=$v->getPrice() * $value['qty'];
                        $totalQty+= $value['qty'];

                        if($addressId==$value['address']){
                        $res['url']=$url;
                        $res['image']=$rootPath.$image;
                        $res['productId']=$value['productId'];
                        $res['crontab_id']=$value['crontab_id'];
                        $res['name']=$configProduct->getName();
                        $res['price']=$v->getPrice();
                        $res['type']=$value['type'];
                        $res['qty']=$value['qty'];
                        $res['cost']=$v->getPrice();
                        $res['address']=$value['address'];
                        $res['stock']=$StockState->getStockQty($v->getId(), $v->getStore()->getWebsiteId());
                        

                        array_push($addressRes,$res);


                        $result[$key]['image']=$image;
                        $price=$v->getPrice();
                        $qty=$value['qty'];
                        $addressCost+=$price*$qty;
                        $addressQty+=$qty;
                    }
                }
            }
            }else{

            $totalCost+=$configProduct->getPrice() * $value['qty'];
            $totalQty+= $value['qty'];

                if($addressId==$value['address']){
                $url=$configProduct->getProductUrl();
                $image=$configProduct->getImage();
                $result[$key]['url']=$url;
                $result[$key]['image']=$image;

                $res['url']=$url;
                $res['image']=$rootPath.$image;
                $res['productId']=$value['productId'];
                        $res['crontab_id']=$value['crontab_id'];
                        $res['type']=$value['type'];
                $res['name']=$configProduct->getName();
                $res['qty']=$value['qty'];
                $res['price']=$configProduct->getPrice();
                $res['cost']=$configProduct->getPrice();
                $res['address']=$value['address'];
                $res['stock']=$StockState->getStockQty($configProduct->getId(), $configProduct->getStore()->getWebsiteId());


                array_push($addressRes,$res);

                $price=$configProduct->getPrice();
                $qty=$value['qty'];
                $addressCost+=$price*$qty;
                $addressQty+=$qty;
               }
            }
            }

            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============AddressCost=============\n".print_r($addressCost, true));
            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============addressQty=============\n".print_r($addressQty, true));
            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============totalQty=============\n".print_r($totalQty, true));
            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============totalCost=============\n".print_r($totalCost, true));
            $ad=array('addressCost'=>$addressCost,'addressQty'=>$addressQty,'totalCost'=>$totalCost,'totalQty'=>$totalQty);

            $array=array('data'=>$addressRes,'TotalData'=>$ad);


         //   file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============addressQty=============\n".print_r($array, true));


            if($array){
                return json_encode($array);
            }else{
                return "[]";
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

        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

        $rootPath  =  $directory->getRoot();
        $rootPath=$rootPath.'/pub/media/catalog/product';
       // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============AddressCost=============\n".print_r($rootPath, true));

        $totalCost=null;
        $addressCost=null;
        $totalQty=null;
        $addressQty=null;
        $res=array();
        $addressRes=array();

        // $sql="SELECT *
        // FROM blm_crontab b
        // Where b.quoteId=$quoteId AND b.address=$addressId";
        // $result = $connection->fetchAll($sql);


        $sqlTotal="SELECT *
        FROM blm_crontab b
        Where b.quoteId=$quoteId";
        $result = $connection->fetchAll($sqlTotal);



          
  
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        foreach ($result as $key => $value) {

            $configProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($value['productId']);

            $type=$value['type'];

            if($type!=0){
                $_children = $configProduct->getTypeInstance()->getUsedProducts($configProduct);

                foreach ($_children as $k => $v) {
                    //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============AddressCost=============\n".print_r($v->debug(), true));

                $packageId=$v->getCustomAttribute('package_type')->getValue();

                    if($type==$packageId){
                        $url=$v->getProductUrl();
                        $image=$v->getData('image');
                        $result[$key]['url']=$url;

                        $totalCost+=$v->getPrice() * $value['qty'];
                        $totalQty+= $value['qty'];

                       
                        $res['url']=$url;
                        $res['image']=$rootPath.$image;
                        $res['productId']=$value['productId'];
                        $res['name']=$v->getName();
                        $res['type']=$value['type'];
                        $res['qty']=$value['qty'];
                        $res['address']=$value['address'];
                        $res['stock']=$StockState->getStockQty($v->getId(), $v->getStore()->getWebsiteId());

                        array_push($addressRes,$res);


                        $result[$key]['image']=$image;
                        $price=$v->getPrice();
                        $qty=$value['qty'];
                        $addressCost+=$price*$qty;
                        $addressQty+=$qty;
                    
                }
            }
            }else{

            $totalCost+=$configProduct->getPrice() * $value['qty'];
            $totalQty+= $value['qty'];

                $url=$configProduct->getProductUrl();
                $image=$configProduct->getData('image');
                $result[$key]['url']=$url;
                $result[$key]['image']=$image;

                $res['url']=$url;
                $res['image']=$rootPath.$image;
                $res['productId']=$value['productId'];
                $res['type']=$value['type'];
                $res['name']=$configProduct->getName();
                $res['qty']=$value['qty'];
                $res['address']=$value['address'];
                $res['stock']=$StockState->getStockQty($configProduct->getId(), $configProduct->getStore()->getWebsiteId());


                array_push($addressRes,$res);

                $price=$configProduct->getPrice();
                $qty=$value['qty'];
                $addressCost+=$price*$qty;
                $addressQty+=$qty;
               }
            }
            

            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============AddressCost=============\n".print_r($addressCost, true));
            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============addressQty=============\n".print_r($addressQty, true));
            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============totalQty=============\n".print_r($totalQty, true));
            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============totalCost=============\n".print_r($totalCost, true));
            $ad=array('addressCost'=>$addressCost,'addressQty'=>$addressQty,'totalCost'=>$totalCost,'totalQty'=>$totalQty);

            $array=array('data'=>$addressRes,'TotalData'=>$ad);


            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============addressQty=============\n".print_r($array, true));



            if($array){
                return json_encode($array);
            }else{
                return "[]";
            }
     }


                                /**
     * Sum an array of numbers.
     *
     * @api

     * @param string $CartData The array of numbers to sum.
     * @return string The sum of the numbers.
     */
     public function getCartQty($CartData){


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $CartData=json_decode($CartData);
        //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============quoteid=============\n".print_r($CartData, true));

       // $CartData=array('address'=>6,'quoteid'=>28,'quote'=>array(array('productid'=>34,'type'=>21),array('productid'=>34,'type'=>22),array('productid'=>27,'type'=>0)));

        $products=null;
        $types=null;
        $addressid=$CartData->address;
        $quoteid=$CartData->quoteid;
        $tab=array();

        if(empty($CartData->quote)){
            return "[]";
        }
        foreach ($CartData->quote as $key => $value) {

            array_push($tab,array('productId'=>$value->productid,'type'=>$value->type,'qty'=>0,'stock'=>0));
           $products.='(productId='.$value->productid.' AND '.'type='.$value->type.') OR ';
        }
        $products=rtrim($products,' OR ');
       // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============tab=============\n".print_r($tab, true));

        $sql="SELECT q.productId,q.qty,q.type
        FROM blm_crontab q
        WHERE q.quoteId=$quoteid AND q.address=$addressid AND($products)";


        $result = $connection->fetchAll($sql);
       // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============quoteid=============\n".print_r($result, true));

       
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        foreach ($tab as $key => $value) {

            $productId=$value['productId'];
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============getCartQty=============\n".print_r($value, true));
            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============productId=============\n".print_r($productId, true));
            
           if($value['type']==0){
            $result[$key]['stock']=$StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
            if(!isset($result[$key]['productId'])){
                $result[$key]['productId']=$value['productId'];
                $result[$key]['type']=$value['type'];
                $result[$key]['qty']=0;
            }
           
           }else{
           $_children = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($_children as $k => $child) {
                    $packageId=$child->getCustomAttribute('package_type')->getValue();
                    if($packageId==$value['type']){
                        $result[$key]['stock']=$StockState->getStockQty($child->getId(), $child->getStore()->getWebsiteId());
                        if(!isset($result[$key]['productId'])){
                            $result[$key]['productId']=$value['productId'];
                            $result[$key]['type']=$value['type'];
                            $result[$key]['qty']=0;
                        }
                    }
                }
           }
        }

        if($result){

            $output = array_map("unserialize",
            array_unique(array_map("serialize", $result)));
         //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============output=============\n".print_r($output, true));
        // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============result=============\n".print_r($result, true));

        foreach ($output as $key => $value) {
            if($value['qty']>0){
                for ($i=sizeof($output); $i>=0; $i--) { 
                    if(isset($output[$i])){
                        if($value['productId']==$output[$i]['productId']){
                            if($value['type']==$output[$i]['type']){
                                //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============toDelete=============\n".print_r($output[$i], true));
                                unset($output[$i]);
                                break;
                            }
                        }
                    }
                }
            }
            //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============fin=============\n".print_r($output, true));

       // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============result=============\n".print_r($value, true));
        # code...
        }

            return json_encode($output);
        }else{
            return "[]";

        }


     }



}