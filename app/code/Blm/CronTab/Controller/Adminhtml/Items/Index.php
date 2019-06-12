<?php
/**
  
 
 
  
 
 */

namespace Blm\CronTab\Controller\Adminhtml\Items;

class Index extends \Blm\CronTab\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Blm_CronTab::test');
        $resultPage->getConfig()->getTitle()->prepend(__('Customers List'));
        $resultPage->addBreadcrumb(__('Customers'), __('Customers'));
        $resultPage->addBreadcrumb(__('CustomersList'), __('CustomersList'));
        return $resultPage;
    }
}