<?php
/**
  
 
 
  
 
 */

namespace Blm\CronTab\Controller\Adminhtml\Items;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Blm\CronTab\Model\ResourceModel\CronTab\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $record) {
            $date = date('Y-m-d', time());
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($record['entity_id']);
            $effectiveDate = date('Y-m-d', strtotime("+3 months", strtotime($date)));
            $customerObj['approve_account']=2;
            $customerObj['CheckedDate']=$effectiveDate;
            $customerObj->save();



           // file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========adres===========\n".print_r($customerObj->debug(), true));
            //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========date===========\n".print_r($effectiveDate, true));
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been approved.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
