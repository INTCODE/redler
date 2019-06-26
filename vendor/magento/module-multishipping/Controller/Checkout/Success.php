<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

/**
 * Multishipping checkout success controller.
 */
class Success extends Action
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var Multishipping
     */
    private $multishipping;

    /**
     * @param Context $context
     * @param State $state
     * @param Multishipping $multishipping
     */
    public function __construct(
        Context $context,
        State $state,
        Multishipping $multishipping
    ) {
        $this->state = $state;
        $this->multishipping = $multishipping;

        parent::__construct($context);
    }

    /**
     * Multishipping checkout success page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->state->getCompleteStep(State::STEP_OVERVIEW)) {
            $this->_redirect('*/*/addresses');
            return;
        }

        $this->_view->loadLayout();
        $ids = $this->multishipping->getOrderIds();


        


        $this->_eventManager->dispatch('multishipping_checkout_controller_success_action', ['order_ids' => $ids]);


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


        $this->_view->renderLayout();
    }
}
