<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amitshree\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class AccountDocument extends Column
{
    /**
     * 
     * @param ContextInterface   $context           
     * @param UiComponentFactory $uiComponentFactory   
     * @param array              $components        
     * @param array              $data              
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) 
            {

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($item['entity_id']);
                $item[$this->getData('name')] = $customer->getData('CheckedDate');
                // if($customer->getData('approve_account') == 0) {
                //     $item[$this->getData('name')] = __("Pending");
                // }
                // else if ($customer->getData('approve_account') == 1){
                //     $item[$this->getData('name')] = __("Dissaproved");
                // }
                // else if($customer->getData('approve_account') == 2) {
                //     $item[$this->getData('name')] = __("Approved");
                // }
            }
        }
        return $dataSource;
    }
}