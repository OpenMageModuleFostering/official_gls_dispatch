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
 * Class SynergeticAgency_Gls_Model_Glscashondelivery
 */
class SynergeticAgency_Gls_Model_Glscashondelivery extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Magento internal name for the payment method
     * @var string
     */
    protected $_code = 'glscashondelivery';

    /**
     * GLS Cash On Delivery payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'synergeticagency_gls/form_glscashondelivery';

    /**
     * Inherited from Mage_Sales_Model_Order_Payment
     * Set to true to set and validate custom config data
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Decides if the payment method can be used in the backend
     * Not needed as method is just shown in the checkout by magento
     * Handling is covered by GLS module itself
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * This module doesn't work for multishipping
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * Holds all GLS shipping rates for which GLS cash on delivery is allowed
     * @var array
     */
    protected $_allowedShippingRates = array();

    const SHIPPING_RATE_STANDARD = 'standard';
    const SHIPPING_RATE_EXPRESS = 'express';
    const SHIPPING_RATE_FOREIGNCOUNTRIES = 'foreigncountries';

    /**
     * Holds full config for for payment module glscashondelivery
     * @var array
     */
    protected $_glsConfig = array();

    /**
     * The constructor setting shipping rates for which cash on delivery is allowed
     */
    public function __construct()
    {
        $this->_allowedShippingRates = array(
            SynergeticAgency_Gls_Model_Carrier::CODE . '_' . SynergeticAgency_Gls_Model_Carrier::SHIPPING_RATE_STANDARD,
        );
        parent::__construct();
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    /**
     * Check whether payment method is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int|null $checksBitMask
     * @return bool
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {

        $parent = parent::isApplicableToQuote($quote, $checksBitMask);

        if ($parent === true) {

            /**
             * @var object $store
             * @desc Getting the Store
             */
            $store = $quote->getStore();

            /**
             * @desc Getting full payment config for for module synergeticagency_gls
             */
            $this->_glsConfig = Mage::getStoreConfig(
                'payment/glscashondelivery',
                $store
            );

            /** Does store country equal shipping country? */
            if ($this->_isDomesticShipping($quote) === false) {
                return false;
            }
            /** Is store country allowed at all for payment method? */
            if ($this->_isAllowedStoreCountry($quote) === false) {
                return false;
            }
            /** Is grand total within country limit? */
            if ($this->_isInCountryLimit($quote) === false) {
                return false;
            }
            /** Is current store currency allowed for payment method in this country? */
            if ($this->_isAllowedCountryCurrency($quote) === false) {
                return false;
            }
            /** If delivery address is package shop disallow payment method*/
            if ($this->_isParcelShopDelivery($quote) === true) {
                return false;
            }
            /** Is payment method allowed for for current shipping method? */
            if ($this->_isAllowedShippingRate($quote) === false) {
                return false;
            }
        }

        return $parent;


    }

    /**
     * Does store country equal shipping country?
     * @param $quote Mage_Sales_Model_Quote
     * @return bool
     */
    protected function _isDomesticShipping($quote)
    {
        $shippingCountry = $quote->getShippingAddress()->getCountry();

        $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
            $quote->getStore());

        return $shippingCountry === $storeCountry;
    }

    /**
     * Is store country allowed at all for payment method?
     * @param $quote Mage_Sales_model_Quote
     * @return bool
     * TODO: Test - Get allowed store countries from config
     */
    protected function _isAllowedStoreCountry($quote)
    {
        return Mage::getModel('synergeticagency_gls/gls')->isCashserviceAvailabelForOriginCountry(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $quote->getStore()));
    }

    /**
     * Is currency allowed for this payment method
     * @param $quote Mage_Sales_Model_Quote
     * @return bool
     * TODO: Test
     */
    protected function _isAllowedCountryCurrency($quote)
    {
        $originCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $quote->getStore());
        $baseCurrency = $quote->getStore()->getBaseCurrencyCode();

        return Mage::getModel('synergeticagency_gls/gls')->isCurrencyAvailabelForOriginCountry($originCountry,$baseCurrency);
    }

    /**
     * Only show Payment Method if basket total payment amount is within GLS defined limit
     * @param $quote Mage_Sales_Model_Quote
     * @return bool
     */
    protected function _isInCountryLimit($quote)
    {
        $originCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $quote->getStore());
        return Mage::getModel('synergeticagency_gls/gls')->isGrandTotalInLimitForOriginCountry($originCountry,$quote->getGrandTotal());
    }

    /**
     * Check if it's a parcel shop delivery. Payment method is not allowed for parcel shop deliveries by GLS
     * @param $quote Mage_Sales_Model_Quote
     * @return bool
     */
    protected function _isParcelShopDelivery($quote)
    {
        $parcelShopId = $quote->getShippingAddress()->getParcelshopId();
        return !empty($parcelShopId);
    }

    /**
     * Check is payment is enabled in magento configuration
     * @param $quote Mage_Sales_Model_Quote
     * @return bool
     */
    protected function _isAllowedShippingRate($quote)
    {
        $disallowedShippingMethods = explode(',', $this->_glsConfig['disallowed_delivery_services']);

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

        if(empty($shippingMethod)) {
            return false;
        }

        $rates = $quote->getShippingAddress()->getShippingRatesCollection();
        if(!count($rates)) {
            return false;
        }

        foreach ($rates as $rate) {
            if ($rate->getCode() == $shippingMethod) {
                if ($rate->getCarrier() == "synergeticagency_gls") {
                    if (!in_array($shippingMethod, $this->_allowedShippingRates)) {
                        return false;
                    }
                } else {
                    if ($this->_glsConfig['disallow_delivery_services'] === '1') {
                        if (in_array($rate->getCarrier(), $disallowedShippingMethods)) {
                            return false;
                        }
                    }
                }
                break;
            }
        }

        return true;
    }
}