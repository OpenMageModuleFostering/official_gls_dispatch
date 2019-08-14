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
    'combinations' => [
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL,
            'product'       => SynergeticAgency_Gls_Model_Gls::PRODUCT_BUSINESS_PARCEL,
            'services'      => [],
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_CASHSERVICE,
            'product'       => SynergeticAgency_Gls_Model_Gls::PRODUCT_BUSINESS_PARCEL,
            'services'      => [
                SynergeticAgency_Gls_Model_Gls::SERVICE_CASHSERVICE,
            ],
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_GUARANTEED,
            'product'       => SynergeticAgency_Gls_Model_Gls::PRODUCT_BUSINESS_PARCEL,
            'services'      => [
                SynergeticAgency_Gls_Model_Gls::SERVICE_GUARANTEED,
            ],
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY,
            'product'       => SynergeticAgency_Gls_Model_Gls::PRODUCT_BUSINESS_PARCEL,
            'services'      => [
                SynergeticAgency_Gls_Model_Gls::SERVICE_SHOPDELIVERY,
            ],
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL,
            'product'       => SynergeticAgency_Gls_Model_Gls::PRODUCT_EUROBUSINESS_PARCEL,
            'services'      => [],
        ],
        [
            'id'            => SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY,
            'product'       => SynergeticAgency_Gls_Model_Gls::PRODUCT_EUROBUSINESS_PARCEL,
            'services'      => [
                SynergeticAgency_Gls_Model_Gls::SERVICE_SHOPDELIVERY,
            ],
        ],
    ]
];