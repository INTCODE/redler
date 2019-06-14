<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

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


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/checkout_address/newShipping');
            return;
        }
        try {
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($this->getRequest()->getParams(), true));

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
                foreach ($result as $key => $value) {
                    $productId=$value['productId'];
                    $address=$value['address'];
                    $qty=$value['qty'];
                    
                 $sql="SELECT q.item_id
                 FROM quote_item q
                 WHERE q.quote_id=$idQuote AND q.product_id=$productId";
 
                 $result = $connection->fetchAll($sql); 
               $product_ship=array('qty'=>$qty,'address'=>$address);
           
               if(isset($result[0]['item_id']))
                 $ship_elem = array($result[0]['item_id'] => $product_ship);
                 array_push($dbArray,$ship_elem);
 
                }


            
                file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($_SESSION["curr"], true));
                //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($this->_getCheckout()->debug(), true));

                $this->_getCheckout()->setShippingItemsInformation($dbArray);
                file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============function=============\n".print_r($this->_getCheckout()->debug(), true));

            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
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
            $this->_redirect('*/*/addresses');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Data saving problem'));
            $this->_redirect('*/*/addresses');
        }

    }

}
