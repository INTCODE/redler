<?php
/**
  
 
 
  
 
 */

namespace Blm\CronTab\Controller\Adminhtml\Items;

class NewAction extends \Blm\CronTab\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
