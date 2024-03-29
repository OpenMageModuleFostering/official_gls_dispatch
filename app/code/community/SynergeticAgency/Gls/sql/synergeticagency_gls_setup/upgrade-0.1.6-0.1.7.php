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

$installer->getConnection()->addColumn($installer->getTable('synergeticagency_gls/shipment_parcel'),'trackId',array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 10,
    'nullable'  => true,
    'comment' => 'GLS Track id'
));

$installer->getConnection()->addColumn($installer->getTable('synergeticagency_gls/shipment_parcel'),'location',array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'nullable'  => true,
    'comment' => 'GLS Track location'
));

$installer->getConnection()->dropColumn($installer->getTable('synergeticagency_gls/shipment_parcel'),'ndi_number');
$installer->getConnection()->dropColumn($installer->getTable('synergeticagency_gls/shipment_parcel'),'primary2d');
$installer->getConnection()->dropColumn($installer->getTable('synergeticagency_gls/shipment_parcel'),'secondary2d');
$installer->getConnection()->dropColumn($installer->getTable('synergeticagency_gls/shipment_parcel'),'national_ref');


$installer->endSetup();