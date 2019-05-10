<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 * Magenest_QuickBooksOnline extension
 * NOTICE OF LICENSE
 * @category Magenest
 * @package  Magenest_QuickBooksOnline
 * @author   Magenest JSC
 */

namespace Magenest\QuickBooksOnline\Observer\Item;

use Magenest\QuickBooksOnline\Observer\AbstractObserver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\QuickBooksOnline\Model\Synchronization\Item;
use Magenest\QuickBooksOnline\Model\Config;
use Magenest\QuickBooksOnline\Model\QueueFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Action\Context;


/**
 * Update Stock status for Magento 2.3 MSI
 * Class UpdateInventory
 * @package Magenest\QuickBooksOnline\Observer\Item
 */
class UpdateInventory extends AbstractObserver implements ObserverInterface
{
    /**
     * @var Item
     */
    protected $_item;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $stockInterface;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * @var Context
     */
    protected $context;

    /**
     * UpdateInventory constructor.
     *
     * @param Config $config
     * @param QueueFactory $queueFactory
     * @param Item $item
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param ProductFactory $productFactory
     * @param StockRegistryInterface $stockInterface
     * @param ProductMetadataInterface $productMetadata
     * @param Context $context
     */
    public function __construct(
        Config $config,
        QueueFactory $queueFactory,
        Item $item,
        Registry $registry,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        ProductFactory $productFactory,
        StockRegistryInterface $stockInterface,
        ProductMetadataInterface $productMetadata,
        Context $context
    ) {
        parent::__construct($context->getMessageManager(), $config, $queueFactory);
        $this->_item           = $item;
        $this->type            = 'item';
        $this->registry        = $registry;
        $this->storeManager    = $storeManager;
        $this->resource        = $resource;
        $this->productFactory  = $productFactory;
        $this->stockInterface  = $stockInterface;
        $this->productMetaData = $productMetadata;
        $this->context         = $context;
    }

    /**
     * Save product inventory stock backend
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $magentoVer = $this->productMetaData->getVersion();
        if ($this->isConnected() && $this->isConnected() == 1 && version_compare($magentoVer, '2.3', '>=')) {
            try {
                /** @var \Magento\Catalog\Model\Product $product */
                $product      = $observer->getEvent()->getProduct();
                $request      = $observer->getEvent()->getController()->getRequest();
                $productArray = $request->getParam('product', []);
                $sources      = $request->getParam('sources', []);
                $id           = $product->getId();
                $productType  = $product->getTypeId();

                if (isset($productArray) && isset($sources) && $this->isEnabled() && $id
                    && $productType != 'grouped' && $productType != 'bundle') {
                    if ($this->isImmediatelyMode()) {

                        /** @var \Magento\Framework\Registry $registryObject */
                        $registryObject = ObjectManager::getInstance()->get('Magento\Framework\Registry');

                        $websiteCode = $this->storeManager->getWebsite(1)->getCode(); /*get default website's code*/

                        /** @var  \Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite $getAssignedStockId */
                        $getAssignedStockId = ObjectManager::getInstance()
                            ->create('Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite');
                        $websiteStockId     = $getAssignedStockId->execute($websiteCode); /*get default website's stock Id*/

                        if ($productType == 'simple' || $productType == 'virtual' || $productType == 'downloadable') {
                            $qty = $this->checkQty($id, $product->getSku(), $sources, $websiteStockId);
                            $registryObject->unregister('check_to_syn' . $id); /*unregister check to syn registry, in case of new product*/
                            $qboId = $this->_item->sendItems($id, true, $qty);
                            if ($qboId) {
                                $this->messageManager->addSuccessMessage(
                                    __('Updated stock status of this product (Id: %1) in QuickBooksOnline.', $qboId)
                                );
                            }
                        } else if ($productType == 'configurable') {

                            $configurableMatrix = $request->getParam('configurable-matrix-serialized', '');
                            if ($configurableMatrix != '') {
                                $this->checkQtyConfigurable($configurableMatrix, $websiteStockId);
                            }
                        }
                    } else {
                        $this->addToQueue($id);
                    }
                }
                unset($sources);
            } catch
            (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
    }

    /**
     * Check Multi Source stock qty for configurable product
     *
     * @param $matrix
     * @param $websiteStockId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    public function checkQtyConfigurable($matrix, $websiteStockId)
    {
        $productsData = json_decode($matrix, true);
        /** @var \Magento\Framework\Registry $registryObject */
        $registryObject = ObjectManager::getInstance()->get('Magento\Framework\Registry');

        foreach ($productsData as $key => $productData) { /*get stock qty per child product*/
            if (isset($productData['quantity_per_source'])) {
                $quantityPerSource = is_array($productData['quantity_per_source'])
                    ? $productData['quantity_per_source']
                    : [];

                $sku     = $productData['sku'];
                $product = $this->productFactory->create();
                $id      = $product->load($product->getIdBySku($sku))->getId();

                $qty = 0;
                foreach ($quantityPerSource as $source) { /*get stock qty per valid source*/
                    $sourceCode = @$source['source_code'];
                    if ($this->validateSource($websiteStockId, $sourceCode) && (@$source['source_status'] == 'Enabled' || @$source['status'] == 1)) {
                        $qty += @$source['quantity_per_source'];
                    }
                }
                $currentQty        = $this->stockInterface->getStockItem($id)->getQty();
                $currentSalableQty = $this->_item->getSalableQty($sku);
                if (isset($currentQty) && isset($currentSalableQty)) {
                    $pendingQty = $currentQty - $currentSalableQty; /*sold but not delivered qty*/
                    $qty        = $pendingQty > 0 ? $qty - $pendingQty : $qty; /*get updated salable qty*/
                }
                $registryObject->unregister('check_to_syn' . $id); /*unregister check to syn registry, in case of new product*/
                $qboId = $this->_item->sendItems($id, true, $qty);
                if ($qboId) {
                    $this->messageManager->addSuccessMessage(
                        __('Updated stock status of this product (Id: %1) in QuickBooksOnline.', $qboId)
                    );
                }
            }
        }
    }

    /**
     * Check Multi Source stock qty for basic products
     *
     * @param $sources
     * @param $websiteStockId
     * @param $sku
     * @param $id
     *
     * @return null
     */
    public function checkQty($id, $sku, $sources, $websiteStockId)
    {
        $qty        = null;
        $currentQty = $this->stockInterface->getStockItem($id)->getQty();

        if (!empty($sources['assigned_sources'])) {
            foreach ($sources['assigned_sources'] as $source) { /*get stock qty per valid source*/
                $sourceCode = @$source['source_code'];
                if ($this->validateSource($websiteStockId, $sourceCode) && @$source['status'] == 1) {
                    $qty += @$source['quantity'];
                }
            }
        } else $qty = $currentQty;

        $currentSalableQty = $this->_item->getSalableQty($sku);
        if (isset($currentQty) && isset($currentSalableQty)) {
            $pendingQty = $currentQty - $currentSalableQty; /*sold but not delivered qty*/
            $qty        = $pendingQty > 0 ? $qty - $pendingQty : $qty; /*get updated salable qty*/
        }

        return $qty;
    }

    /**
     * Validate input source for base website
     *
     * @param $stockId
     * @param $sourceCode
     *
     * @return bool
     */
    public function validateSource($stockId, $sourceCode)
    {
        $resource             = $this->resource;
        $connection           = $resource->getConnection();
        $stockSourceLinkTable = $resource->getTableName('inventory_source_stock_link'); /*gives table name with prefix*/
        $sourceCode           = '"' . $sourceCode . '"';
        $sql                  = "Select stock_id FROM " . $stockSourceLinkTable . " Where source_code = " . $sourceCode;
        $sourceStockId        = $connection->fetchOne($sql); /*get stock Id linked to this source*/
        if ($sourceStockId == $stockId) {
            return true;
        } else return false;
    }
}
