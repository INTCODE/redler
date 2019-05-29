<?php
/**
 *  
  
 * 
 */

namespace Blm\CustomerDocuments\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
		$installer->startSetup();

		/**
		 * Creating table blm_customerdocuments
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('blm_customerdocuments')
		)->addColumn(
			'customerdocuments_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Document Id'
		)->addColumn(
			'entity_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['unsigned' => true, 'nullable' => false],
			'Entity Id'
		)->addColumn(
			'Document',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true,'default' => null],
			'Document'
		)->addForeignKey(
			$installer->getFkName(
				'blm_customerdocuments',
				'entity_id',
				'customer_entity',
				'entity_id'
			),
			'entity_id',
			$installer->getTable('customer_entity'),
			'entity_id',
			\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->setComment(
            'Blm CustomerDocuments Table'
        );
		$installer->getConnection()->createTable($table);
		$installer->endSetup();
	}
}