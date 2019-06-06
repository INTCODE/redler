<?php
/**
  
 
 
  
 
 */

namespace Blm\CronTab\Model\ResourceModel\CronTab;


 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected $_eventPrefix= 'CronTab_CronTab_collection';

    protected $eventObject= 'CronTab_collection';
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Blm\CronTab\Model\CronTab',
            'Blm\CronTab\Model\ResourceModel\CronTab'
        );
    }
}