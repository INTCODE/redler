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

        $this->_getState()->unsCompleteStep(State::STEP_SHIPPING);

        $this->_getState()->setActiveStep(State::STEP_SELECT_ADDRESSES);
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $message = $this->_getCheckout()->getMinimumAmountDescription();
            $this->messageManager->addNotice($message);
        }
               file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========addresses=============\n".print_r('call', true));

        $isset=0;

        if(isset($_SESSION["curr"] ))
        $tab=$_SESSION["curr"] ;
        if(isset($_SESSION["set"] ))
        $isset=$_SESSION["set"];
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========addresses=============\n".print_r($tab, true));
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========addresses=============\n".print_r($isset, true));

        if($isset==1){

            $AddressPost = $this->_objectManager->get('Magento\Multishipping\Controller\Checkout\AddressesPost');
            $AddressPost->updateAddresses($tab);
            unset($_SESSION["set"]);
        }
    


        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
