<?php

namespace Amitshree\Customer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LogInRedirect implements \Magento\Framework\Event\ObserverInterface {
    protected $_ResponseFactory;
    protected $_url;
    protected $_session;

    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\ResponseFactory $ResponseFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->_session = $session;
        $this->_ResponseFactory = $ResponseFactory;
        $this->_url = $url;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $isCustomerLoggedIn = $this->_session->isLoggedIn();
         if($isCustomerLoggedIn) {
             $event = $observer->getEvent();
            //  $customRedirectUrl = $this->_url->getUrl('index.php');
             $customRedirectUrl = $this->_url->getUrl('.');
             $this->_session->setBeforeAuthUrl($customRedirectUrl);
            return $this;
        }
    }
}