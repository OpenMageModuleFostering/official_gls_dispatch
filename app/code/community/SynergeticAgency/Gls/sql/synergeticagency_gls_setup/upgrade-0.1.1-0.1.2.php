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


$installer = new Mage_Customer_Model_Entity_Setup('core_setup');
$installer->startSetup();

/**
 * add attribute parcelshop_name to customer_address
 */
$installer->addAttribute('customer_address', 'parcelshop_name', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'GLS Parcelshop Name',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

/**
 * add attribute parcelshop_id to customer_address
 */
$installer->addAttribute('customer_address', 'parcelshop_id', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'GLS Parcelshop ID',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

/**
 * add attribute parcelshop_name to required forms
 */
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'parcelshop_name')
    ->setData('used_in_forms', array('customer_address_edit','adminhtml_customer_address','customer_register_address'))
    ->save();

/**
 * add attribute parcelshop_id to required forms
 */
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'parcelshop_id')
    ->setData('used_in_forms', array('customer_address_edit','adminhtml_customer_address','customer_register_address'))
    ->save();

// todo remove later just for fixing
$installer->getConnection()->dropColumn($installer->getTable('sales/quote_address'), 'parcelshop_id');
$installer->getConnection()->dropColumn($installer->getTable('sales/order_address'), 'parcelshop_id');
$installer->getConnection()->dropColumn($installer->getTable('sales/quote_address'), 'parcelshop_name');
$installer->getConnection()->dropColumn($installer->getTable('sales/order_address'), 'parcelshop_name');
$installer->getConnection()->dropColumn($installer->getTable('sales/quote_address'), 'to_parcelshop');
$installer->getConnection()->dropColumn($installer->getTable('sales/order_address'), 'to_parcelshop');


/**
 * add attribute parcelshop_id to quote_address table
 */
$installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'parcelshop_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'nullable' => true,
    'default' => null,
    'comment' => 'GLS Parcelshop id'
));

/**
 * add attribute parcelshop_name to quote_address table
 */
$installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'parcelshop_name', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'nullable' => true,
    'default' => null,
    'comment' => 'GLS Parcelshop name'
));

/**
 * add attribute parcelshop_id to order_address table
 */
$installer->getConnection()->addColumn($installer->getTable('sales/order_address'), 'parcelshop_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'nullable' => true,
    'default' => null,
    'comment' => 'GLS Parcelshop id'
));

/**
 * add attribute parcelshop_name to order_address table
 */
$installer->getConnection()->addColumn($installer->getTable('sales/order_address'), 'parcelshop_name', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'nullable' => true,
    'default' => null,
    'comment' => 'GLS Parcelshop name'
));


$installer->endSetup();