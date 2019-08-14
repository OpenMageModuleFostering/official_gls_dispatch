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
* @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Order\Shipment\Create
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Gls
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Gls extends Mage_Adminhtml_Block_Template {

    /**
     * The Constructor initiates the template for the used Mage_Adminhtml_Block_Template
     */
    public function _construct()
    {
        $this->setTemplate('gls/sales/order/shipment/create/gls.phtml');
        parent::_construct();
    }

    /**
     * Returns true, if GLS basis configuration "gls/shipment/return_label_enabled" is set to '1'
     * Returns false, if GLS basis configuration "gls/shipment/return_label_enabled" is not set to '1'
     *
     * @return bool
     */
    public function getReturnLabel() {
        return Mage::getStoreConfig('gls/shipment/return_label_enabled',$this->getStore()) === '1';
    }

    /**
     * Returns GLS shipping configuration "gls/shipment/labelsize"
     *
     * @return string
     */
    public function getLabelsize() {
        return Mage::getStoreConfig('gls/shipment/labelsize',$this->getStore());
    }

    /**
     * Returns true, if destination of current shipment is allowed by current Magento store configuration and GLS basis configuration.
     * Returns false, if destination of current shipment is not allowed by current Magento store configuration  and GLS basis configuration.
     *
     * @return bool
     */
    public function isCountryAvailable() {
        $glsModel = Mage::getModel('synergeticagency_gls/gls');
        $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $this->getStore());
        $destCountry = $this->_getCurrentShipment()->getShippingAddress()->getCountryId();
        return ($glsModel->isCountryOriginIdAvailable($storeCountry)
            && ($this->isDomestic() || $glsModel->isDestinationCountryAvailable($storeCountry,$destCountry)));
    }

    /**
     * Returns options array of current GLS product and GLS service combination
     *
     * @return array
     */
    public function getCombinations() {
        $options =  Mage::getModel('synergeticagency_gls/gls')->productServiceCombinationsToOptionArray();
        array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        return $options;
    }

    /**
     * Returns the "d.m.Y" formatted date string representation of "today"
     *
     * @return bool|string
     */
    public function getShippingDate() {
        return date('d.m.Y', strtotime('today'));
    }

    /**
     * Returns true, if the given combinationId is allowed by given values such as "isDomestic", "isStandardShipping", etc
     * and if the given combinationId is defined as constant of SynergeticAgency_Gls_Model_Gls
     * Otherwise, this function returns false
     *
     * @param $combinationId
     * @return bool
     */
    public function isCombinationSelected($combinationId) {
        $return = false;
        switch ($combinationId) {
            case SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL:
                if($this->isDomestic() && ($this->isStandardShipping()) && !$this->isParcelshopDelivery() && !$this->isCashService()) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_GUARANTEED:
                if($this->isDomestic() && $this->isExpressShipping()) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY:
                if($this->isDomestic() && $this->isParcelshopDelivery()) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_CASHSERVICE:
                if($this->isDomestic() && $this->isCashService()) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL:
                if((!$this->isDomestic() || $this->matchCountry('FI')) && !$this->isParcelshopDelivery() && $this->isForeignShipping()) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY:
                if(!$this->isDomestic() && $this->isParcelshopDelivery()) {
                    $return = true;
                }
                break;
        }
        return $return;
    }

    /**
     * Returns true, if the given serviceId is allowed by given values
     * and if the given serviceId is defined as constant of SynergeticAgency_Gls_Model_Gls
     * Otherwise, this function returns false
     *
     * @param $serviceId
     * @return bool
     */
    public function isServiceSelected($serviceId) {
        $return = false;
        switch($serviceId) {

        }
        return $return;
    }

    /**
     * Returns true, if current shipping method is a GLS "Standard" shipping
     * Otherwise, this function returns false
     *
     * @return bool
     */
    public function isStandardShipping() {
        return $this->_getCurrentShipment()->getOrder()->getShippingMethod() == SynergeticAgency_Gls_Model_Carrier::CODE.'_'.SynergeticAgency_Gls_Model_Carrier::SHIPPING_RATE_STANDARD;
    }

    /**
     * Returns true, if current shipping method is a GLS "Express" shipping
     * Otherwise, this function returns false
     *
     * @return bool
     */
    public function isExpressShipping() {
        return $this->_getCurrentShipment()->getOrder()->getShippingMethod() == SynergeticAgency_Gls_Model_Carrier::CODE.'_'.SynergeticAgency_Gls_Model_Carrier::SHIPPING_RATE_EXPRESS;
    }

    /**
     * Returns true, if current shipping method is a GLS "Foreign" shipping
     * Otherwise, this function returns false
     *
     * @return bool
     */
    public function isForeignShipping() {
        return $this->_getCurrentShipment()->getOrder()->getShippingMethod() == SynergeticAgency_Gls_Model_Carrier::CODE.'_'.SynergeticAgency_Gls_Model_Carrier::SHIPPING_RATE_FOREIGNCOUNTRIES;
    }

    /**
     * Returns true, if given country code(ISO-3166 Alpha 2) matches to the configured country of current store
     * Returns false, if given country code(ISO-3166 Alpha 2) does nit match to the configured country of current store
     *
     * @param $countryCode
     * @return bool
     */
    public function matchCountry( $countryCode ) {
        $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $this->getStore());
        return strtoupper( $storeCountry ) === strtoupper( $countryCode );
    }

    /**
     * Determine "Shipment to parcelshop"
     * Returns true, if parcelshopId is set in shipment address of current shipment
     * Returns false, if parcelshopId is not set or NULL in shipment address of current shipment
     *
     * @return bool
     */
    public function isParcelshopDelivery() {
        $parcelShopId = $this->_getCurrentShipment()->getShippingAddress()->getParcelshopId();
        return !empty($parcelShopId);
    }

    /**
     * Returns true, if payment method of current shipping is an instance of SynergeticAgency_Gls_Model_Glscashondelivery
     * Returns false, if payment method of current shipping is not an instance of SynergeticAgency_Gls_Model_Glscashondelivery
     *
     * @return bool
     */
    public function isCashService() {
        $shipment = $this->_getCurrentShipment();
        if($shipment) {
            if($shipment->getOrder()->getPayment()->getMethodInstance() instanceof SynergeticAgency_Gls_Model_Glscashondelivery) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the configured "gls/shipment/default_combination" code as integer value.
     * Otherwise, this function returns integer value 0
     *
     * @return int
     */
    public function getDefaultCombination() {
        $defaultCombination = (int)Mage::getStoreConfig('gls/shipment/default_combination', $this->getStore());
        return $defaultCombination ? $defaultCombination : 0;
    }

    /**
     * Returns the configured "gls/shipment/default_services" representation as a JSON formatted string
     *
     * @return string
     */
    public function getDefaultServices() {
        $jsonReturn = '[]';
        $defaultServices = Mage::getStoreConfig('gls/shipment/default_services', $this->getStore());
        if(!empty($defaultServices)) {
            $defaultServices = explode(',',$defaultServices);
            foreach ($defaultServices as &$defaultService) {
                $defaultService = intval($defaultService);
            }
            $jsonReturn = Mage::helper('core')->jsonEncode($defaultServices);
        }
        return $jsonReturn;
    }

    /**
     * Returns SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY or even SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY
     * in respect to the return value of the function $this->isDomestic()
     *
     * @return int
     */
    public function getParcelShopCombinationId() {
        return $this->isDomestic() ? SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY : SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY;
    }

    /**
     * Returns an array containing the GLS addon services
     * @return array
     */
    public function getAddonServices() {
        return Mage::getModel('synergeticagency_gls/gls')->addonServicesToOptionArray(false,true);
    }

    /**
     * Returns the maximun weight float value under several circumstances such as
     * "isDomestic" and/or "isParcelshopDelivery", etc.
     *
     * @return float
     */
    public function getMaxWeight() {
        $jsonModel = Mage::getModel('synergeticagency_gls/jsonimport');
        $fieldName = 'maxweight';
        $originCountryId = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $this->getStore());
        $shipToCountryId = $this->_getCurrentShipment()->getShippingAddress()->getCountryId();
        if($this->isParcelshopDelivery()) {
            $fieldName = 'parcelshopweight';
        }
        if($this->isDomestic()) {
            $maxWeight = $jsonModel->getDomesticValueByOriginCountryId($originCountryId,$fieldName);
        } else {
            $maxWeight = $jsonModel->getForeignValueByOriginAndForeignCountryId($originCountryId,$shipToCountryId,$fieldName);
        }
        return $maxWeight;
    }

    /**
     * Returns the configured store country config representation as a JSON formatted string if there is a GLS country id available
     *
     * @return string
     */
    public function getCountriesJson() {
        $shipment = $this->_getCurrentShipment();
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
     * Gets Json Config for specific origin country supplied by GLS
     * Returns an specific origin country supplied by GLS representation as a JSON formatted string
     *
     * @return string
    */
    public function getJsonConfig() {
        $shipment = $this->_getCurrentShipment();
        $jsonData = '{}';
        if($shipment) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                $this->getStore());
            /** @var Zend_Config_Json $jsonConfig */
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
     * Returns the delivery country of the current shipment
     *
     * @return string
     */
    public function getTargetCountry() {
        return $this->_getCurrentShipment()->getShippingAddress()->getCountryId();;
    }

    /**
     * Returns true, if the country of current shipment equals to the country defined for the current store
     *
     * @return bool|null
     */
    public function isDomestic() {
        $shipment = $this->_getCurrentShipment();
        $return = null;
        if($shipment) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $this->getStore());
            $destCountry = $shipment->getShippingAddress()->getCountryId();
            $return = $storeCountry == $destCountry;
        }
        return $return;
    }

    /**
     * Returns the Mage_Core_Model_Store object of the current shipment
     *
     * @return Mage_Core_Model_Store
     */
    private function getStore() {
        return $this->_getCurrentShipment()->getStore();
    }

    /**
     * Returns a Mage_Sales_Model_Order_Shipment object
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    private function _getCurrentShipment() {

        return Mage::registry('current_shipment');
    }
}
