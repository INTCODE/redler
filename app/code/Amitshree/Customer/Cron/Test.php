<?php

namespace Amitshree\Customer\Cron;

use \Psr\Log\LoggerInterface;
use \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class Test {


  protected $_customerFactory;

  public function __construct(
    
    \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
    
) {
   
    $this->_customerFactory = $customerFactory;
    
}
  /**

    * Write to system.log

    *

    * @return void

  */

  public function getCustomerCollection()
{
    return $this->_customerFactory->create();
}


  public function execute() {


    $customerCollection = $this->getCustomerCollection();

    foreach ($customerCollection as $customer) {
      $date = date("Y-m-d",time());
      $date=strval($date);
      $date=$date." 00:00:00";
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($customer->getId());
      //$result = $date->format('Y-m-d H:i:s');
      $counter=0;
      if($customerObj['CheckedDate']==$date){
         $customerObj['approve_account']=1;
         $customerObj->save();
         $counter++;
          }
      } 
      
      if($counter>0){
        $msg="Dissaproved :". $counter." users";
        mail("w.chudek@gloo.pl","CRON - ". date("Y-m-d",time()),$msg);     
      }else{
        $msg="No problems";

        mail("w.chudek@gloo.pl","CRON - ". date("Y-m-d",time()),$msg);             

      }     
  }

}

