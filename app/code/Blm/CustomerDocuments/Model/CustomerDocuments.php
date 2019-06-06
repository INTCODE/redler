<?php
namespace Blm\CustomerDocuments\Model;

use Magento\Framework\Model\AbstractModel;

class CustomerDocuments extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Blm\CustomerDocuments\Model\ResourceModel\CustomerDocuments');
    }
}