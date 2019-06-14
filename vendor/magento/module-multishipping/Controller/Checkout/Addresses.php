<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

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

         $sql2="SELECT *
         FROM quote_item q
         WHERE q.quote_id=$idQuote";

         $re = $connection->fetchAll($sql2); 

         $dbArray=array();
        $result = $connection->fetchAll($sql); 
        foreach ($result as $key => $value) {

            $productId=$value['productId'];
            $address=$value['address'];
            $qty=$value['qty'];
            $type=$value['type'];

            

            foreach ($re as $key => $value) {
             if($value['parent_item_id']){
                 $product = $objectManager->create('Magento\Catalog\Model\Product')->load($value['product_id']);
                 $packageId=$product->getCustomAttribute('package_type')->getValue();
                 if($packageId==$type){
                     $product_ship=array('qty'=>$qty,'address'=>$address);
                     $ship_elem = array($value['parent_item_id'] => $product_ship);
                 
                 }
             }
            }
            array_push($dbArray,$ship_elem);
         }

        if(isset($_SESSION["curr"] ))
        $tab=$_SESSION["curr"] ;
        if(isset($_SESSION["set"] ))
        $isset=$_SESSION["set"];
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========addresses=============\n".print_r($dbArray, true));
      //  file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========addresses=============\n".print_r($isset, true));

        if($isset==1){
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========addresses=============\n".print_r('call', true));
            $AddressPost = $this->_objectManager->get('Magento\Multishipping\Controller\Checkout\AddressesPost');
            $AddressPost->updateAddresses($dbArray);
            unset($_SESSION["set"]);
        }
    


        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
