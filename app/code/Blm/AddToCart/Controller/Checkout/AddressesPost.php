<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blm\AddToCart\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use MSP\TwoFactorAuth\Model\Alert;

class AddressesPost extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout process posted addresses
     *
     * @return void
     */
    public function execute()
    {

        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============Działa 2=============\n".print_r('x', true));

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/checkout_address/newShipping');
            return;
        }
        try {
            //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($this->getRequest()->getParams(), true));

            if ($this->getRequest()->getParam('continue', false)) {
                $this->_getCheckout()->setCollectRatesFlag(true);
                $this->_getState()->setActiveStep(State::STEP_SHIPPING);
                $this->_getState()->setCompleteStep(State::STEP_SELECT_ADDRESSES);
                $this->_redirect('*/*/shipping');
            } elseif ($this->getRequest()->getParam('new_address')) {
                $this->_redirect('*/checkout_address/newShipping');
            } else {
                $this->_redirect('*/*/addresses');
            }
            if ($shipToInfo = $this->getRequest()->getPost('ship')) {

               
               $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
               $idQuote=$cart->getQuote()->getId();


             file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($idQuote, true));


             $sql="SELECT *
             FROM blm_crontab b
             WHERE b.quoteId=$idQuote";
     
            $dbArray=array();
            $result = $connection->fetchAll($sql); 
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========result=============\n".print_r($result, true));
           
            foreach ($result as $key => $value) {

                $productId=$value['productId'];
                $address=$value['address'];
                $qty=$value['qty'];
                $type=$value['type'];
    
                file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========type=============\n".print_r($type, true));
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========value=============\n".print_r($product->debug(), true));
    
                $configProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
    
    
    
                if($type!=0){
                    $_children = $configProduct->getTypeInstance()->getUsedProducts($configProduct);
                    //$quoteID = $this->cart->getQuote()->getId();
                    foreach ($_children as $child){
                     $packageId=$child->getCustomAttribute('package_type')->getValue();
                     if($packageId==$type){
                         $children = $objectManager->create('Magento\Catalog\Model\Product')->load($child->getId());
                         //$item= $items->getItemByProduct($children);
                         file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========$-children=============\n".print_r($children->debug(), true));
                          //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========cild=============\n".print_r($quoteID, true));
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
        
        
                          file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========cild=============\n".print_r($ItemID, true));
        
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

                file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($dbArray, true));

            
                //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($_SESSION["curr"], true));
                //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($this->_getCheckout()->debug(), true));

                $this->_getCheckout()->setShippingItemsInformation($dbArray);
                //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($this->_getCheckout()->debug(), true));

            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function0=============\n".print_r($e->getMessage(), true));
            $this->_redirect('*/*/addresses');
        } catch (\Exception $e) {
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($e->getMessage(), true));
            $this->messageManager->addException($e, __('Data saving problem'));
            $this->_redirect('*/*/addresses');
        }
    }



    public function updateAddresses($array){


        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($array, true));
        try {
            //$this->_redirect('*/*/shipping');
            
              //  unset($_SESSION["set"]);
                file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($this->_getCheckout()->debug(), true));
                $this->_getCheckout()->setShippingItemsInformation($array);
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            
            $this->messageManager->addError($e->getMessage());
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============error-1=============\n".print_r($e->getMessage(), true));
            $this->_redirect('*/*/addresses');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Data saving problem'));
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============error-2=============\n".print_r($e->getMessage(), true));

            $this->_redirect('*/*/addresses');
        }

    }

}