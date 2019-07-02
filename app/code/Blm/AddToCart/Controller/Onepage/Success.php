<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blm\AddToCart\Controller\Onepage;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Onepage checkout success controller class
 */
class Success extends \Magento\Checkout\Controller\Onepage implements HttpGetActionInterface
{
    /**
     * Order success action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }


        $session->clearQuote();

        //dodawanie koszyka//
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $cartManagementInterface=$objectManager->create('Magento\Quote\Api\CartManagementInterface');
        $cartRepositoryInterface=$objectManager->create('Magento\Quote\Api\CartRepositoryInterface');

        $customerSession = $objectManager->get('Magento\Customer\Model\Session');

        $cartId = $cartManagementInterface->createEmptyCart();

        $quote = $cartRepositoryInterface->get($cartId);

   

        $customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');

        $customer= $customerRepository->getById($customerSession->getCustomerId());

        $quote->setCurrency();
        $quote->assignCustomer($customer);
        $quote->save();
        //$session->setQuote($quote);

        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n============addressQty=============\n".print_r('poszło', true));


        //dodawanie koszyka//


        //@todo: Refactor it to match CQRS
        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            [
                'order_ids' => [$session->getLastOrderId()],
                'order' => $session->getLastRealOrder()
            ]
        );
        return $resultPage;
    }
}
