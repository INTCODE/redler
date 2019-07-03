<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class OverviewPost
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OverviewPost extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface
     */
    protected $agreementsValidator;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator
     * @param SessionManagerInterface $session
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator,
        SessionManagerInterface $session
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
        $this->agreementsValidator = $agreementValidator;
        $this->session = $session;

        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
    }

    /**
     * Overview action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->_forward('backToAddresses');
            return;
        }
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        try {
            if (!$this->agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', [])))) {
                $this->messageManager->addError(
                    __('Please agree to all Terms and Conditions before placing the order.')
                );
                $this->_redirect('*/*/billing');
                return;
            }

            $payment = $this->getRequest()->getPost('payment');
            $paymentInstance = $this->_getCheckout()->getQuote()->getPayment();
            if (isset($payment['cc_number'])) {
                $paymentInstance->setCcNumber($payment['cc_number']);
            }
            if (isset($payment['cc_cid'])) {
                $paymentInstance->setCcCid($payment['cc_cid']);
            }
            $this->_getCheckout()->createOrders();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $Multishipping = $objectManager->get('Magento\Multishipping\Model\Checkout\Type\Multishipping');
            $orderRepository = $objectManager->get('Magento\Sales\Model\OrderRepository');
            $ids = $Multishipping->getOrderIds();
      
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            foreach ($ids as $key => $orderId) {
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========================\n".print_r($orderId, true));
                $order = $orderRepository->get($orderId);
              
                $array=$order->toArray();
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========================\n".print_r($order->debug(), true));
                
                if($array['quote']['aw_use_store_credit']){

                    $amount=-($array['subtotal_incl_tax']+$array['shipping_amount']);
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=============amount============\n".print_r($amount, true));

                    $order->setAwUseStoreCredit(1);
                     $order->setAwStoreCreditAmount($amount);
                     $order->setBaseAwStoreCreditAmount($amount);
                     $order->save();
                        try {
                            $customer_id=$array['customer_id'];
                            $customer_email=$array['customer_email'];
                            $customer_name=$array['customer_firstname'].' '.$array['customer_lastname'];
                            $increment_id=$array['increment_id'];
                            $comment_to_customer='Spent Store Credit on order #'.$increment_id;
                            $comment_to_customer_placeolder='Spent Store Credit on order %order_id';
                            $entity_type=1;
                       
                       $sql="SELECT *
                            FROM aw_sc_summary a
                            WHERE a.customer_id=$customer_id";
    
                            $result = $connection->fetchAll($sql); 
                            $spended=$amount;
                            $newBalance=$result[0]['balance']+$spended;
                            $newSpend=$result[0]['spend']+abs($spended);
                            
                            $insert1="INSERT INTO aw_sc_transaction
                            (customer_id, customer_name, customer_email, comment_to_customer, comment_to_customer_placeholder, balance, current_balance, website_id, balance_update_notified, `type`)
                            VALUES ($customer_id, '$customer_name', '$customer_email', '$comment_to_customer', '$comment_to_customer_placeolder', $spended, $newBalance, 1, 2, 5)";
                            $connection->query($insert1);

                            $insert2="INSERT INTO aw_sc_transaction_entity
                            (entity_type, entity_id, entity_label)
                            VALUES (1, $orderId, '$increment_id')";
                           
                            $connection->query($insert2);

                            $update="UPDATE aw_sc_summary
                            SET
                                balance=$newBalance,
                                spend=$newSpend
                            WHERE customer_id=$customer_id";
    
                            $connection->query($update);
                        } catch (\Throwable $th) {
                     file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n===========result==============\n".print_r($th->getMessage(), true));
          
                        }
              
                }


            }
            


            $this->_getState()->setCompleteStep(State::STEP_OVERVIEW);

            if ($this->session->getAddressErrors()) {
                $this->_getState()->setActiveStep(State::STEP_RESULTS);
                $this->_redirect('*/*/results');
            } else {
                $this->_getState()->setActiveStep(State::STEP_SUCCESS);
                $this->_getCheckout()->getCheckoutSession()->clearQuote();
                $this->_getCheckout()->getCheckoutSession()->setDisplaySuccess(true);
                $this->_redirect('*/*/success');
            }
        } catch (PaymentException $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addError($message);
            }
            $this->_redirect('*/*/billing');
        } catch (\Magento\Checkout\Exception $e) {
            $this->_objectManager->get(
                \Magento\Checkout\Helper\Data::class
            )->sendPaymentFailedEmail(
                $this->_getCheckout()->getQuote(),
                $e->getMessage(),
                'multi-shipping'
            );
            $this->_getCheckout()->getCheckoutSession()->clearQuote();
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/cart');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get(
                \Magento\Checkout\Helper\Data::class
            )->sendPaymentFailedEmail(
                $this->_getCheckout()->getQuote(),
                $e->getMessage(),
                'multi-shipping'
            );
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/billing');
        } catch (\Exception $e) {
            $this->logger->critical($e);
            try {
                $this->_objectManager->get(
                    \Magento\Checkout\Helper\Data::class
                )->sendPaymentFailedEmail(
                    $this->_getCheckout()->getQuote(),
                    $e->getMessage(),
                    'multi-shipping'
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n===========result==============\n".print_r($e->getMessage(), true));

            $this->messageManager->addError(__('Order place error'));
            $this->_redirect('*/*/billing');
        }
    }
}
