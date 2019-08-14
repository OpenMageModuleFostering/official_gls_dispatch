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

return [
    'countries' => [
        [
            'id' => 'DE',
            'options'  => [
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL,
                    'addon_services' => [
                        SynergeticAgency_Gls_Model_Gls::SERVICE_THINKGREEN,
                        SynergeticAgency_Gls_Model_Gls::SERVICE_FLEXDELIVERY,
                    ],
                    'domestic' => true,
                    'foreign' => false,
                    'cashservice' => false,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_CASHSERVICE,
                    'addon_services' => [
                        #SynergeticAgency_Gls_Model_Gls::SERVICE_THINKGREEN,
                    ],
                    'domestic' => true,
                    'foreign' => false,
                    'cashservice' => true,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_GUARANTEED,
                    'addon_services' => [
                        #SynergeticAgency_Gls_Model_Gls::SERVICE_THINKGREEN,
                    ],
                    'domestic' => true,
                    'foreign' => false,
                    'cashservice' => false,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY,
                    'addon_services' => [
                        SynergeticAgency_Gls_Model_Gls::SERVICE_THINKGREEN,
                    ],
                    'domestic' => true,
                    'foreign' => false,
                    'cashservice' => false,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL,
                    'addon_services' => [
                        SynergeticAgency_Gls_Model_Gls::SERVICE_THINKGREEN,
                    ],
                    'domestic' => false,
                    'foreign' => true,
                    'cashservice' => false,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY,
                    'addon_services' => [],
                    'domestic' => false,
                    'foreign' => true,
                    'cashservice' => false,
                ],
            ],
        ],
        [
            'id' => 'DK',
            'options'      => [
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL,
                    'addon_services' => [
                        SynergeticAgency_Gls_Model_Gls::SERVICE_EMAILNOTIFICATION,
                        SynergeticAgency_Gls_Model_Gls::SERVICE_PRIVATEDELIVERY,
                    ],
                    'domestic' => true,
                    'foreign' => false,
                    'cashservice' => false,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_CASHSERVICE,
                    'addon_services' => [
                        SynergeticAgency_Gls_Model_Gls::SERVICE_EMAILNOTIFICATION,
                        SynergeticAgency_Gls_Model_Gls::SERVICE_PRIVATEDELIVERY,
                    ],
                    'domestic' => true,
                    'foreign' => false,
                    'cashservice' => true,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY,
                    'addon_services' => [],
                    'domestic' => true,
                    'foreign' => false,
                    'cashservice' => false,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL,
                    'addon_services' => [],
                    'domestic' => false,
                    'foreign' => true,
                    'cashservice' => false,
                ],
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY,
                    'addon_services' => [],
                    'domestic' => false,
                    'foreign' => true,
                    'cashservice' => false,
                ],
            ],
        ],
        [
            'id' => 'FI',
            'options'      => [
                [
                    'combination' => SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL,
                    'addon_services' => [],
                    'domestic' => true,
                    'foreign' => true,
                    'cashservice' => false,
                ],
            ],
        ],
    ]
];