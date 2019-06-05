<?php
namespace Blm\CustomerDocuments\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'customerdocuments_id';
	protected $_eventPrefix = 'blm_customerdocuments_post_collection';
	protected $_eventObject = 'post_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Blm\CustomerDocuments\Model\Post', 'Blm\CustomerDocuments\Model\ResourceModel\Post');
	}

}

