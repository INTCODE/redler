<?php


namespace Amitshree\Customer\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;

use Magento\Framework\DB\Ddl\Table;

class CustomerYesNoOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Option values
     */

    const VALUE_NO = 1;
    const VALUE_YES = 2;

    /**
     * @var optionFactory
     */
    protected $optionFactory;

    /*
     * Retrieve all options array
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Dissaproved'), 'value' => self::VALUE_NO],
            ['label' => __('Approved'), 'value' => self::VALUE_YES],
        ];
        return $this->_options;
    }

    /**
     *  retrive option array
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach($this->getAllOptions() as $option)
        {
            $_options[$option['value']] = $option['label'];
            return $_options;
        }
    }

    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}