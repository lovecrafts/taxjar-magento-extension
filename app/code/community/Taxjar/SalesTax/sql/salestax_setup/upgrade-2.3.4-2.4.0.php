<?php
/**
 * Taxjar_SalesTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Taxjar
 * @package    Taxjar_SalesTax
 * @copyright  Copyright (c) 2017 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();

// orders
$installer->getConnection()->dropTable($installer->getTable('taxjar/order_synced'));
$ordersTable = $installer->getConnection()
    ->newTable($installer->getTable('taxjar/order_synced'))
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ),
        'Order Id'
    )
    ->addColumn(
        'synced_at',
        Varien_Db_Ddl_Table::TYPE_DATETIME,
        null,
        array('nullable' => false),
        'Last syncroniation time'
    )
    ->setComment('Orders syncroniation time with TaxJar API');
$installer->getConnection()->createTable($ordersTable);

// creditmemos
$installer->getConnection()->dropTable($installer->getTable('taxjar/creditmemo_synced'));
$creditMemosTable = $installer->getConnection()
    ->newTable($installer->getTable('taxjar/creditmemo_synced'))
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ),
        'Order Id'
    )
    ->addColumn(
        'synced_at',
        Varien_Db_Ddl_Table::TYPE_DATETIME,
        null,
        array('nullable' => false),
        'Last syncroniation time'
    )
    ->setComment('Creditmemos syncroniation time with TaxJar API');
$installer->getConnection()->createTable($creditMemosTable);

$installer->endSetup();
