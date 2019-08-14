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
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool|null
     */
    public function isDomestic($shipment) {
        //$shipment = SynergeticAgency_Gls_Helper_Validate::getCurrentShipment();
        $return = null;
        if($shipment) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $shipment->getStore());
            $destCountry = $shipment->getShippingAddress()->getCountryId();
            $return = $storeCountry == $destCountry;
        }
        return $return;
    }

    /**
     * Shipment to parcel shop? True if a parcel shop ID is given
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function isParcelshopDelivery($shipment) {
        $parcelShopId = $shipment->getShippingAddress()->getParcelshopId();
        return !empty($parcelShopId);
    }

    /**
     * Gets Json Config for specific origin country supplied by gls
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return string
     */
    public function getJsonConfig($shipment) {
        $jsonData = '{}';
        if($shipment) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                $shipment->getStore());

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
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return string
     */
    public function getConfig($shipment) {
        $jsonConfig = '';
        if($shipment) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                $shipment->getStore());

            $jsonConfig = Mage::getModel('synergeticagency_gls/jsonimport')->getCountry($storeCountry);

        }
        return $jsonConfig;
    }

    /**
     * get country config as json
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return string
     */
    public function getCountriesJson($shipment) {
        $jsonData = '{}';
        if($shipment) {
            $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $shipment->getStore());
            $glsCountry = Mage::getModel('synergeticagency_gls/country')->load($storeCountry);
            if($glsCountry->getId()) {
                $jsonData = Mage::helper('core')->jsonEncode($glsCountry->getData());
            }
        }
        return $jsonData;
    }

    /**
     * get country config
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return string
     */
    public function getCountriesConfig($shipment) {
        $glsCountry = null;
        if($shipment) {
                $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $shipment->getStore());
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
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return int
     */
    public function getParcelShopCombinationId($shipment) {
        return $this->isDomestic($shipment) ? SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY : SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY;
    }

    /**
     * Gets current delivery country
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return string
     */
    public function getTargetCountry($shipment) {
        return $shipment->getShippingAddress()->getCountryId();
    }

    /**
     * is target delivery country allowed from source country
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function isAllowedTargetCountry($shipment){

        $config = $this->getConfig($shipment);

        $countryValidator = new Zend_Validate_InArray(array_keys($config->foreign->countries));
        return $countryValidator->isValid($this->getTargetCountry($shipment));
    }

    /**
     * Returns true, if current shipping method is a GLS "Standard" shipping
     * Otherwise, this function returns false
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function isStandardShipping($shipment) {
        return $shipment->getOrder()->getShippingMethod() == SynergeticAgency_Gls_Model_Carrier::CODE.'_'.SynergeticAgency_Gls_Model_Carrier::SHIPPING_RATE_STANDARD;
    }

    /**
     * Returns true, if current shipping method is a GLS "Express" shipping
     * Otherwise, this function returns false
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function isExpressShipping($shipment) {
        return $shipment->getOrder()->getShippingMethod() == SynergeticAgency_Gls_Model_Carrier::CODE.'_'.SynergeticAgency_Gls_Model_Carrier::SHIPPING_RATE_EXPRESS;
    }

    /**
     * Returns true, if current shipping method is a GLS "Foreign" shipping
     * Otherwise, this function returns false
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function isForeignShipping($shipment) {
        return $shipment->getOrder()->getShippingMethod() == SynergeticAgency_Gls_Model_Carrier::CODE.'_'.SynergeticAgency_Gls_Model_Carrier::SHIPPING_RATE_FOREIGNCOUNTRIES;
    }

    /**
     * Returns true, if payment method of current shipping is an instance of SynergeticAgency_Gls_Model_Glscashondelivery
     * Returns false, if payment method of current shipping is not an instance of SynergeticAgency_Gls_Model_Glscashondelivery
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function isCashService($shipment) {
        if($shipment) {
            if($shipment->getOrder()->getPayment()->getMethodInstance() instanceof SynergeticAgency_Gls_Model_Glscashondelivery) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true, if given country code(ISO-3166 Alpha 2) matches to the configured country of current store
     * Returns false, if given country code(ISO-3166 Alpha 2) does nit match to the configured country of current store
     *
     * @param $countryCode
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function matchCountry( $countryCode, $shipment ) {
        $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $shipment->getStore());
        return strtoupper( $storeCountry ) === strtoupper( $countryCode );
    }

    /**
     * Returns true, if destination of current shipment is allowed by current Magento store configuration and GLS basis configuration.
     * Returns false, if destination of current shipment is not allowed by current Magento store configuration  and GLS basis configuration.
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function isCountryAvailable($shipment) {
        $glsModel = Mage::getModel('synergeticagency_gls/gls');
        $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $shipment->getStore());
        $destCountry = $shipment->getShippingAddress()->getCountryId();
        return ($glsModel->isCountryOriginIdAvailable($storeCountry)
            && ($this->isDomestic($shipment) || $glsModel->isDestinationCountryAvailable($storeCountry,$destCountry)));
    }

    /**
     * @param int $combinationId
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function isCombinationValid($combinationId,$shipment) {
        $return = false;
        switch ($combinationId) {
            case SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL:
                if($this->isDomestic($shipment) && !$this->isParcelshopDelivery($shipment) && !$this->isCashService($shipment)) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_GUARANTEED:
                if($this->isDomestic($shipment)) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY:
                if($this->isDomestic($shipment) && $this->isParcelshopDelivery($shipment)) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_CASHSERVICE:
                if($this->isDomestic($shipment) && $this->isCashService($shipment)) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL:
                if((!$this->isDomestic($shipment) || $this->matchCountry('FI',$shipment)) && !$this->isParcelshopDelivery($shipment) && $this->isForeignShipping($shipment)) {
                    $return = true;
                }
                break;
            case SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY:
                if(!$this->isDomestic($shipment) && $this->isParcelshopDelivery($shipment)) {
                    $return = true;
                }
                break;
        }
        return $return;
    }
}
