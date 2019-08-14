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
 * to info@synergetic.ag so we can send you a copy immediately.
 *
 *
 * @category   SynergetigAgency
 * @package    SynergeticAgency\Gls\config
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

//notice will show up in packaging form
return [
    'services' => [
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::SERVICE_THINKGREEN,
            'name'          => 'ThinkGreenService',
            'notice'        => '',
            'is_addon'      => true,
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::SERVICE_FLEXDELIVERY,
            'name'          => 'FlexDeliveryService',
            'notice'        => '',
            'is_addon'      => true,
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::SERVICE_EMAILNOTIFICATION,
            'name'          => 'E-Mail-NotificationService',
            'notice'        => '',
            'is_addon'      => true,
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::SERVICE_PRIVATEDELIVERY,
            'name'          => 'PrivateDeliveryService',
            'notice'        => '',
            'is_addon'      => true,
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::SERVICE_CASHSERVICE,
            'name'          => 'CashService',
            'notice'        => '',
            'is_addon'      => false,
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::SERVICE_GUARANTEED,
            'name'          => 'Guaranteed24Service',
            'notice'        => '',
            'is_addon'      => false,
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::SERVICE_SHOPDELIVERY,
            'name'          => 'ShopDeliveryService',
            'notice'        => '',
            'is_addon'      => false,
        ],
    ]
];