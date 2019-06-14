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


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();


        $params = $this->getRequest()->getParams();
        $idQuote=$this->cart->getQuote()->getId();
        $id=$params['product'];
        $qty=$params['qty'];
        $address=$params['addressId'];
        if(isset($params['super_attribute'])) $type=reset($params['super_attribute']); else $type = "0";
       // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========================\n".print_r('asd', true));

      //  file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========tab=============\n".print_r($type, true));


        $sel="SELECT crontab_id, quoteId, productId, `type`, qty, address
        FROM blm_crontab
        WHERE quoteId=$idQuote";

file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========================\n".print_r('xd', true));
            $result = $connection->fetchAll($sel); 
            $flag=false;

            foreach ($result as $key => $value) {
                if($value['productId']==$id && $value['address']==$address && $value['type']==$type){
                    $tabid=$value['crontab_id'];
                    if($qty>0){
                        $update="UPDATE blm_crontab
                        SET
                            qty='$qty'
                        WHERE crontab_id=$tabid";

file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========================\n".print_r($tabid, true));

                        $connection->query($update);
                        $flag=true;
                    }elseif ($qty==0) {
                       $del="DELETE FROM blm_crontab WHERE crontab_id=$tabid";
                       $connection->query($del);
                       $flag=true;
                    }

               file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========tabdata=============\n".print_r($value, true));

                }
            }
            if($flag!=true){
                $ins="INSERT INTO blm_crontab
                (quoteId, productId, `type`, qty, address)
                VALUES ('$idQuote', '$id', '$type', '$qty', '$address')";

                 $connection->query($ins);
            }

       // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========session=============\n".print_r('qwe', true));

     
        
      
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
            
            $debugContent = "";
            $deletedId=null;
            $items = $this->cart->getQuote()->getAllItems();
            $tempCart=$this->cart->getQuote()->getAllItems();
            foreach($items as $item) {
                             //    file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========typeID===========\n".print_r($item->debug(), true));
                
                if($item->getProductId() == $product->getId()){
                    $debugContent .= "?? ".$item->getProductId()." == ".$product->getId()." >>> ".$item->getItemId()."\n";
                    $deletedId=$item->getItemId();
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
                              
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                $productDel = $objectManager->get('Magento\Catalog\Model\Product')->load($option->getProduct()->getId());
                                $packageId=$productDel->getCustomAttribute('package_type')->getValue();
                                // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========typeID===========\n".print_r($option->getProduct()->getId(), true));
                                // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========typeID===========\n".print_r($productDel->debug(), true));
           


                                if($product->getTypeId()=='configurable'){
                                    $productType=$packageId;
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
                            $deletedId=$idToDelete;
                            $debugContent .= " :) deleting...\n";
                       
                            $idQuote=$this->cart->getQuote()->getId();
                            $id=$params['product'];
                            $qty=$params['qty'];
                            $address=$params['addressId'];

                            $res="SELECT *
                            FROM blm_crontab b
                            WHERE b.productId=$id AND b.quoteId=$idQuote AND  b.`type`=$idAttribute";
                             $res = $connection->fetchAll($res); 

                             foreach ($result as $key => $value) {
      
                                 if($value['address']==$address){
                                    try {
                                        $newqty=0;
                                        foreach ($res as $key => $value) {
                                            $newqty+=$value['qty'];
                                        }

                                        $params['qty']=$newqty;
                                        $this->cart->removeItem($idToDelete);
                                        $this->cart->getQuote()->setTotalsCollectedFlag(false);
                                        break;
                                    } catch (\Exception $e) {
                                    // $this->messageManager->addErrorMessage(__('We can\'t remove the item.'));
                                    // $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                                    }
                                 }
                             # code...
                             }

                            // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n===========newqty==============\n".print_r($newqty, true));
                                     
            
                         
                        }
                    }

                    $debugContent .= "\n---------------\n";
            
                }
                            
            }


          // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========================\n".print_r($this->cart->getQuote()->getShippingAddress()->debug(), true));

            //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========product============\n".print_r($product->debug(), true));




            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            $idQuote=$this->cart->getQuote()->getId();
            $id=$params['product'];
            $qty=$params['qty'];
            $address=$params['addressId'];


            if($params['qty'] > 0){
              $this->cart->addProduct($product, $params);
       
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }
            }

            $this->cart->save();
          //  file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($this->cart->debug(), true));


          $items = $this->cart->getQuote()->getAllItems();
          $ids=array();

            //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========koszyk=============\n".print_r($ids, true));



  
            $idQuote=$this->cart->getQuote()->getId();
            $sql = "SELECT *
            FROM quote_item 
            WHERE quote_id = $idQuote";
            $result = $connection->fetchAll($sql); 

        $shiptest=array();

                // if(isset($temp)){
                //     $_SESSION["curr"] =$temp;
    
                // }else{
                //    // $_SESSION["curr"] =$shiptest;
                // }
                

             //   file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========sessionearly=============\n".print_r($_SESSION["curr"], true));

          foreach ($result as $key => $value) {
             if(!$value['parent_item_id']){
               
                $address=$params['addressId'];
                $qty=(int)$value['qty'];
               //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========qty=============\n".print_r($value, true));

                $id=$value['item_id'];
                $product_ship=array('qty'=>$qty,'address'=>$address);
                $ship_elem = array($id => $product_ship);
                array_push($shiptest,$ship_elem);
            
             }
          }
               file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========currentShip=============\n".print_r($shiptest, true));
               //$_SESSION["curr"]=$shiptest;

               $sql="SELECT *
               FROM blm_crontab b
               WHERE b.quoteId=$idQuote";


                $dbArray=array();
               $result = $connection->fetchAll($sql); 
               foreach ($result as $key => $value) {
                   $productId=$value['productId'];
                   $address=$value['address'];
                   $qty=$value['qty'];
                   
                $sql="SELECT q.item_id
                FROM quote_item q
                WHERE q.quote_id=$idQuote AND q.product_id=$productId";

                $result = $connection->fetchAll($sql); 
              $product_ship=array('qty'=>$qty,'address'=>$address);
          
              if(isset($result[0]['item_id']))
                $ship_elem = array($result[0]['item_id'] => $product_ship);
                array_push($dbArray,$ship_elem);

               }
               $_SESSION["set"] =1;
               $_SESSION["curr"]=$dbArray;

               //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========finish=============\n".print_r($dbArray, true));



             //  file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========session=============\n".print_r($_SESSION["curr"], true));

            
    

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
             file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========error=============\n".print_r($e->getMessage(), true));

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