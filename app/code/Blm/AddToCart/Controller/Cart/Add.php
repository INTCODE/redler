<?php




/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blm\AddToCart\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
use function GuzzleHttp\json_encode;

/**
 * Controller for processing add to cart action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{


    
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $id=null;
        $customerSession = $objectManager->create("Magento\Customer\Model\Session");
      
        if($customerSession->isLoggedIn()){
          $id= $customerSession->getCustomerId();
        }
        $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($id);
      
        $addresses = $customerObj->getAddresses();
        $currentAddress=null;
        foreach ($customerObj->getAddresses() as $address)
        {
            if($params['addressId']==$address['entity_id']){
                $currentAddress=$address;
            }

        }

       //currently set address 
      //  file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========adres===========\n".print_r($currentAddress->debug(), true));
        //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========adres===========\n".print_r($params['addressId'], true));

       
      
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        

        try {
            
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
               $params['qty'] = $filter->filter($params['qty']);
            }else{
                $params['qty'] = 0;
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            $idAttribute = 0;
            if(isset($params['super_attribute'])){
                foreach($params['super_attribute'] as $attr){
                    $idAttribute = $attr;
                }
            }
 

            $elo = "";
            

              /*  $childProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
                foreach ($childProducts as $child) {
                    $elo .= $child->getData($attributeData[0]['attribute_code']);
                    //$elo .= $child->getPrice();
                }  
*/

            // $optionsData = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);

            // foreach ($optionsData as $option) {
            //     $elo .= $option['frontend_label'];
            //    // $elo .= $option['attribute_code'];
            //    // $elo .= $option['attribute_id'];
            //    // $elo .= $option['options'];

            
            // }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $configProduct = $objectManager->create('Magento\Catalog\Model\Product')->load(31);

            //$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            
    // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========attributes===========\n".print_r($result, true));

          //  $attributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product); 
          //  file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========attributes===========\n".print_r($attributes, true));
         //   file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========test===========\n".print_r($optionsData, true));

            $debugContent = "";
            $items = $this->cart->getQuote()->getAllItems();
            foreach($items as $item) {
                if($item->getProductId() == $product->getId()){
                    $debugContent .= "?? ".$item->getProductId()." == ".$product->getId()." >>> ".$item->getItemId()."\n";
                    $debugContent .= "ID: ".$item->getProductId()."\n";
                    $debugContent .= "Name: ".$item->getName()."\n";
                    $debugContent .= "ITEM ID: ".$item->getItemId()."\n";
                    $debugContent .= "Quantity: ".$item->getQty()."\n";
                    
                    $delete = false;
                    if($idAttribute != 0){
                        foreach ($item->getOptions() as $option) {
                            if (is_object($option->getProduct()) && $option->getProduct()->getId() != $item->getProduct()->getId()) {

                                $debugContent .= "ID KOMBINACJI: ".$option->getProduct()->getId()."\n";
                                $debugContent .= "ID ATTRYBUTU Z KOSZYKA: ".$idAttribute."\n";
                              
           // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========typeID===========\n".print_r($product->getTypeId(), true));
                                if($product->getTypeId()=='configurable'){
                                    $productType=$option->getProduct()->getId()-10;
                                }else{
                                    $productType=$option->getProduct()->getId();
                                }
                                
                                if($productType == $idAttribute){
                                    $delete = true;
                                }
                                break;
                            }
                        }
                    }

                    if($delete || $idAttribute == 0){
                        $idToDelete = (int)$item->getItemId();
                        if ($idToDelete) {
                            $debugContent .= " :) deleting...\n";
                            try {
                                $this->cart->removeItem($idToDelete);
                                $this->cart->getQuote()->setTotalsCollectedFlag(false);
                                break;
                            } catch (\Exception $e) {
                            // $this->messageManager->addErrorMessage(__('We can\'t remove the item.'));
                            // $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                            }
                        }
                    }

                    $debugContent .= "\n---------------\n";
            
                }
                            
            }

           // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========================\n".print_r($debugContent, true));
          // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========================\n".print_r($this->cart->getQuote()->getShippingAddress()->debug(), true));

            //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========product============\n".print_r($product->debug(), true));



            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            if($params['qty'] > 0){
                $this->cart->addProduct($product, $params);
         //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========params============\n".print_r($params, true));
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }
            }

           // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($this->cart->debug(), true));
            $this->cart->save();

            $cookie_name = "user";     
            $cookie_value = "asd";



          //  $data = json_decode($_COOKIE[$cookie_name], true);

            
            //  file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszykpoSave=============\n".print_r($this->cart->debug(), true));
            $shiptest=array();
         //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($quote->getId(), true));
          $items = $this->cart->getQuote()->getAllItems();
          foreach ($items as $key => $value) {

          $address=$params['addressId'];
          $qty=$value->getQty();
          $product_item_id=$value->getId();

          $product_ship=array('qty'=>$qty,'address'=>$address);
          $ship_elem = array($product_item_id => $product_ship);
          array_push($shiptest,$ship_elem);

          }

        //   file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========shipTest=============\n".print_r($shiptest, true));
          
        //   file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========data=============\n".print_r($data, true));
          
          
        //   setcookie($cookie_name, json_encode($shiptest), time() + (86400 * 30), "/"); // 86400 = 1 day
     

          
          


        
          $AddressPost = $this->_objectManager->get('Magento\Multishipping\Controller\Checkout\AddressesPost');
          $AddressPost->updateAddresses($shiptest);

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    if ($this->shouldRedirectToCart()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                       // $this->messageManager->addSuccessMessage($message);
                    } else {
                      /*  $this->messageManager->addComplexSuccessMessage(
                            'addCartSuccessMessage',
                            [
                                'product_name' => $product->getName(),
                                'cart_url' => $this->getCartUrl(),
                            ]
                        );*/
                    }
                }
                return $this->goBack(null, $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $url = $this->_redirect->getRedirectUrl($this->getCartUrl());
            }

            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }

    /**
     * Returns cart url
     *
     * @return string
     */
    private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }

    /**
     * Is redirect should be performed after the product was added to cart.
     *
     * @return bool
     */
    private function shouldRedirectToCart()
    {
        return $this->_scopeConfig->isSetFlag(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}