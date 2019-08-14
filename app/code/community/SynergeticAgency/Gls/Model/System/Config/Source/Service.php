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
 * Used in creating options for GLS service config value selection
 *
 */
class SynergeticAgency_Gls_Model_System_Config_Source_Service
{

    /**
     * Options getter
     *
     * @param bool $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect=false)
    {
        /*$options = array(
            array('value' => 1, 'label'=>Mage::helper('synergeticagency_gls')->__('ThinkGreen Service')),
            array('value' => 2, 'label'=>Mage::helper('synergeticagency_gls')->__('FlexDelivery Service')),
            array('value' => 3, 'label'=>Mage::helper('synergeticagency_gls')->__('E-Mail-Notification Service')),
            array('value' => 4, 'label'=>Mage::helper('synergeticagency_gls')->__('PrivateDelivery Service')),
        );*/

        $gls = Mage::getModel('synergeticagency_gls/gls');
        $options = $gls->addonServicesToOptionArray(false);

        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        /*return array(
            1 => Mage::helper('synergeticagency_gls')->__('ThinkGreen Service'),
            2 => Mage::helper('synergeticagency_gls')->__('FlexDelivery Service'),
            3 => Mage::helper('synergeticagency_gls')->__('E-Mail-Notification Service'),
            4 => Mage::helper('synergeticagency_gls')->__('PrivateDelivery Service'),
        );*/

        $gls = Mage::getModel('synergeticagency_gls/gls');
        return $gls->addonServicesToOptionArray(false);
    }

}
