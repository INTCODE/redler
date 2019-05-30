<?php
namespace Blm\CustomerDocuments\Block\Adminhtml\CustomerDocuments\Grid;
 
use Magento\Customer\Controller\RegistryConstants;
 
class Documents extends \Magento\Backend\Block\Widget\Grid\Extended {
 
    protected $_coreRegistry = null;
    protected $collectionFactory;
    protected $_customerFactory;
 
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory, \Magento\Framework\Registry $coreRegistry, \Magento\Customer\Model\CustomerFactory $customerFactory, array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        $this->_customerFactory = $customerFactory;
 
        parent::__construct($context, $backendHelper, $data);
    }
 
    protected function _construct() {
        parent::_construct();
        $this->setId('customer_managepayment_grid');
        $this->setDefaultSort('target_invoive_id', 'desc');
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection() {
 
        $customer = $this->_customerFactory->create();
        $customer = $customer->load($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID))->getData();
 
 
        $collection = $this->_collectionFactory->getReport('sales_order_invoice_grid_data_source')->addFieldToFilter(
                'customer_email', $customer['email']
        );
        $this->setCollection($collection);
 
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns() {
 
        $this->addColumn('increment_id', ['header' => __('Invoice'), 'width' => '100', 'index' => 'increment_id']);
 
        $this->addColumn('created_at', ['header' => __('Invoice Date'), 'width' => '100', 'index' => 'created_at', 'type' => 'date']);
 
        $this->addColumn('order_increment_id', ['header' => __('Order #'), 'width' => '100', 'index' => 'order_increment_id']);
 
        $this->addColumn('order_created_at', ['header' => __('Order Date'), 'index' => 'order_created_at', 'type' => 'date']);
 
        $this->addColumn('state', ['header' => __('Status'), 'index' => 'state', 'type' => 'state']);
 
        $this->addColumn(
                'base_grand_total', [
            'header' => __('Grand Total (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_grand_total'
                ]
        );
 
        $this->addColumn(
                'grand_total', [
            'header' => __('Grand Total (Purchased)'),
            'index' => 'interest_amount',
            'type' => 'currency',
            'currency' => 'grand_total'
                ]
        );
 
         
return parent::_prepareColumns();
    }
 
    public function formattedStatus($value, $row, $column, $isExport) {
 
        return ucfirst($value);
    }
 
}