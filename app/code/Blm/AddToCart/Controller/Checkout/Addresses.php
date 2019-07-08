<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blm\AddToCart\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class Addresses extends \Magento\Multishipping\Controller\Checkout implements HttpGetActionInterface
{
    /**
     * Multishipping checkout select address page
     *
     * @return void
     */
    public function execute()
    {
        // If customer do not have addresses
        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/checkout_address/newShipping');
            return;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $this->_getState()->unsCompleteStep(State::STEP_SHIPPING);

        $this->_getState()->setActiveStep(State::STEP_SELECT_ADDRESSES);
        if (!$this->_getCheckout()->validateMinimumAmount()) {

            $message = $this->_getCheckout()->getMinimumAmountDescription();
            $this->messageManager->addNotice($message);
        }

        $isset=0;

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $idQuote=$cart->getQuote()->getId();

        $sql="SELECT *
        FROM blm_crontab b
        WHERE b.quoteId=$idQuote";

         $dbArray=array();
        $result = $connection->fetchAll($sql); 
        foreach ($result as $key => $value) {

            $productId=$value['productId'];
            $address=$value['address'];
            $qty=$value['qty'];
            $type=$value['type'];

            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

            $configProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);


            if($type!=0){
                $_children = $configProduct->getTypeInstance()->getUsedProducts($configProduct);
                //$quoteID = $this->cart->getQuote()->getId();
                foreach ($_children as $child){
                 $packageId=$child->getCustomAttribute('package_type')->getValue();
                 if($packageId==$type){
                     $children = $objectManager->create('Magento\Catalog\Model\Product')->load($child->getId());
                     //$item= $items->getItemByProduct($children);

                     $childID=$children->getId();
    
                     $getItemID="SELECT *
                     FROM quote_item
                     WHERE quote_id=$idQuote AND product_id=$childID";
                      $itemIDres = $connection->fetchAll($getItemID);
                      if($itemIDres){
                        $ItemID=$itemIDres[0]['parent_item_id'];
                        
                      $product_ship=array('qty'=>$qty,'address'=>$address);
                      $ship_elem = array($ItemID => $product_ship);
                      }else{
                        $ItemID=0;
                      }
                 }
                }
            }else{

                $getItemID="SELECT *
                     FROM quote_item
                     WHERE quote_id=$idQuote AND product_id=$productId";

                $itemIDres = $connection->fetchAll($getItemID);
                $ItemID=$itemIDres[0]['item_id'];
                $product_ship=array('qty'=>$qty,'address'=>$address);
                $ship_elem = array($ItemID => $product_ship);
            }

            if(isset($ship_elem)){
             array_push($dbArray,$ship_elem);
          

            }
         }

        if(isset($_SESSION["curr"] ))
        $tab=$_SESSION["curr"] ;
        if(isset($_SESSION["set"] ))
        $isset=$_SESSION["set"];

      $AddressPost = $this->_objectManager->get('Magento\Multishipping\Controller\Checkout\AddressesPost');
      $AddressPost->updateAddresses($dbArray);


        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
