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


/**
 * Create table 'synergeticagency_gls/shipment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('synergeticagency_gls/shipment'))
    ->addColumn('gls_shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'GLS Shipment ID')
    ->addColumn('shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        'default'   => null,
    ), 'Shipment ID')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        'default'   => null,
    ), 'Order ID')
    ->addColumn('gls_product', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS Product')
    ->addColumn('gls_services', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS Services')
    ->addColumn('consignment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS Consignement ID')
    ->addColumn('sandbox', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Sandbox')
    ->addColumn('printed', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Shipment Printed')
    ->addForeignKey($installer->getFkName('synergeticagency_gls/shipment', 'shipment_id', 'sales/shipment', 'entity_id'),
        'shipment_id', $installer->getTable('sales/shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_SET_NULL)
    ->addForeignKey($installer->getFkName('synergeticagency_gls/shipment', 'order_id', 'sales/order', 'entity_id'),
        'order_id', $installer->getTable('sales/order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_SET_NULL)
    ->setComment('GLS Shipment Table');
$installer->getConnection()->createTable($table);


/**
 * Create table 'synergeticagency_gls/shipment_parcel'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('synergeticagency_gls/shipment_parcel'))
    ->addColumn('gls_shipment_parcel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'GLS Shipment Parcel ID')
    ->addColumn('gls_shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'GLS Shipment ID')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '6,2', array(
        'nullable'  => false,
        'default'   => '0.00',
    ), 'Parcel Weight')
    ->addColumn('cashservice', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.00',
    ), 'Parcel Cashservice Amount')
    ->addColumn('unique_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS Tracking Number')
    ->addColumn('parcel_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS Parcel Number')
    ->addColumn('ndi_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS Ndi Number')
    ->addColumn('primary2d', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS Primary 2d Barcode')
    ->addColumn('secondary2d', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS Secondary 2d Barcode')
    ->addColumn('national_ref', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'GLS National Reference Number')
    ->addForeignKey($installer->getFkName('synergeticagency_gls/shipment_parcel', 'gls_shipment_id', 'synergeticagency_gls/shipment', 'gls_shipment_id'),
        'gls_shipment_id', $installer->getTable('synergeticagency_gls/shipment'), 'gls_shipment_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('GLS Shipment Parcel Table');
$installer->getConnection()->createTable($table);


/**
 * Create table 'synergeticagency_gls/shipment_address'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('synergeticagency_gls/shipment_address'))
    ->addColumn('gls_shipment_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'GLS Shipment Address ID')
    ->addColumn('gls_shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'GLS Shipment ID')
    ->addColumn('name1', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'Name 1')
    ->addColumn('name2', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'Name 2')
    ->addColumn('name3', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'Name 3')
    ->addColumn('street1', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'Street 1')
    ->addColumn('country_num', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        'nullable'  => true,
        'default'   => null,
    ), 'Country ISO 3166-1 numeric')
    ->addColumn('zip_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'ZIP Code')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'City')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'E-Mail')
    ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        'default'   => null,
    ), 'Phone Number')
    ->addForeignKey($installer->getFkName('synergeticagency_gls/shipment_address', 'gls_shipment_id', 'synergeticagency_gls/shipment', 'gls_shipment_id'),
        'gls_shipment_id', $installer->getTable('synergeticagency_gls/shipment'), 'gls_shipment_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('GLS Shipment Address Table');
$installer->getConnection()->createTable($table);

/*
 * Add ISO 3166-1 N3 column to directory_country table
 */
$installer->getConnection()->addColumn($installer->getTable('directory/country'), 'iso_n3_code', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 3,
    'nullable' => true,
    'default' => null,
    'comment' => 'ISO 3166-1 N3'
));


$installer->endSetup();