<?php
/**
 *  
  
 * 
 */

namespace Blm\CustomerDocuments\Model\ResourceModel;

class CustomerDocuments extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('blm_customerdocuments', 'customerdocuments_id');   //here "blm_customerdocuments" is table name and "customerdocuments_id" is the primary key of custom table
    }
}