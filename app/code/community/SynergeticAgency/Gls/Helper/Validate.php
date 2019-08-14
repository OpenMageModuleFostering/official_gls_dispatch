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
 * This class implements helper functions for the validation of several models, such as GLS shipment, etc.
 * Class SynergeticAgency_Gls_Helper_Validate
 */
class SynergeticAgency_Gls_Helper_Validate extends Mage_Core_Helper_Abstract {

    /**
     * Check if destination of current shipment is for other country than origin shipping country of current store
     * @return bool|null
     */
    public function isDomestic() {
        $shipment = SynergeticAgency_Gls_Helper_Validate::getCurrentShipment();
        $return = null;
        if($shipment) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $this->getStore());
            $destCountry = $shipment->getShippingAddress()->getCountryId();
            $return = $storeCountry == $destCountry;
        }
        return $return;
    }

    /**
     * Shipment to parcel shop? True if a parcel shop ID is given
     * @return bool
     */
    public function isParcelshopDelivery() {
        $parcelShopId = SynergeticAgency_Gls_Helper_Validate::getCurrentShipment()->getShippingAddress()->getParcelshopId();
        return !empty($parcelShopId);
    }

    /**
     * Gets Json Config for specific origin country supplied by gls
     * @return string
     */
    public function getJsonConfig() {
        $jsonData = '{}';
        $jsonConfig = '{}';
        if(SynergeticAgency_Gls_Helper_Validate::getCurrentShipment()) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                $this->getStore());

            $jsonConfig = Mage::getModel('synergeticagency_gls/jsonimport')->getCountry($storeCountry);
            if($jsonConfig) {
                $writer = new Zend_Config_Writer_Json();
                $writer->setConfig($jsonConfig);
                $jsonData = $writer->render();
            }
        }
        return $jsonData;
    }

    /**
     * Gets config for specific origin country supplied by gls
     * @return string
     */
    public function getConfig() {
        $jsonConfig = '';
        if(SynergeticAgency_Gls_Helper_Validate::getCurrentShipment()) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                $this->getStore());

            $jsonConfig = Mage::getModel('synergeticagency_gls/jsonimport')->getCountry($storeCountry);

        }
        return $jsonConfig;
    }

    /**
     * get country config as json
     * @return string
     */
    public function getCountriesJson() {
        $shipment = SynergeticAgency_Gls_Helper_Validate::getCurrentShipment();
        $jsonData = '{}';
        if($shipment) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $this->getStore());
            $glsCountry = Mage::getModel('synergeticagency_gls/country')->load($storeCountry);
            if($glsCountry->getId()) {
                $jsonData = Mage::helper('core')->jsonEncode($glsCountry->getData());
            }
        }
        return $jsonData;
    }

    /**
     * get country config
     * @return string
     */
    public function getCountriesConfig() {
        $shipment = SynergeticAgency_Gls_Helper_Validate::getCurrentShipment();
        if($shipment) {
                $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, SynergeticAgency_Gls_Helper_Validate::getStore());
            $glsCountry = Mage::getModel('synergeticagency_gls/country')->load($storeCountry);
        }
        return $glsCountry;
    }

    /**
     * get a (service) combination by id
     * @param $countryConfigOptions
     * @param $combinationId
     * @return bool
     */
    public function getCombinationByCombinationId($countryConfigOptions, $combinationId){
        $combination = false;
        foreach($countryConfigOptions AS $countryConfigOption){
            if($countryConfigOption['combination'] == $combinationId){
                $combination = $countryConfigOption;
            }
        }
        return $combination;
    }


    /**
     * Get the current shipment method. Shipment methods are actually combinations of gls services
     * @return int
     */
    public function getParcelShopCombinationId() {
        return SynergeticAgency_Gls_Helper_Validate::isDomestic() ? SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY : SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY;
    }

    /**
     * Gets current delivery country
     * @return string
     */
    public function getTargetCountry() {
        return SynergeticAgency_Gls_Helper_Validate::getCurrentShipment()->getShippingAddress()->getCountryId();
    }

    /**
     * is target delivery country allowed from source country
     * @return bool
     */
    public function isAllowedTargetCountry(){

        $config = SynergeticAgency_Gls_Helper_Validate::getConfig();

        $countryValidator = new Zend_Validate_InArray(array_keys($config->foreign->countries));
        return $countryValidator->isValid(SynergeticAgency_Gls_Helper_Validate::getTargetCountry());
    }

    /**
     * get current shipment
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getCurrentShipment() {
        return Mage::registry('current_shipment');
    }

    /**
     * Get current store
     * @return Mage_Core_Model_Store
     */
    public function getStore() {
        return SynergeticAgency_Gls_Helper_Validate::getCurrentShipment()->getStore();
    }
}
