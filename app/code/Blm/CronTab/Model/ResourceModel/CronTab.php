<?php
/**
  
 
 
  
 
 */

namespace Blm\CronTab\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

class CronTab extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table

     
     */

     public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }


    
    protected function _construct()
    {
        $this->_init('customer_entity', 'entity_id');   //here "blm_crontab" is table name and "crontab_id" is the primary key of custom table
    }
}