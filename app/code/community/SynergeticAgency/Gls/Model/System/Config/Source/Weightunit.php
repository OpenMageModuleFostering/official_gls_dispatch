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
 * @package    SynergeticAgency\Gls\Model\System\Config\Source
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Used in creating options for GLS weight configuration
 *
 */
class SynergeticAgency_Gls_Model_System_Config_Source_Weightunit
{

    public function toOptionArray()
    {
        return [
            [
                'value' => SynergeticAgency_Gls_Model_Gls::GLS_WEIGHT_UNIT_G,
                'label' => Mage::helper('synergeticagency_gls')->__(SynergeticAgency_Gls_Model_Gls::GLS_WEIGHT_UNIT_G)
            ],
            [
                'value' => SynergeticAgency_Gls_Model_Gls::GLS_WEIGHT_UNIT_KG,
                'label' => Mage::helper('synergeticagency_gls')->__(SynergeticAgency_Gls_Model_Gls::GLS_WEIGHT_UNIT_KG)
            ]
        ];
    }
}
