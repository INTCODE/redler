<?php
/**
  
 
 
  
 
 */

namespace Blm\CronTab\Model\ResourceModel;

class CronTab extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('blm_crontab', 'crontab_id');   //here "blm_crontab" is table name and "crontab_id" is the primary key of custom table
    }
}