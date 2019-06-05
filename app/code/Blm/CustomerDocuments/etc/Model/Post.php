<?php
namespace Blm\CustomerDocuments\Model;
class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'blm_customerdocuments_post';

	protected $_cacheTag = 'blm_customerdocuments_post';

	protected $_eventPrefix = 'blm_customerdocuments_post';

	protected function _construct()
	{
		$this->_init('Blm\CustomerDocuments\Model\ResourceModel\Post');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
