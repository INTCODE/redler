<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Post update shopping cart.
 */
class UpdatePost extends \Magento\Checkout\Controller\Cart implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param RequestQuantityProcessor $quantityProcessor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        RequestQuantityProcessor $quantityProcessor = null
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );

        $this->quantityProcessor = $quantityProcessor ?: $this->_objectManager->get(RequestQuantityProcessor::class);
    }

    /**
     * Empty customer's shopping cart
     *
     * @return void
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->cart->truncate()->save();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __('We can\'t update the shopping cart.'));
        }
    }

    /**
     * Update customer's shopping cart
     *
     * @return void
     */
    protected function _updateShoppingCart()
    {
        try {
            
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }
                $cartData = $this->quantityProcessor->process($cartData);
                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData)->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the shopping cart.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }

    /**
     * Update shopping cart data action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $idQuote=$cart->getQuote()->getId();

        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');
        $params=$this->getRequest()->getParams();

        foreach ($params['cart'] as $key => $value) {
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========tab=============\n".print_r($value['qty'], true));
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========tab=============\n".print_r($key, true));
        $qty=$value['qty'];
                
        $sql="SELECT q.product_id
        FROM quote_item q
        WHERE q.quote_id=$idQuote AND q.item_id=$key";
       $result = $connection->fetchAll($sql); 


       $child="SELECT q.product_id
       FROM quote_item q
       WHERE q.quote_id=$idQuote AND q.parent_item_id=$key";

        $childId = $connection->fetchAll($child); 

        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($childId);
        $type=$product->getCustomAttribute('package_type')->getValue();
        $product_id=$result[0]['product_id'];
        file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========c=============\n".print_r($type, true));

        if($qty>0){
            $sql="UPDATE blm_crontab
            SET
                qty='$qty'
            WHERE quoteId= $idQuote AND productId=$product_id AND `type`=$type";
    
        }else{
            $sql="DELETE FROM blm_crontab   WHERE quoteId= $idQuote AND productId=$product_id AND `type`=$type";
        }

        }
        $connection->query($sql);


        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        return $this->_goBack();
    }
}
