<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\QuickBooksOnline\Model\Config\Source;

/**
 * Class NameRule
 *
 * @package Magenest\QuickBooksOnline\Model\Config\Source
 */
class NameRule implements \Magento\Framework\Option\ArrayInterface
{

    const USE_NAME = 1;
    const USE_SKU = 2;
    const USE_BOTH = 3;

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::USE_NAME => __('Use product name and trim long names to fit with QBO'),
            self::USE_SKU => __('Use SKU'),
            self::USE_BOTH => __('Use both but trim it to fit with QBO')
        ];
    }

    /**
     * Return options array
     * @return array
     */
    public function toOptionArray()
    {
        return $this->toArray();
    }
}
