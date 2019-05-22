<?php 
namespace Amitshree\Customer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;

class QuoteObserver implements ObserverInterface
{
    protected $customerSession;
    public function __construct(CustomerSession $customerSession) {
    $this->customerSession = $customerSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
       // if (!$this->_helper->isAvailableAddToCart()) {
        //throw new LocalizedException(__('You can not add products to cart.')); 
        //}
    }
}
