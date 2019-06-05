<?php
/**
  
 
 
  
 
 */

namespace Blm\CronTab\Model;

use Magento\Framework\Model\AbstractModel;

class CronTab extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Blm\CronTab\Model\ResourceModel\CronTab');
    }
}