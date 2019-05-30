<?php
    /**
     * Webkul Hello Hello Edit Tab Grid Block
     *
     * @category    Webkul
     * @package     Webkul_Hello
     * @author      Webkul Software Private Limited
     *
     */
    namespace Blm\CustomerDocuments\Block\Adminhtml\CustomerDocuments\Edit\Tab;
 
    class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
    {
        /**
         * @var \Webkul\Hello\Model\GridFactory
         */
        protected $productFactory;
 
        public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Backend\Helper\Data $backendHelper,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Framework\Registry $coreRegistry,
            array $data = []
        ) {
            $this->productFactory = $productFactory;
            $this->_coreRegistry = $coreRegistry;
            parent::__construct($context, $backendHelper, $data);
        }
 
        /**
         * @return void
         */
        protected function _construct()
        {
            parent::_construct();
            $this->setId('hello_tab_grid');
            $this->setDefaultSort('entity_id');
            $this->setUseAjax(true);
        }
 
        /**
         * @return Grid
         */
        protected function _prepareCollection()
        {
            $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect("*");
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
 
        /**
         * @return Extended
         */
        protected function _prepareColumns()
        {
            $this->addColumn(
                'entity_id',
                [
                    'header' => __('Product Id'),
                    'sortable' => true,
                    'index' => 'entity_id',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id'
                ]
            );
            $this->addColumn(
                'name',
                [
                    'header' => __('Product Name'),
                    'index' => 'name'
                ]
            );
            $this->addColumn(
                'sku',
                [
                    'header' => __('Sku'),
                    'index' => 'sku'
                ]
            );
 
            return parent::_prepareColumns();
        }
 
        /**
         * @return string
         */
        public function getGridUrl()
        {
            return $this->getUrl('customerDocuments/*/controllGrid', ['_current' => true]);
        }
         
    }