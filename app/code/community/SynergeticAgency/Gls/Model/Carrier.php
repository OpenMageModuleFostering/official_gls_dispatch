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
 * @package    SynergeticAgency\Gls\Model
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Model_Carrier
 */
class SynergeticAgency_Gls_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'synergeticagency_gls';

    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request data
     *
     * @var Mage_Shipping_Model_Rate_Request|null
     */
    protected $_request = null;

    /**
     * Raw rate request data
     *
     * @var Varien_Object|null
     */
    protected $_rawRequest = null;

    /**
     * Rate result data
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $_result = null;

    /**
     * Errors placeholder
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Shipping Methods
     *
     * @var array
     */
    protected $_shippingConfigKeys = array();

    /**
     * Full config container for for module synergeticagency_gls
     *
     * @var array
     */
    protected $_glsConfig = array();


    /**
     * The name of the standard shipping rate which is a GLS service combination
     */
    const SHIPPING_RATE_STANDARD = 'standard';
    /**
     * The name of the express shipping rate which is a GLS service combination
     */
    const SHIPPING_RATE_EXPRESS = 'express';
    /**
     *  The name of the foreigncountries shipping rate which is a GLS service combination
     */
    const SHIPPING_RATE_FOREIGNCOUNTRIES = 'foreigncountries';
    /**
     * @TODO: check if the current value is correct
     */
    const CARRIER_TITLE = 'General Logistics Systems';


    /**
     * Returns available shipping rates for GLS Shipping carrier
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $this->_request = $request;

        $result = Mage::getModel('shipping/rate_result');
        $store = Mage::app()->getStore();

        //Getting full config for for module synergeticagency_gls */
        $this->_glsConfig = Mage::getStoreConfig(
            'carriers/synergeticagency_gls',
            $store
        );

        //$this->_shippingConfigKeys = preg_filter('/^name_(.*)/', '$1', array_keys($this->_glsConfig));

        $this->_shippingConfigKeys = array('0'=>'foreigncountries','1'=>'express','2'=>'standard');

        $hasResults = false;
        foreach ($this->_shippingConfigKeys AS $deliveryConfigKey) {
            //@todo check price and name here as well and if empty disable
            if($this->_glsConfig['active_'.$deliveryConfigKey] == '1'){
                $methodName = '_get'.Mage::helper('synergeticagency_gls')->underlineToCamelCase($deliveryConfigKey, TRUE).'Rate';
                if(method_exists($this, $methodName)) {
                    $methodResult = $this->{$methodName}();
                    if($methodResult !== false) {
                        $hasResults = true;
                        $result->append($methodResult);
                    }
                }
            }
        }
        if(!$hasResults) {
            $result = false;
        }
        return $result;
    }

    /**
     * Returns allowed shipping methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            self::SHIPPING_RATE_STANDARD    =>  $this->_glsConfig['name_'.self::SHIPPING_RATE_STANDARD],
            self::SHIPPING_RATE_EXPRESS     =>  $this->_glsConfig['name_'.self::SHIPPING_RATE_EXPRESS],
            self::SHIPPING_RATE_FOREIGNCOUNTRIES     =>  $this->_glsConfig['name_'.self::SHIPPING_RATE_FOREIGNCOUNTRIES],
        );
    }

    /**
     * Return all tracking for a shipment
     * @param $tracking
     * @return bool|false|Mage_Core_Model_Abstract
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && 0 < strlen($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get a trackings for shipment
     * @param $trackings
     * @return Mage_Shipping_Model_Tracking_Result
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }

        $result = Mage::getModel('shipping/tracking_result');
        foreach ($trackings as $trackingNumber) {
            $status = Mage::getModel('shipping/tracking_result_status');
            $status->setCarrierTitle(self::CARRIER_TITLE);
            $status->setCarrier(self::CODE);
            $status->setTracking($trackingNumber);
            $status->setPopup(true);
            $status->setUrl(Mage::getStoreConfig('gls/general/tracking_url').$trackingNumber);
            $result->append($status);
        }

        return $result;
    }


    /**
     * Get Standard rate object
     * @return Mage_Shipping_Model_Rate_Result_Method
     * @internal param Mage_Shipping_Model_Rate_Request $request
     */
    protected function _getStandardRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        // shipping method is only available for domestic shipping
        if(!$this->_isDomesticShipping($this->_request)) {
            return false;
        }

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod(self::SHIPPING_RATE_STANDARD);
        $rate->setMethodTitle($this->getConfigData('name_'.self::SHIPPING_RATE_STANDARD));
        $rate->setPrice($this->getConfigData('price_per_order_'.self::SHIPPING_RATE_STANDARD));
        $rate->setCost(0);

        return $rate;
    }

    /**
     * Get Express rate object
     * @return Mage_Shipping_Model_Rate_Result_Method
     * @internal param Mage_Shipping_Model_Rate_Request $request
     */
    protected function _getExpressRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        // shipping method is only available for specific countries, only domestic and not compatible with parcel shop delivery
        if(!$this->_isDomesticShipping($this->_request) || !$this->_isAvailableCountryForExpress($this->_request) || $this->_isParcelShopDelivery($this->_request)) {
            return false;
        }

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod(self::SHIPPING_RATE_EXPRESS);
        $rate->setMethodTitle($this->getConfigData('name_'.self::SHIPPING_RATE_EXPRESS));
        $rate->setPrice($this->getConfigData('price_per_order_'.self::SHIPPING_RATE_EXPRESS));
        $rate->setCost(0);

        return $rate;
    }


    /**
     * Get Express rate object
     * @return Mage_Shipping_Model_Rate_Result_Method
     * @internal param Mage_Shipping_Model_Rate_Request $request
     */
    protected function _getForeigncountriesRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        // shipping method is only available for specific countries and only domestic
        if($this->_isDomesticShipping($this->_request) || !$this->_isAvailableCountryForForeign($this->_request)) {
            return false;
        }

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod(self::SHIPPING_RATE_FOREIGNCOUNTRIES);
        $rate->setMethodTitle($this->getConfigData('name_'.self::SHIPPING_RATE_FOREIGNCOUNTRIES));
        $rate->setPrice($this->getConfigData('price_per_order_'.self::SHIPPING_RATE_FOREIGNCOUNTRIES));
        $rate->setCost(0);

        return $rate;
    }

    /**
     * Verify if current checkout shipping address country is same as the shops origin shipping country
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool
     */
    protected function _isDomesticShipping(Mage_Shipping_Model_Rate_Request $request) {
        return $request->getDestCountryId() == Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $request->getStoreId());
    }

    /**
     * Verify if shipping address country is valid for SHIPPING_RATE_EXPRESS shipping rate
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool
     */
    protected function _isAvailableCountryForExpress(Mage_Shipping_Model_Rate_Request $request) {
        //TODO: get this information from a config
        return $request->getDestCountryId() == 'DE';
    }

    /**
     * Verify if shipping address country is allowed in by GLS
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool
     */
    protected function _isAvailableCountryForForeign(Mage_Shipping_Model_Rate_Request $request) {
        $gls = Mage::getModel('synergeticagency_gls/gls');
        return($gls->isDestinationCountryAvailable(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $request->getStoreId()),$request->getDestCountryId()));
    }

    /**
     * Verify if shipping address is a parcel shop
     * Returning true if the request value parcelshop_id holds any value
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool
     */

    protected function _isParcelShopDelivery(Mage_Shipping_Model_Rate_Request $request)
    {
        // TODO: extend Mage_Shipping_Model_Rate_Request with dest_parcelshopid
        $getParams = Mage::app()->getRequest()->getParams();
        $parcelShopId = '';
        if (array_key_exists('shipping', $getParams) && array_key_exists('parcelshop_id', $getParams['shipping'])) {
            $parcelShopId = $getParams['shipping']['parcelshop_id'];
        }
        return !empty($parcelShopId);
    }

    /**
     * Verify if current stores shipping origin country is allowed by GLS and also by Magento shows the rate in dependency of result in checkout
     * Overrides Mage_Shipping_Model_Carrier_Abstract checkAvailableShipCountries
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return $this|bool
     */
    public function checkAvailableShipCountries(Mage_Shipping_Model_Rate_Request $request)
    {
        // basic check if country origin is available for gls shipping
        $gls = Mage::getModel('synergeticagency_gls/gls');
        if(!$gls->isCountryOriginIdAvailable(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $request->getStoreId()))) {
            return false;
        }

        $speCountriesAllow = $this->getConfigData('sallowspecific');
        $showMethod = $this->getConfigData('showmethod');

        //for specific countries, the flag will be 1
        if ($speCountriesAllow && $speCountriesAllow == 1){
            $availableCountries = array();
            if($this->getConfigData('specificcountry')) {
                $availableCountries = explode(',',$this->getConfigData('specificcountry'));
            }

        } else {
            $availableCountries = explode(',', (string)Mage::getStoreConfig('general/country/allow'));
        }

        if ($availableCountries && in_array($request->getDestCountryId(), $availableCountries)) {
            return $this;
        } elseif ($showMethod && (!$availableCountries || ($availableCountries
                    && !in_array($request->getDestCountryId(), $availableCountries)))
        ){
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $errorMsg = $this->getConfigData('specificerrmsg');
            $error->setErrorMessage($errorMsg ? $errorMsg : Mage::helper('shipping')->__('The shipping module is not available for selected delivery country.'));
            return $error;
        } else {
           //The admin set not to show the shipping module if the devliery country is not within specific countries
            return false;
        }
    }
}