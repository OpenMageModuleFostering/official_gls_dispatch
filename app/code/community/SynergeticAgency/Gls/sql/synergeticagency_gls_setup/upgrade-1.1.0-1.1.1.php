<?php
/**
 * SynergeticAgency_Gls
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category   SynergetigAgency
 * @package    SynergeticAgency\Gls\sql\synergeticagency_gls_setup
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
    ->newTable($installer->getTable('synergeticagency_gls/shipment_job'))
    ->addColumn('gls_shipment_job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'GLS Shipment Job ID')
    ->addColumn('completed', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Completed')
    ->addColumn('in_process', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Job currently in process')
    ->addColumn('printed', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Job Printed')
    ->addColumn('qty_items_open', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Qty open items to process')
    ->addColumn('qty_items_successful', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Qty successful items')
    ->addColumn('qty_items_error', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0,
    ), 'Qty failed items')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
    ), 'Job creation date')
    ->addColumn('error_messages', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => true,
    ), 'Error Messages')
    ->addColumn('pdf_file', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'PDF File')
    ->setComment('GLS Massprint Shipment Job Table');
$installer->getConnection()->createTable($table);

$installer->getConnection()->addColumn($installer->getTable('synergeticagency_gls/shipment'),'job_id',array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => true,
    'default'   => null,
    'comment'   => 'Mass Print JobID'
));

$installer->getConnection()->addColumn($installer->getTable('synergeticagency_gls/shipment'),'pdf_file',array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => true,
    'length' => 255,
    'default'   => null,
    'comment'   => 'PDF File Path'
));

$installer->getConnection()->addColumn($installer->getTable('synergeticagency_gls/shipment'),'error_code',array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => true,
    'length' => 255,
    'default'   => null,
    'comment'   => 'GLS Error Code'
));

$installer->getConnection()->addColumn($installer->getTable('synergeticagency_gls/shipment'),'error_message',array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => true,
    'default'   => null,
    'comment'   => 'GLS Error Message'
));

$installer->endSetup();
