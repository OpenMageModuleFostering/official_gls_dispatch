<?php
/**
 * Magento
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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Model_System_Config_Source_Country
 */
class SynergeticAgency_Gls_Model_System_Config_Source_Country
{
    /**
     * Options for available shipping countries in magento backend spipping method GLS
     * @var
     */
    protected $_options;

    /**
     * Getting options for available shipping countries in magento backend spipping method GLS
     * @param bool|false $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $countries = Mage::getResourceModel('directory/country_collection')->load();
            $sort = array();
            foreach($countries as $country) {
                $name = Mage::app()->getLocale()->getCountryTranslation($country->getCountryId());
                if($country->getIso2Code() && !empty($name)) {
                    //$this->_options[] = array('value' => $country->getIsoN3Code(), 'label'=> $name);
                    if (!empty($name)) {
                        $sort[$name] = $country->getIso2Code();
                    }
                }
            }
            Mage::helper('core/string')->ksortMultibyte($sort);
            $options = array();
            foreach ($sort as $label => $value) {
                $options[] = array(
                    'value' => $value,
                    'label' => $label
                );
            }
            $this->_options = $options;
        }


        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;
    }
}
