<?php
/**
 * Created by PhpStorm.
 * User: thang
 * Date: 30/06/2018
 * Time: 09:25
 */

namespace Magenest\QuickBooksOnline\Block\Adminhtml\Queue;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Button\Generic;
use Magento\Framework\App\ObjectManager;

class AddProductButton extends Generic
{

    public function getButtonData()
    {
        /**
         * @var \Magenest\QuickBooksOnline\Model\Config $config
         */
        $config = ObjectManager::getInstance()->get('Magenest\QuickBooksOnline\Model\Config');
        if ($config->isEnableByType('item')) {
            return [
                'id' => 'add_product_button',
                'class' => '',
                'label' => __('Add All Products'),
                'on_click' => sprintf("location.href = '%s';", $this->getUrl('qbonline/queue/item'))
            ];
        }
        return [];
    }
}
