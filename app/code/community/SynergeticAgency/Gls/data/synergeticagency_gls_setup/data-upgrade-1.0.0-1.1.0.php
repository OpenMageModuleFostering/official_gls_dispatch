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
 * @package    SynergeticAgency\Gls\data\synergeticagency_gls_setup
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/**
 * Install order statuses from config
 */
$data     = array();
$statuses = array(
    SynergeticAgency_Gls_Model_Gls::GLS_STATUS_PENDING => array('label' => 'Pending GLS'),
    SynergeticAgency_Gls_Model_Gls::GLS_STATUS_PENDING_ERROR => array('label' => 'Pending GLS Error'),
    SynergeticAgency_Gls_Model_Gls::GLS_STATUS_PROCESSING => array('label' => 'Processing GLS'),
    SynergeticAgency_Gls_Model_Gls::GLS_STATUS_PROCESSING_ERROR => array('label' => 'Processing GLS Error'),
    SynergeticAgency_Gls_Model_Gls::GLS_STATUS_COMPLETE => array('label' => 'Complete GLS')
);
foreach ($statuses as $code => $info) {
    $data[] = array(
        'status' => $code,
        'label'  => $info['label']
    );
}
$installer->getConnection()->insertArray(
    $installer->getTable('sales/order_status'),
    array('status', 'label'),
    $data
);

/**
 * Install order states from config
 */
$data   = array();
$states = array(
    Mage_Sales_Model_Order::STATE_NEW => array(
        'statuses' => array(
            SynergeticAgency_Gls_Model_Gls::GLS_STATUS_PENDING => array(),
            SynergeticAgency_Gls_Model_Gls::GLS_STATUS_PENDING_ERROR => array()
        )
    ),
    Mage_Sales_Model_Order::STATE_PROCESSING => array(
        'statuses' => array(
            SynergeticAgency_Gls_Model_Gls::GLS_STATUS_PROCESSING => array(),
            SynergeticAgency_Gls_Model_Gls::GLS_STATUS_PROCESSING_ERROR => array()
        )
    ),
    Mage_Sales_Model_Order::STATE_COMPLETE => array(
        'statuses' => array(
            SynergeticAgency_Gls_Model_Gls::GLS_STATUS_COMPLETE => array()
        )
    )
);

foreach ($states as $code => $info) {
    if (isset($info['statuses'])) {
        foreach ($info['statuses'] as $status => $statusInfo) {
            $data[] = array(
                'status'     => $status,
                'state'      => $code,
                'is_default' => is_array($statusInfo) && isset($statusInfo['@']['default']) ? 1 : 0
            );
        }
    }
}
$installer->getConnection()->insertArray(
    $installer->getTable('sales/order_status_state'),
    array('status', 'state', 'is_default'),
    $data
);
