<?php
namespace Blm\CustomerDocuments\Block;

/**
 * CustomerDocuments content block
 */
class CustomerDocuments extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Blm CustomerDocuments Module'));
        
        return parent::_prepareLayout();
    }
}
