<?php
/**
 *  
  
 * 
 */

namespace Blm\CustomerDocuments\Controller\Adminhtml\Items;

class NewAction extends \Blm\CustomerDocuments\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
