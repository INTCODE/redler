<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\QuickBooksOnline\Model\Synchronization;

use Magenest\QuickBooksOnline\Model\Client;
use Magenest\QuickBooksOnline\Model\Log;
use Magenest\QuickBooksOnline\Model\Synchronization;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Creditmemo as CreditmemoModel;
use Magento\Framework\Exception\LocalizedException;
use Magenest\QuickBooksOnline\Model\TaxFactory;
use Magento\Sales\Model\OrderFactory;
use Magenest\QuickBooksOnline\Model\Config;
use Magento\Framework\App\Action\Context;

/**
 * Class Creditmemo
 * @package Magenest\QuickBooksOnline\Model\Synchronization
 * @method CreditMemoModel getModel()
 */
class Creditmemo extends Synchronization
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var Customer
     */
    protected $_syncCustomer;

    /**
     * @var Item
     */
    protected $_item;

    /**
     * @var CreditmemoModel
     */
    protected $_creditmemo;

    /**
     * @var PaymentMethods
     */
    protected $_paymentMethods;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var TaxFactory
     */
    protected $tax;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CreditmemoModel\ItemFactory
     */
    protected $itemCreditmemo;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * Creditmemo constructor.
     *
     * @param Client $client
     * @param Log $log
     * @param CreditmemoModel $creditmemo
     * @param Item $item
     * @param Customer $customer
     * @param \Magenest\QuickBooksOnline\Model\PaymentMethodsFactory $paymentMethods
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Psr\Log\LoggerInterface $logger
     * @param TaxFactory $taxFactory
     * @param OrderFactory $orderFactory
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        Client $client,
        Log $log,
        CreditmemoModel $creditmemo,
        Item $item,
        Customer $customer,
        \Magenest\QuickBooksOnline\Model\PaymentMethodsFactory $paymentMethods,
        \Magento\Catalog\Model\ProductFactory $product,
        \Psr\Log\LoggerInterface $logger,
        TaxFactory $taxFactory,
        OrderFactory $orderFactory,
        Config $config,
        Context $context
    ) {
        parent::__construct($client, $log, $context);
        $this->_creditmemo     = $creditmemo;
        $this->_item           = $item;
        $this->_syncCustomer   = $customer;
        $this->_paymentMethods = $paymentMethods;
        $this->tax             = $taxFactory;
        $this->type            = 'creditmemo';
        $this->logger          = $logger;
        $this->_orderFactory   = $orderFactory;
        $this->config          = $config;
        $this->product         = $product;
    }

    /**
     * @param $id
     * @param null $item
     *
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function sync($id, $item = null)
    {
        $registryObject = ObjectManager::getInstance()->get('Magento\Framework\Registry');
        $registryObject->unregister('skip_log');
        try {
            $model = $this->loadByIncrementId($id);
            if ($item != null) {
                $model->setItem($item);
            }

            $invoiceEnabled = $this->config->isEnableByType('invoice');

            $orderId    = $model->getOrderId();
            $modelOrder = ObjectManager::getInstance()->create('Magento\Sales\Model\Order')->load($orderId);
            $invoice    = $this->checkInvoice($modelOrder->getIncrementId());
            if (empty($invoice)) {
                $this->addLog($id, null, __('We can\'t find the Invoice #%1 on QBO to map with this Memo #%2', $modelOrder->getIncrementId(), $id));

            } else {
                $amountReceive = $invoice['TotalAmt'] - $invoice['Balance'];
                if ($this->getShippingAllow() == true) {
                    $amountRefund = $model->getGrandTotal();
                } else $amountRefund = $model->getGrandTotal() - $model->getShippingAmount();
                if ($amountReceive < $amountRefund) {
                    if ($invoiceEnabled == 0) {
                        $this->addLog($id, null, __('You need to update this Invoice #%1 on QuickBooksOnline before credit memo can be synced', $modelOrder->getIncrementId()));
                    } else
                        $this->addLog($id, null, 'Refund amount must be equal or less than invoiced amount. Please sync invoice before credit memo.');
                } else {
                    $checkCredit = $this->checkCreditmemo($id);
                    if (isset($checkCredit['Id'])) {
                        $this->addLog(
                            $id,
                            $checkCredit['Id'],
                            __('This Creditmemo already exists.'),
                            'skip'
                        );
                    } else {
                        if (!$model->getId()) {
                            throw new LocalizedException(__('We can\'t find the Creditmemo #%1', $id));
                        }
                        /**
                         * check the case delete customer before sync their creditmemo
                         */
                        $customerIsGuest = true;
                        if ($modelOrder->getCustomerId()) {
                            $customerCollection = ObjectManager::getInstance()->create('Magento\Customer\Model\ResourceModel\Customer\Collection')->addFieldToFilter('entity_id', $modelOrder->getCustomerId());
                            if (!$customerCollection->getData()) {
                                $customerIsGuest = true;
                            } else $customerIsGuest = false;

                        }

                        $this->setModel($model);
                        $this->prepareParams($customerIsGuest);
                        $params   = $this->getParameter();
                        $response = $this->sendRequest(\Zend_Http_Client::POST, 'creditmemo', $params);
                        $qboId    = @$response['CreditMemo']['Id'];
                        if (!empty($qboId)) {
                            $this->addLog($id, $qboId);
                        }
                        $this->parameter = [];

                        /** Sync memo items when creating new memo */
                        $itemCreditCollection = $item;
                        if (!($itemCreditCollection and $itemCreditCollection[0] instanceof \Magento\Sales\Model\Order\CreditMemo\Item)) {
                            $itemCreditCollection = $this->getModel()->getAllItems();
                        }
                        foreach ($itemCreditCollection as $creditItemModel) {
                            $this->_item->syncBySku($creditItemModel->getSku(), true);
                        }

                        return $qboId;
                    }
                    $this->parameter = [];
                }
            }
        } catch (LocalizedException $e) {
            $this->addLog($id, null, $e->getMessage());
        }
    }

    /**
     * @param $id
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function loadByIncrementId($id)
    {
        $ids = $this->_creditmemo->getCollection()->addFieldToFilter('increment_id', $id)->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->_creditmemo->load(current($ids));
        }

        return $this->_creditmemo;
    }

    /**
     * @param null $customerIsGuest
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function prepareParams($customerIsGuest = null)
    {
        $model      = $this->getModel();
        $modelOrder = $this->_orderFactory->create()->load($this->getModel()->getOrderId());

        $prefix = $this->config->getPrefix('creditmemos');
        $params = [
            'DocNumber'    => $prefix . $model->getIncrementId(),
            'TxnDate'      => $model->getCreatedAt(),
            'TxnTaxDetail' => ['TotalTax' => $model->getTaxAmount()],
            'CustomerRef'  => $this->prepareCustomerId($customerIsGuest),
            'Line'         => $this->prepareLineItems(),
            'TotalAmt'     => $model->getGrandTotal(),
            'BillEmail'    => ['Address' => mb_substr((string)$modelOrder->getCustomerEmail(), 0, 100)],
        ];

        $this->setParameter($params);
        // st Tax
        if ($this->config->getCountry() == 'OTHER' && $model->getTaxAmount() > 0) {
            $this->prepareTax();
        }

        //set billing address
        $this->prepareBillingAddress();

        if ($this->getShippingAllow() == true) {
            $this->prepareShippingAddress();
        }

        //set payment method
        $this->preparePaymentMethod();

        return $this;
    }

    /**
     * Create Tax
     */
    public function prepareTax()
    {
        $params['TxnTaxDetail'] = [
            'TotalTax' => $this->getModel()->getTaxAmount(),
        ];
    }

    /**
     * @param null $customerIsGuest
     *
     * @return array
     * @throws LocalizedException
     */
    public function prepareCustomerId($customerIsGuest = null)
    {
        try {
            $modelOrder = $this->_orderFactory->create()->load($this->getModel()->getOrderId());
            $customerId = $modelOrder->getCustomerId();
            if ($customerId and $customerIsGuest == false) {
                $cusRef = $this->_syncCustomer->sync($customerId, false);
            } else {
                $cusRef = $this->_syncCustomer->syncGuest(
                    $modelOrder->getBillingAddress(),
                    $modelOrder->getShippingAddress()
                );
            }

            return ['value' => $cusRef];
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Can\'t sync customer on Invoice to QBO')
            );
        }
    }

    /**
     * @return void
     */
    public function prepareBillingAddress()
    {
        $billAddress = $this->getModel()->getBillingAddress();
        if ($billAddress !== null) {
            $params['BillAddr'] = $this->getAddress($billAddress);
            $this->setParameter($params);
        }
    }

    /**
     * @return void
     */
    public function prepareShippingAddress()
    {
        $shippingAddress = $this->getModel()->getShippingAddress();
        if ($shippingAddress !== null) {
            $params['ShipAddr'] = $this->getAddress($shippingAddress);
            $this->setParameter($params);
        }
    }

    /**
     * set payment method
     */
    public function preparePaymentMethod()
    {
        $orderData = $this->_orderFactory->create()->load($this->getModel()->getOrderId());
        $code      = $orderData->getPayment()->getMethodInstance()->getCode();


        $paymentMethod = $this->_paymentMethods->create()->load($code, 'payment_code');

        if ($paymentMethod->getId()) {
            $params['PaymentMethodRef'] = [
                'value' => $paymentMethod->getQboId(),
                'name'  => $paymentMethod->getTitle()
            ];
            $this->setParameter($params);
        }
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function prepareLineItems()
    {
        try {
//            $model                = $this->_orderFactory->create()->load($this->getModel()->getOrderId());
            /** @var /Magento/Sales/Model/Order/Creditmemo $creditModel */
            $creditModel          = $this->getModel();
            $itemCreditCollection = $creditModel->getItem();
            if (!($itemCreditCollection and $itemCreditCollection[0] instanceof \Magento\Sales\Model\Order\CreditMemo\Item)) {
                $itemCreditCollection = $this->getModel()->getAllItems();
            }
            $i     = 1;
            $lines = [];

            /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
            $registryObject = ObjectManager::getInstance()->get('Magento\Framework\Registry');
            foreach ($itemCreditCollection as $item) {
                $registryObject->unregister('check_to_syn' . $item->getProductId());
                $qty    = $item->getQty();
                $sku    = $item->getSku();
                $total  = $item->getRowTotal();
                $tax    = $item->getTaxAmount();
                $price  = $item->getPrice();
                $itemId = $this->_item->syncBySku($sku);
                if (!$itemId) throw new \Exception(
                    __('Can\'t sync product with SKU:%1 on CreditMemo to QBO', $sku)
                );
//                }
                /** Only sync up refunded items */
                if ($qty > 0 && isset($total) && isset($itemId)) {
                    if ($this->config->getCountry() == 'OTHER') {
                        $lines[] = [
                            'LineNum'             => $i,
                            'Amount'              => $total,
                            'DetailType'          => 'SalesItemLineDetail',
                            'SalesItemLineDetail' => [
                                'ItemRef'    => ['value' => $itemId],
                                'UnitPrice'  => $price,
                                'Qty'        => $qty,
                                'TaxCodeRef' => ['value' => $tax ? 'TAX' : 'NON'],
                            ],
                        ];
                    } else {
                        $lines[] = [
                            'LineNum'             => $i,
                            'Amount'              => $total,
                            'DetailType'          => 'SalesItemLineDetail',
                            'SalesItemLineDetail' => [
                                'ItemRef'    => ['value' => $itemId],
                                'UnitPrice'  => $price,
                                'Qty'        => $qty,
                                'TaxCodeRef' => ['value' => $tax ? $this->prepareTaxCodeRef($item->getOrderItemId()) : $this->getTaxFree()]
                            ],
                        ];
                    }
                }

                $i++;
            }

            //build shipping fee
            // set shipping fee
            if ($this->getShippingAllow() == true) {
                if ($this->prepareLineShippingFee())
                    $lines[] = $this->prepareLineShippingFee();
            }

            //build discount fee
            $lines[] = $this->prepareLineDiscountAmount();

            return $lines;
        } catch (\Exception $exception) {
            throw new LocalizedException(
                __('Error when syncing products: %1', $exception->getMessage())
            );
        }
    }

    /**
     * @param $itemId
     *
     * @return bool|int
     */
    public function prepareTaxCodeRef($itemId)
    {
        $taxCode = 0;
        /** @var \Magento\Sales\Model\Order\Tax\Item $modelTaxItem */
        $modelTaxItem = ObjectManager::getInstance()->create('Magento\Sales\Model\Order\Tax\Item')->load($itemId, 'item_id');
        if ($modelTaxItem) {
            $taxId    = $modelTaxItem->getTaxId();
            $modelTax = ObjectManager::getInstance()->create('Magento\Sales\Model\Order\Tax')->load($taxId);

            if ($modelTax && !empty($modelTax->getData())) {
                $taxCode = $modelTax->getCode();
            }
            $tax = $this->tax->create()->load($taxCode, 'tax_code');
            if ($tax->getQboId() && $tax->getQboId() > 0) {
                $taxCodeId = $tax->getQboId();

                return $taxCodeId;
            }
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getTaxFree()
    {
        $modelTax = $this->tax->create()->load('tax_zero_qb', 'tax_code');
        if ($modelTax) {
            return $modelTax->getQboId();
        }

        return false;
    }

    /**
     * @param bool $hasTax
     *
     * @return array
     */
    public function getTaxCodeRef($hasTax)
    {
        return ['value' => $hasTax ? 'TAX' : 'NON'];
    }

    /**
     * @return array
     */
    public function prepareLineShippingFee()
    {

        $shippingAmount = $this->getModel()->getShippingInclTax();
        if ($this->config->getCountry() != 'OTHER') {
            $lines = [
                'Amount'              => $shippingAmount ? $shippingAmount : 0,
                'DetailType'          => 'SalesItemLineDetail',
                'SalesItemLineDetail' => [
                    'ItemRef'    => ['value' => 'SHIPPING_ITEM_ID'],
                    'TaxCodeRef' => ['value' => $this->config->getTaxShipping()],
                ],
            ];
        } else {
            $lines = [
                'Amount'              => $shippingAmount ? $shippingAmount : 0,
                'DetailType'          => 'SalesItemLineDetail',
                'SalesItemLineDetail' => [
                    'ItemRef' => ['value' => 'SHIPPING_ITEM_ID'],
                ],
            ];
        }

        return $lines;
    }

    /**
     * @return array
     */
    public function prepareLineDiscountAmount()
    {
        $discountAmount = $this->getModel()->getDiscountAmount();
        $lines          = [
            'Amount'             => $discountAmount ? -1 * $discountAmount : 0,
            'DetailType'         => 'DiscountLineDetail',
            'DiscountLineDetail' => [
                'PercentBased' => false,
            ]
        ];

        return $lines;
    }

    /**
     * Check creditmemo by Increment Id
     *
     * @param $id
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkCreditmemo($id)
    {
        $prefix = $this->config->getPrefix('creditmemos');
        $name   = $prefix . $id;
        $query  = "SELECT Id, SyncToken FROM CreditMemo WHERE DocNumber='{$name}'";

        return $this->query($query);
    }

//    /**
//     * Check creditmemo by Increment Id
//     *
//     * @param $id
//     * @return array
//     */
//    protected function checkPayment($orderQboId)
//    {
//        $query = "SELECT Line FROM PayMent ";
//
//        $paymentLine = $this->query($query);
//        foreach ($paymentLine as $item){
//            if($i
//        }
//    }

    /**
     * Check invoice by Increment Id
     *
     * @param $id
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkInvoice($id)
    {
        $prefix = $this->config->getPrefix('order');
        $name   = $prefix . $id;
        $query  = "SELECT TotalAmt, Balance FROM invoice WHERE DocNumber='{$name}'";

        return $this->query($query);
    }

    /**
     * @return mixed
     */
    public function getShippingAllow()
    {
        $registryObject = ObjectManager::getInstance()->get('Magento\Framework\Registry');
        try {
            $shippingAllow = $registryObject->registry('shipping_allow');
            if (isset($shippingAllow)) return $shippingAllow;
        } catch (\Exception $exception) {
        }
        /** @var \Magenest\QuickBooksOnline\Model\Synchronization\PreferenceSetting $preferenceSetting */
        $preferenceSetting = ObjectManager::getInstance()->create('Magenest\QuickBooksOnline\Model\Synchronization\PreferenceSetting');
        $shippingAllow     = $preferenceSetting->getShippingAllow();
        $registryObject->register('shipping_allow', $shippingAllow);

        return $shippingAllow;
    }

}
