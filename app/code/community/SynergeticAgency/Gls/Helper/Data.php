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
 * @package    SynergeticAgency\Gls\Helper
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GLS helper data
 *
 * @category    SynergeticAgency
 * @package     SynergeticAgency_Gls
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_Gls_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Constant for the store config xml path name "carriers"
     */
    const XML_PATH_SHIPPING_METHODS = 'carriers';

    /**
     * Get underlineToCamelCase string
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @return mixed
     */
    function underlineToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    /**
     * Get available GLS shipping methods
     * @return array
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $store
     */
    public function getShippingMethodList($sorted = true, $asLabelValue = false, $store = null) {
        $methods = array();

        foreach ($this->getShippingMethods($store) as $code => $data) {
            if ((isset($data['title']))) {
                $methods[$code] = $data['title'];
            } else {
                if ($this->getMethodInstance($code)) {
                    $methods[$code] = $this->getMethodInstance($code)->getConfigData('title', $store);
                }
            }
        }

        if ($sorted) {
            asort($methods);
        }
        if ($asLabelValue) {
            $labelValues = array();
            foreach ($methods as $code => $title) {
                $labelValues[$code] = array();
            }
            foreach ($methods as $code => $title) {
                $labelValues[$code] = array('value' => $code, 'label' => $title);
            }
            return $labelValues;
        }

        return $methods;
    }

    /**
     * Get full magento configuration for shipping GLS method
     * @param null $store
     * @return mixed
     */
    public function getShippingMethods($store = null)
    {
        $shippingConfig = Mage::getStoreConfig(self::XML_PATH_SHIPPING_METHODS, $store);
        unset($shippingConfig['synergeticagency_gls']);
        return $shippingConfig;
    }

    /**
     * Check all config settings (Basiskonfiguration) for validity including if extension is active
     * @return mixed    true if everything is valid.
     *                  array containing the error list if at least one configuration is not valid
     */
    public function checkConfigSettings() {

        $returnValue = true;
        $errorList = array();
        $generalGlsConfig = $this->getGeneralGlsConfig();

        foreach( $generalGlsConfig AS $key => $value ){
            switch ($key) {
                case "active":
                    if( !preg_match("/^[0-1]$/", $value) ){
                        $returnValue = false;
                        $errorList["active"] = Mage::helper('synergeticagency_gls')->__('Value not allowed: ') . $value;
                    }
                    break;
                case "sandbox":
                    if( !preg_match("/^[0-1]$/", $value) ){
                        $returnValue = false;
                        $errorList["sandbox"] = Mage::helper('synergeticagency_gls')->__('Value not allowed: ') . $value;
                    }
                    break;
                case "logging_enabled":
                    if( !preg_match("/^[0-1]$/", $value) ){
                        $returnValue = false;
                        $errorList["logging_enabled"] = Mage::helper('synergeticagency_gls')->__('Value not allowed: ') . $value;
                    }
                    break;
                case "debug_enabled":
                    if( !preg_match("/^[0-1]$/", $value) ){
                        $returnValue = false;
                        $errorList["debug_enabled"] = Mage::helper('synergeticagency_gls')->__('Value not allowed: ') . $value;
                    }
                    break;
                case "connector_log_enabled":
                    if( !preg_match("/^[0-1]$/", $value) ){
                        $returnValue = false;
                        $errorList["connector_log_enabled"] = Mage::helper('synergeticagency_gls')->__('Value not allowed: ') . $value;
                    }
                    break;
                case "api_url":
                    if( empty($value) ){
                        $returnValue = false;
                        $errorList["api_url"] = Mage::helper('synergeticagency_gls')->__('Not set or empty') . ":" . var_export($value, 1);
                    }
                    break;
                case "json_url":
                    if(empty($value) ){
                        $returnValue = false;
                        $errorList["json_url"] = Mage::helper('synergeticagency_gls')->__('Not set or empty');
                    }
                    break;
                case "tracking_url":
                    if( empty($value) ){
                        $returnValue = false;
                        $errorList["tracking_url"] = Mage::helper('synergeticagency_gls')->__('Not set or empty');
                    }
                    break;
                case "username":
                    if( empty($value) ){
                        $returnValue = false;
                        $errorList["username"] = Mage::helper('synergeticagency_gls')->__('Not set or empty');
                    }
                    break;
                case "password":
                    if( empty($value) ){
                        $returnValue = false;
                        $errorList["password"] = Mage::helper('synergeticagency_gls')->__('Not set or empty');
                    }
                    break;
                case "customer_id":
                    if( empty($value) ){
                        $returnValue = false;
                        $errorList["customer_id"] = Mage::helper('synergeticagency_gls')->__('Not set or empty');
                    }
                    break;
                case "contact_id":
                    if( empty($value) ){
                        $returnValue = false;
                        $errorList["contact_id"] = Mage::helper('synergeticagency_gls')->__('Not set or empty');
                    }
                    break;
            }
        }

        if( $returnValue === false ){
            $returnValue = $errorList;
        }

       return $returnValue;
    }

    /**
     * Checks, if alternative shipper data is complete and valid
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function checkAlternativeShipper($store) {
        $checkFields = array(
            'name1',
            'street1',
            'country',
            'zip_code',
            'city',
        );
        foreach($checkFields as $checkField) {
            $fieldValue = Mage::getStoreConfig('gls/alternative_shipper/'.$checkField,$store);
            $fieldValue = trim($fieldValue);
            if(empty($fieldValue)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Checks, if return address data is complete and valid
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function checkReturnAddress($store) {
        $checkFields = array(
            'name1',
            'street1',
            'country',
            'zip_code',
            'city',
        );
        foreach($checkFields as $checkField) {
            $fieldValue = Mage::getStoreConfig('gls/return_address/'.$checkField,$store);
            $fieldValue = trim($fieldValue);
            if(empty($fieldValue)) {
                return false;
            }
        }
        return true;
    }

    /**

     * @return mixed
     */

    /**
     * Get magento GLS configuration defined by $key
     * Get complete magento GLS configuration if $key is not set
     *
     * @param null $key
     * @return mixed
     */
    public function getGeneralGlsConfig( $key = NULL )
    {
        $returnValue = Mage::getStoreConfig(
            'gls/general',
            Mage::app()->getStore()
        );

        if( $key !== NULL && isset($returnValue[$key]) ){
            $returnValue = $returnValue[$key];
        }
        return $returnValue;
    }


    /**
     * Get magento GLS shipment configuration defined by $key
     * Get complete magento GLS shipment configuration if $key is not set
     *
     * @param null $key
     * @return mixed
     */
    public function getShipmentGlsConfig( $key = NULL )
    {
        $returnValue = Mage::getStoreConfig(
            'gls/shipment',
            Mage::app()->getStore()
        );

        if( $key !== NULL && isset($returnValue[$key]) ){
            $returnValue = $returnValue[$key];
        }
        return $returnValue;
    }

    /**
     * returns current version number of gls extension
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->SynergeticAgency_Gls->version;
    }
}