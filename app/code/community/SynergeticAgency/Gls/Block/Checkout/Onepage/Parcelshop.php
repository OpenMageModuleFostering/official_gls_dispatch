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
 * @package    SynergeticAgency\Gls\Block\Checkout\Onepage
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Block_Checkout_Onepage_Parcelshop
 */
class SynergeticAgency_Gls_Block_Checkout_Onepage_Parcelshop
extends Mage_Core_Block_Template
{

    /**
     * Internal constructor, that is called from real constructor.
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * determines, if GLS parcelshop option is active, see also store configuration "gls/parcel_shop/parcel_shop_enabled"
     * @return mixed
     */
    public function isActive() {
        return Mage::getStoreConfig('gls/parcel_shop/parcel_shop_enabled', Mage::app()->getStore());
    }

    /**
     * get country config as json
     * @return string
     */
    public function getCountriesJson() {
        $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, Mage::app()->getStore());
        $glsCountry = Mage::getModel('synergeticagency_gls/country')->load($storeCountry);
        $jsonData = '{}';
        if($glsCountry->getId()) {
            $jsonData = Mage::helper('core')->jsonEncode($glsCountry->getData());
        }
        return $jsonData;
    }

    /**
     * Gets Json Config for specific origin country supplied by gls
     * @return string
     */
    public function getJsonConfig() {
        $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, Mage::app()->getStore());
        /** @var Zend_Config_Json $jsonConfig */
        $jsonConfig = Mage::getModel('synergeticagency_gls/jsonimport')->getCountry($storeCountry);
        $jsonData = '{}';
        if($jsonConfig) {
            $writer = new Zend_Config_Writer_Json();
            $writer->setConfig($jsonConfig);
            $jsonData = $writer->render();
        }
        return $jsonData;
    }

}