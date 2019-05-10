<?php
/**
 * Created by PhpStorm.
 * User: thang
 * Date: 15/06/2018
 * Time: 14:11
 */

namespace Magenest\QuickBooksOnline\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\Product;
use Magento\Tax\Model\Calculation\Rate;
use Magenest\QuickBooksOnline\Model\TaxFactory;
use Magento\Framework\App\State;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;
    private $eavConfig;
    private $rate;
    private $taxFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        Rate $rate,
        TaxFactory $taxFactory,
        State $state
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->rate = $rate;
        $this->taxFactory = $taxFactory;
        $state->setAreaCode('global');
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.3.2') < 0) {
            $this->addCustomerQboIdAttr($setup);
            $this->addProductQboIdAttr($setup);

        }
        if (version_compare($context->getVersion(), '2.3.3') < 0) {
            $this->removeProductQboIdAttr($setup);
            $this->addProductQboIdAttr($setup);

        }
        if (version_compare($context->getVersion(), '2.4.1') < 0) {
            $this->addTaxCodes();
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addCustomerQboIdAttr(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'qbo_id',
            [
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'system' => 0,
//                'user_defined' => true
            ]
        );
//        $qboIdAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'qbo_id');
//        $qboIdAttribute->setData('used_in_forms', ['adminhtml_customer']);
//        $qboIdAttribute->save();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function addProductQboIdAttr(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'qbo_id',
            [
                'type' => 'varchar',
                'required' => false,
                'visible' => false,
                'nullable' => true,
                'input' => 'text',
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function removeProductQboIdAttr(ModuleDataSetupInterface $setup){
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(
            Product::ENTITY,
            'qbo_id'
        );
    }

    /**
     * Add existing tax codes to qbonline tax table
     * @throws \Exception
     */
    private function addTaxCodes()
    {
        $collections = $this->rate->getCollection();
        /** @var \Magento\Tax\Model\Calculation\Rate $collections */
        foreach ($collections as $tax) {
            $data = [
                'tax_id' => $tax->getId(),
                'tax_code' => trim($tax->getCode()),
                'rate' => $tax->getRate(),
                'tax_rate_name' => trim($tax->getCode()).'_'.$tax->getId(),
            ];
            $model = $this->taxFactory->create()->load($tax->getCode(), 'tax_code');
            $model->addData($data)->save();
        }
        $taxZero = [
            'tax_id' => 1000,
            'tax_code' => 'tax_zero_qb',
            'rate' => 0,
            'tax_rate_name' => 'tax_zero_qb_1000',
        ];

        $model = $this->taxFactory->create()->load('tax_zero_qb', 'tax_code');
        $model->addData($taxZero)->save();
    }
}
