<?php

namespace Blm\CustomerDocuments\Ui\Component\Listing\Column;

class CustomerDocumentsActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_EDIT = 'blm_customerdocuments/items/edit';

    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * constructor
     * 
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $test=array();
        $arr=array();


        if (isset($dataSource['data']['items'])) {
           
            foreach ($dataSource['data']['items'] as & $item) {

            $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($item['entity_id']);

            $item['entity_id']=$customerObj['entity_id'];
            $item['firstname']=$customerObj['firstname'];
            $item['lastname']=$customerObj['lastname'];
            $item['email']=$customerObj['email'];

                $item[$this->getData('name')] = [
                    'view' => [
                        'href' => $item['image_src'],
                        'label' => __('View')
                    ],
                ];
            }

            file_put_contents("testowyxd.txt", file_get_contents("testowyxd.txt")."\n=========adres===========\n".print_r($dataSource, true));
        }
        return $dataSource;
    }
}