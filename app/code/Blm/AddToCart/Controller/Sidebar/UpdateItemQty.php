<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blm\AddToCart\Controller\Sidebar;

use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;

class UpdateItemQty extends Action
{
    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper

     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Sidebar $sidebar,

        LoggerInterface $logger,
        Data $jsonHelper
    ) {
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();


        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();



        $itemId = (int)$this->getRequest()->getParam('item_id');
        $itemQty = $this->getRequest()->getParam('item_qty') * 1;

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $idQuote=$cart->getQuote()->getId();
        
        $sql="SELECT q.product_id
        FROM quote_item q
        WHERE q.quote_id=$idQuote AND q.item_id=$itemId";
       $result = $connection->fetchAll($sql); 


       $child="SELECT q.product_id
       FROM quote_item q
       WHERE q.quote_id=$idQuote AND q.parent_item_id=$itemId";

        $childId = $connection->fetchAll($child); 

        try {
        $product_id=$result[0]['product_id'];
        $childId=$childId[0]['product_id'];

        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($childId);
        $type=$product->getCustomAttribute('package_type')->getValue();

        if($itemQty>0){
            $sql="UPDATE blm_crontab
            SET
                qty='$itemQty'
            WHERE quoteId= $idQuote AND productId=$product_id AND `type`=$type";
    
        }else{
            $sql="DELETE FROM blm_crontab   WHERE quoteId= $idQuote AND productId=$product_id AND `type`=$type";
        }

        $connection->query($sql);
        

            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->updateQuoteItem($itemId, $itemQty);
            return $this->jsonResponse();
        } catch (LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return Http
     */
    protected function jsonResponse($error = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($error))
        );
    }
}
