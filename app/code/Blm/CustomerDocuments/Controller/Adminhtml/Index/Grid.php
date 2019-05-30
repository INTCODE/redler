<?php
namespace Blm\CustomerDocuments\Controller\Adminhtml\Index;
 
class Grid extends \Blm\CustomerDocuments\Controller\Adminhtml\Index
    {
        /**
         * @var \Magento\Framework\View\Result\LayoutFactory
         */
        protected $resultLayoutFactory;
 
        public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
        ) {
            parent::__construct($context);
            $this->resultLayoutFactory = $resultLayoutFactory;
        }
 
        /**
         * @return \Magento\Framework\View\Result\Layout
         */
        public function execute()
        {
            $resultLayout = $this->resultLayoutFactory->create();
            $resultLayout->getLayout()->getBlock('hello.hello.edit.tab.grid');
            return $resultLayout;
        }
 
    }






// class View extends \Magento\Customer\Controller\Adminhtml\Index {
 
//     public function execute() {
 
//         $this->initCurrentCustomer();
//         $resultLayout = $this->resultLayoutFactory->create();
//         return $resultLayout;
//     }
 
// }