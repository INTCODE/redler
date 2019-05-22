<?php
namespace Amitshree\Customer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
// use Magento\Customer\Model\Customer;

class ProductObserver implements ObserverInterface
{
    protected $customerSession;
    public function __construct(CustomerSession $customerSession) {
    $this->customerSession = $customerSession;
    }

        public function execute(\Magento\Framework\Event\Observer $observer) {
                /*
                    $isAccountAccepted = $this->_session->getCustomer()->getCustomAttribute('approve_account')->getvalue();
                    $layout = $observer->getEvent()->getLayout();
                    if($isAccountAccepted != 1) {
                        $layout->getUpdate()->addHandle('blocked_product_view');
                        $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($item['entity_id']);
                            $item[$this->getData('name')] = $customer->getData('approve_account');
                    }

                    
                */
               // var_dump($this ->customerSession->getCustomerData());
               // die();
               // $product = $observer->getEvent()->getProduct();
                //$product->setCanShowPrice(false); 
    }
}