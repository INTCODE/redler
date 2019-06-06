<?php
/**
  
 
 
  
 
 */

namespace Blm\CronTab\Ui\Component\Listing\Column;

class CronTabActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_EDIT = 'blm_crontab/items/edit';

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


        if (isset($dataSource['data']['items'])) {
            $totRec=$dataSource['data']['totalRecords'];
            foreach ($dataSource['data']['items'] as $k => & $item) {


                $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($item['entity_id']);


                $item['CheckedDate']=$customerObj['CheckedDate'];
                $item['approve_account']=$customerObj['approve_account'];
                $totRec=$dataSource['data']['totalRecords'];
                

                if($item['approve_account']==2){
                    unset($dataSource['data']['items'][$k]);
                    $totRec--;
                }elseif($item['approve_account']==1){
                    $item['approve_account']=__("Dissaproved");
                }else{
                    $item['approve_account']=__("Pending");
                }
                
                $item[$this->getData('name')] = [
                    'edit' => [
                        'href' => $this->_urlBuilder->getUrl(
                            'customer/*/edit',
                            [
                                'id' => $item['entity_id']
                            ]
                        ),
                        'label' => __('Edit')
                    ],
                ];
             
            }

            $dataSource['data']['items'] = array_values($dataSource['data']['items']);

          
        }
        return $dataSource;
    }
}