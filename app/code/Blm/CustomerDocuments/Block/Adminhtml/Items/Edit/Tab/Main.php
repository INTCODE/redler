<?php

namespace Blm\CustomerDocuments\Block\Adminhtml\Items\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    protected $_wysiwygConfig;
    protected $_customerFactory;
 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $registry, 
        \Magento\Framework\Data\FormFactory $formFactory,  
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, 
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        array $data = []
    ) 
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }


    public function getCustomerCollection()
{
    return $this->_customerFactory->create();
}


    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {



        $customerCollection = $this->getCustomerCollection();
        $userArray=array();
        foreach ($customerCollection as $customer) {
            $userArray[$customer->getId()]=$customer->getEmail();
        //file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========adres===========\n".print_r( $userArray, true));

        }


        $model = $this->_coreRegistry->registry('current_blm_customerdocuments_items');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);
        if ($model->getId()) {
            $fieldset->addField('customerdocuments_id', 'hidden', ['name' => 'customerdocuments_id']);
        }
        $fieldset->addField(
            'User',
            'select',
            ['name' => 'User', 'label' => __('User'), 'title' => __('User'),  'options'   => $userArray, 'required' => true]
        );
        $fieldset->addField(
            'image',
            'file',
            [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'required'  => true
            ]
        );

        
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
