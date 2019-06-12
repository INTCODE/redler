<?php
namespace Blm\CustomerDocuments\Controller\Adminhtml\Items;

class Index extends \Blm\CustomerDocuments\Controller\Adminhtml\Items
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
        $resultPage->setActiveMenu('Blm_CustomerDocuments::test');
        $resultPage->getConfig()->getTitle()->prepend(__('Customers Documents'));
        $resultPage->addBreadcrumb(__('Customer'), __('Customer'));
        $resultPage->addBreadcrumb(__('Documents'), __('Documents'));
        return $resultPage;
    }
}