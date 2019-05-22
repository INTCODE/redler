<?php
namespace Amitshree\Customer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;

class CollectionObserver implements ObserverInterface
{
    protected $customerSession;
    public function __construct(CustomerSession $customerSession) {
    $this->customerSession = $customerSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

    } 
}
