<?php

namespace Blm\CustomerDocuments\Model\ResourceModel\CustomerDocuments;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'customerdocuments_id';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Blm\CustomerDocuments\Model\CustomerDocuments',
            'Blm\CustomerDocuments\Model\ResourceModel\CustomerDocuments'
        );
    }
}