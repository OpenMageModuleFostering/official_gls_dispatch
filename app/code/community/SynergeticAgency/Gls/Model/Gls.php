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
 * @package    SynergeticAgency\Gls\Model
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Model_Gls
 */
class SynergeticAgency_Gls_Model_Gls extends Mage_Core_Model_Abstract {

    const COMB_BUSINESS_PARCEL                  = 1;
    const COMB_BUSINESS_PARCEL_CASHSERVICE      = 2;
    const COMB_BUSINESS_PARCEL_GUARANTEED       = 3;
    const COMB_BUSINESS_PARCEL_SHOPDELIVERY     = 4;
    const COMB_EUROBUSINESS_PARCEL              = 5;
    const COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY = 6;

    const PRODUCT_BUSINESS_PARCEL               = 1;
    const PRODUCT_EUROBUSINESS_PARCEL           = 2;

    const SERVICE_THINKGREEN                    = 1;
    const SERVICE_FLEXDELIVERY                  = 2;
    const SERVICE_EMAILNOTIFICATION             = 3;
    const SERVICE_PRIVATEDELIVERY               = 4;
    const SERVICE_CASHSERVICE                   = 5;
    const SERVICE_GUARANTEED                    = 6;
    const SERVICE_SHOPDELIVERY                  = 7;

    const GLS_WEIGHT_UNIT_KG = 'kg';
    const GLS_WEIGHT_UNIT_G  = 'g';

    const GLS_STATUS_PENDING                    = 'pending_gls';
    const GLS_STATUS_PENDING_ERROR              = 'pending_gls_error';
    const GLS_STATUS_PROCESSING                 = 'processing_gls';
    const GLS_STATUS_PROCESSING_ERROR           = 'processing_gls_error';
    const GLS_STATUS_COMPLETE                   = 'complete_gls';

    /**
     * Returns an array with available gls products available for inland shipping.
     * A gls product in this module is a combination of gls services wrapped up to a product for better usability.
     * In case $toKeyValue param is true the array keys are the combination id's from model (Zend configuration) instead of standard array index
     * @param bool $toKeyValue
     * @return array
     */
    public function nationalProductServiceCombinationsToOptionArray( $toKeyValue=false ) {
        $options = array();
        $combinations = Mage::getModel('synergeticagency_gls/combination')
            ->getCollection()
            ->addFilter('national',true)
            ->load();

        foreach($combinations as $combination) {
            $label = $combination->getName();

            if($toKeyValue) {
                $options[$combination->getId()] = $label;
            } else {
                $options[] = array(
                    'value' => $combination->getId(),
                    'label' => $label
                );
            }
        }

        return $options;
    }

    /**
     * Returns an array with available gls products available for inland shipping and foreign country shipping.
     * A gls product in this module is a combination of gls services wrapped up to a product for better usability.
     * In case $toKeyValue param is true the array keys are the combination id's from model (Zend configuration) instead of standard array index
     * @param bool|false $toKeyValue
     * @return array
     */
    public function productServiceCombinationsToOptionArray($toKeyValue=false) {
        $options = array();
        $combinations = Mage::getModel('synergeticagency_gls/combination')
            ->getCollection()
            ->load();

        foreach($combinations as $combination) {
            $label = $combination->getName();

            if($toKeyValue) {
                $options[$combination->getId()] = $label;
            } else {
                $options[] = array(
                    'value' => $combination->getId(),
                    'label' => $label
                );
            }
        }

        return $options;
    }

    /**
     * Return an array with additional optional services available for gls products (combinations)
     * In case $toKeyValue param is true the array keys are the combination id's from model (Zend configuration) instead of standard array index
     * @param bool|false $toKeyValue
     * @param bool|false $withNotice
     * @return array
     */
    public function addonServicesToOptionArray($toKeyValue=false,$withNotice=false) {
        $options = array();
        $services = Mage::getModel('synergeticagency_gls/service')->getCollection()->addFilter('is_addon',true)->load();
        foreach($services as $service) {
            $value = $service->getId();
            $label = Mage::helper('synergeticagency_gls')->__($service->getName());
            $notice = Mage::helper('synergeticagency_gls')->__($service->getNotice());

            if($toKeyValue) {
                $options[$value] = $label;
            } else {
                if($withNotice) {
                    $options[] = array(
                        'value'  => $value,
                        'label'  => $label,
                        'notice' => $notice
                    );
                } else {
                    $options[] = array(
                        'value' => $value,
                        'label' => $label
                    );
                }
            }
        }
        return $options;
    }

    /**
     * Return an array with countries allowed by GLS as an origin shipping country
     * @return array
     */
    public function getAvialableOriginCountries() {
        $countries = Mage::getModel('synergeticagency_gls/country')->getCollection()->load();
        $countryArray = array();
        foreach($countries as $country) {
            $countryArray[] = $country->getId();
        }
        return $countryArray;
    }

    /**
     * Verify if a given country is allowed as origin country by GLS
     * @param string $countryId
     * @return bool
     */
    public function isCountryOriginIdAvailable($countryId) {
        $availableCountryIds = $this->getAvialableOriginCountries();
        return in_array($countryId, $availableCountryIds);
    }

    /**
     * Verify if a given country is allowed as destination country from origin country by GLS
     * @param string $originCountryId
     * @param string $foreignCountryId
     * @return bool
     */
    public function isDestinationCountryAvailable($originCountryId,$foreignCountryId) {
        $countries = Mage::getModel('synergeticagency_gls/jsonimport')->getForeignCountriesByOriginCountryId($originCountryId);
        if($countries && count($countries)) {
            foreach($countries as $key => $country) {
                if($key == $foreignCountryId) return true;
            }
        }
        return false;
    }

    /**
     * Verify if GLS cash on delivery service is allowed for a given country
     * @param string $originCountryId
     * @return null|bool
     */
    public function isCashserviceAvailabelForOriginCountry($originCountryId) {
        return Mage::getModel('synergeticagency_gls/jsonimport')->getDomesticValueByOriginCountryId($originCountryId,'cashservice');
    }

    /**
     * Verify if shipment currency is allowed for country by GLS
     * @param string $originCountryId
     * @param string $currencyCode
     * @return bool
     */
    public function isCurrencyAvailabelForOriginCountry($originCountryId,$currencyCode) {
        return $currencyCode == Mage::getModel('synergeticagency_gls/jsonimport')->getDomesticValueByOriginCountryId($originCountryId,'cashcurrency');
    }

    /**
     * Verify if total shipping amount is within GLS allowed limit
     * @param string $originCountryId
     * @param string $grandTotal
     * @return bool
     */
    public function isGrandTotalInLimitForOriginCountry($originCountryId, $grandTotal) {
        $cashMax = Mage::getModel('synergeticagency_gls/jsonimport')->getDomesticValueByOriginCountryId($originCountryId,'cashmax');
        if(is_null($cashMax)) {
            return false;
        }
        return $cashMax >= $grandTotal;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    public function prepareGlsShipmentData($shipment) {
        $glsData = array();
        $combination = $this->getCombinationByShipment($shipment);
        if(!$combination) {
            return false;
        }
        $service = $this->getServicesByCombinationAndShipment($combination,$shipment);
        $glsData['combination'] = $combination;
        $glsData['service'] = $service;
        $glsData['shipping_date'] = date('Y-m-d'); // today
        $glsData['return_label'] = Mage::getStoreConfig('gls/shipment/return_label_enabled',$shipment->getStore());
        $weight = 0;
        $items = $shipment->getOrder()->getAllItems();
        if(count($items)) {
            foreach($items as $item) {
                $weight += (float)$item->getWeight() * (int)$item->getQtyOrdered();
            }
        }
        if(empty($weight)) {
            $weight = Mage::getStoreConfig('gls/shipment/weight', $shipment->getStore());
        }
        if(empty($weight)) {
            return false;
        }
        // get unit
        $weightUnit = Mage::getStoreConfig('gls/shipment/weight_unit', $shipment->getStore());
        if($weightUnit == self::GLS_WEIGHT_UNIT_G) {
            $weight = round($weight/1000,3);
        }
        // value should be greater than 0.1
        if($weight <= 0.1 ) {
            return false;
        }

        if(Mage::helper('synergeticagency_gls/validate')->isCashService($shipment)) {
            $cashService = $shipment->getStore()->roundPrice($shipment->getOrder()->getGrandTotal());
            $packages = array(
                'weight' => $weight,
                'cashservice' => $cashService
            );
        } else {
            $packages = array(
                'weight' => $weight
            );
        }
        $data['shipment']['gls'] = $glsData;
        $data['shipment']['packages'][] = $packages;

        return $data;
    }

    /**
     * @param int $combinationId
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param bool $withDefault
     * @return array
     */
    public function getServicesByCombinationAndShipment($combinationId,$shipment,$withDefault=true) {
        // for the moment there is no condition in the order that can trigger a service
        // because think green is not available in the frontend anymore
        // but we have to check the default services
        if(!$withDefault) {
            return array();
        }

        $defaultServices = Mage::getStoreConfig('gls/shipment/default_services', $shipment->getStore());
        if(empty($defaultServices)) {
            return array();
        }

        $defaultServices = explode(',',$defaultServices);
        foreach ($defaultServices as &$defaultService) {
            $defaultService = intval($defaultService);
        }

        $storeCountry = Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $shipment->getStore());
        $glsCountry = Mage::getModel('synergeticagency_gls/country')->load($storeCountry);
        if(empty($glsCountry)) return array();
        $services = $glsCountry->getAddonServicesByCombination($combinationId);
        $serviceReturn = array();
        /** @var SynergeticAgency_Gls_Model_Service $service */
        if($services && count($services)) {
            foreach ($services as $service) {
                if (in_array($service->getId(), $defaultServices)) {
                    $serviceReturn[$service->getId()] = $service->getId(); // important to have the id as key because that is the value used later on
                }
            }
        }
        return $serviceReturn;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param bool $withDefault
     * @return bool|int
     */
    public function getCombinationByShipment($shipment,$withDefault=true) {
        $helper = Mage::helper('synergeticagency_gls/validate');
        if($helper->isDomestic($shipment) && ($helper->isStandardShipping($shipment)) && !$helper->isParcelshopDelivery($shipment) && !$helper->isCashService($shipment)) {
            return SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL;
        }
        if($helper->isDomestic($shipment) && $helper->isExpressShipping($shipment)) {
            return SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_GUARANTEED;
        }
        if($helper->isDomestic($shipment) && $helper->isParcelshopDelivery($shipment)) {
            return SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_SHOPDELIVERY;
        }
        if($helper->isDomestic($shipment) && $helper->isCashService($shipment)) {
            return SynergeticAgency_Gls_Model_Gls::COMB_BUSINESS_PARCEL_CASHSERVICE;
        }
        if((!$helper->isDomestic($shipment) || $helper->matchCountry('FI',$shipment)) && !$helper->isParcelshopDelivery($shipment) && $helper->isForeignShipping($shipment)) {
            return SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL;
        }
        if(!$helper->isDomestic($shipment) && $helper->isParcelshopDelivery($shipment)) {
            return SynergeticAgency_Gls_Model_Gls::COMB_EUROBUSINESS_PARCEL_SHOPDELIVERY;
        }
        if($withDefault) {
            // none matched get the default
            $defaultCombination = Mage::getStoreConfig('gls/shipment/default_combination', $shipment->getStore());
            if (empty($defaultCombination)) {
                return false;
            }
            // check if default combination is valid
            if ($helper->isCombinationValid($defaultCombination, $shipment)) {
                return $defaultCombination;
            }
        }
        return false;
    }

    /**
     * Saves a GLS shipment. Shipments are saved in modules DB Tables additionally to the Magento Tables
     * @param array $data
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param null|SynergeticAgency_Gls_Model_Shipment $glsShipment
     * @throws Exception
     */
    public function saveGlsShipment($data, $shipment, $glsShipment = null) {
        $data     = $data['shipment'];
        $glsData  = $data['gls'];
        $store = $shipment->getStore();
        $editMode = true;
        if(is_null($glsShipment)) {
            $glsShipment = Mage::getModel('synergeticagency_gls/shipment');
            $editMode = false;
        }
        $glsShipment->setShipmentId($shipment->getId());

        $combination = $glsData['combination'];
        $glsShipment->setCombinationId($combination);
        $glsShipment->setSandbox(Mage::getStoreConfig('gls/general/sandbox',$store));

        $serviceIds = array();
        $combinationModel = Mage::getModel('synergeticagency_gls/combination')->load($combination);
        $services = $combinationModel->getServices();
        if(!empty($services) && count($services)) {
            foreach($services as $service) {
                $serviceIds[] = $service->getId();
            }
        }
        if(!empty($glsData['service']) && count($glsData['service'])) {
            foreach($glsData['service'] as $key => $addonService) {
                if($addonService) {
                    $serviceIds[] = $key;
                }
            }
        }
        $glsShipment->setGlsProduct($combinationModel->getProduct()->getId());
        $serviceIdsConcat = '';
        if(count($serviceIds)) {
            $serviceIdsConcat = implode(',',$serviceIds);
        }
        $glsShipment->setGlsServices($serviceIdsConcat);
        $glsShipment->setOrderId($shipment->getOrderId());
        $shippingDate = strtotime($glsData['shipping_date']);
        $shippingDate = date('Y-m-d',$shippingDate);
        $glsShipment->setShippingDate($shippingDate);
        $returnLabel = 0;

        if(array_key_exists('return_label', $glsData) && $glsData['return_label'] === '1') {
            $returnLabel = 1;
        }
        $glsShipment->setReturnLabel($returnLabel);

        $shippingAddress = $shipment->getShippingAddress();
        $parcelShopId = $shippingAddress->getParcelshopId();
        if(empty($parcelShopId)) {
            $parcelShopId = '';
        }
        $glsShipment->setParcelshopId($parcelShopId);
        $glsShipment->save();

        if($editMode) {
            // delete it first
            $glsShipment->getShipmentAddress()->delete();
        }
        $parcelShopId = $shippingAddress->getParcelshopId();
        if(!empty($parcelShopId)) {
            // if parcelshop delivery is set, we have to use the billing address
            $shippingAddress = $shipment->getBillingAddress();
        }

        /** @var SynergeticAgency_Gls_Model_Shipment_Address $shippingAddress */
        $glsShipmentAddress = Mage::getModel('synergeticagency_gls/shipment_address');
        $glsShipmentAddress->setGlsShipmentId($glsShipment->getId());

        $glsShipmentAddress->setName1(
        // Magento's function getName will concat several values in an "magento standard way".
        // Sorry, but GLS need an other sorting:
            $shippingAddress->getPrefix() . " " .
            $shippingAddress->getLastname() . ", " .
            $shippingAddress->getFirstname() . " " .
            $shippingAddress->getMiddlename() . " " .
            $shippingAddress->getSuffix()
        );
        // GLS uses the field name2 for the company
        $glsShipmentAddress->setName2( $shippingAddress->getCompany() );

        // currently, GLS doesn't use the field name3 in the shipment label
        // $glsShipmentAddress->setName3('value for GLS field name3');

        $street = $shippingAddress->getStreet();
        if(is_array($street)) {
            $street = implode(' ',$street);
        }
        $glsShipmentAddress->setStreet1($street);
        $glsShipmentAddress->setCountry($shippingAddress->getCountryModel()->getIso2Code());
        $glsShipmentAddress->setZipCode($shippingAddress->getPostcode());
        $glsShipmentAddress->setCity($shippingAddress->getCity());
        $glsShipmentAddress->setEmail($shippingAddress->getEmail());
        $glsShipmentAddress->setPhone($shippingAddress->getTelephone());
        $glsShipmentAddress->save();

        if($editMode) {
            // first delete all parcels and create new ones
            /** @var SynergeticAgency_Gls_Model_Shipment_Parcel $delParcel */
            foreach($glsShipment->getShipmentParcels() as $delParcel) {
                $delParcel->delete();
            }
        }

        foreach ($data['packages'] as $key => $package) {
            $weight = (float)str_replace(',', '.', $package['weight']);
            $cashService = null;
            if(!empty($package['cashservice'])) {
                $cashService = (float)str_replace(',', '.', $package['cashservice']);
            }

            $glsShipmentParcel = Mage::getModel('synergeticagency_gls/shipment_parcel');
            $glsShipmentParcel->setGlsShipmentId($glsShipment->getId());
            $glsShipmentParcel->setWeight($weight);
            $glsShipmentParcel->setCashservice($cashService);
            $glsShipmentParcel->save();
        }
    }

    /**
     * Returns true if $data is valid
     * Returns HTML formatted error string containing information of invalid data
     * @param array $data
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool|string
     */
    public function validateGlsShipmentData($data,$shipment) {

        $validationResult = array();

        $validationResult[] = $this->isValidWeight($data,$shipment);
        $validationResult[] = $this->isValidCashAmount($data,$shipment);
        $validationResult[] = $this->isValidShippingDate($data);
        $validationResult[] = $this->isValidCashService($data,$shipment);
        $validationResult[] = $this->hasValidServices($data,$shipment);
        $validationResult[] = $this->isValidDestinationCountry($data,$shipment);
        $errorString = '';
        foreach($validationResult AS $messages){
            foreach($messages AS $message){
                $errorString .= $message.'<br />';
            }
        }

        if($errorString === '') {
            return true;
        }
        else {
            return $errorString;
        }
    }

    /**
     * Check if parcels weight(s) is(are)within allowed maximum weight in respect of given parcel data and the corresponding configuration
     * In case the check is negative an appropriate error message(s) is getting returned (Zend validation)
     * @param $data
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     * @throws Zend_Locale_Exception
     * @throws Zend_Validate_Exception
     */
    public function isValidWeight($data,$shipment){

        $minWeight = 0.01;

        $helper = Mage::helper('synergeticagency_gls/validate');
        $config = $helper->getConfig($shipment);
        $symbols = Zend_Locale_Data::getList(Mage::getStoreConfig('general/locale/code', $shipment->getStore()) , 'symbols');

        switch($helper->isDomestic($shipment)){
            case false:
                switch($helper->isParcelshopDelivery($shipment)) {
                    case true:
                        $maxWeight = $config->foreign->countries->{$helper->getTargetCountry($shipment)}->parcelshopweight;
                        break;
                    default:
                        $maxWeight = $config->foreign->countries->{$helper->getTargetCountry($shipment)}->maxweight;
                }
                break;
            default:
                switch($helper->isParcelshopDelivery($shipment)) {
                    case true:
                        $maxWeight = $config->domestic->parcelshopweight;
                        break;
                    default:
                        $maxWeight = $config->domestic->maxweight;
                }
        }

        $weightValidator = new Zend_Validate_Between(floatval($minWeight),floatval($maxWeight),1);
        $weightValidator->setMessage(Mage::helper('synergeticagency_gls')->__("At least one parcel has a invalid weight"),'notBetween');

        foreach($data['shipment']['packages'] AS $package){
            if(false === $weightValidator->isValid(floatval(str_replace($symbols['decimal'], '.', $package['weight'])))){
                break;
            }
        }

        return $weightValidator->getMessages();
    }

    /**
     * Check if parcels cash on delivery amount(s) is(are)within allowed amount in respect of given parcel data and the corresponding configuration.
     * In case the check is negative an appropriate error message(s) is getting returned (Zend validation)
     * @param $data
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     * @throws Zend_Locale_Exception
     * @throws Zend_Validate_Exception
     */
    public function isValidCashAmount($data,$shipment){

        $minAmount = 0.00;

        $helper = Mage::helper('synergeticagency_gls/validate');
        $config = $helper->getConfig($shipment);
        $symbols = Zend_Locale_Data::getList(Mage::getStoreConfig('general/locale/code', $shipment->getStore()) , 'symbols');

        switch($helper->isDomestic($shipment)){
            case false:
                $maxAmount = $config->foreign->countries->{$helper->getTargetCountry($shipment)}->cashmax;
                break;
            default:
                $maxAmount = $config->domestic->cashmax;
                break;
        }

        $amountValidator = new Zend_Validate_Between(floatval($minAmount),floatval($maxAmount),1);
        $amountValidator->setMessage(Mage::helper('synergeticagency_gls')->__("At least one parcel has a invalid cash on delivery amount"),'notBetween');

        foreach($data['shipment']['packages'] AS $package){
            if(array_key_exists('cashservice',$package)) {
                if (false === $amountValidator->isValid(floatval(str_replace($symbols['decimal'], '.',$package['cashservice'])))) {
                    break;
                }
            }
        }

        return $amountValidator->getMessages();
    }

    /**
     * Check if shipping date is not in the past - evaluated on daily base
     * In case the check is negative an appropriate error message(s) are getting returned (Zend validation)
     * @param $data
     * @return array
     * @throws Zend_Validate_Exception
     */
    public function isValidShippingDate($data){

        $minDate = strtotime('yesterday midnight');
        $date = strtotime($data['shipment']['gls']['shipping_date']);

        $dateValidator = new Zend_Validate_GreaterThan($minDate);
        $dateValidator->setMessage(Mage::helper('synergeticagency_gls')->__("The shipping date may not be in the past"),'notGreaterThan');

        $dateValidator->isValid($date);
        return $dateValidator->getMessages();
    }

    /**
     * Check if submitted service combination is allowed
     * In case the check is negative an appropriate error message(s) is getting returned (Zend validation)
     * @param $data
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     * @throws Zend_Validate_Exception
     */
    public function hasValidServices($data,$shipment){

        $helper = Mage::helper('synergeticagency_gls/validate');
        $countryConfig = $helper->getCountriesConfig($shipment);
        $selectedCombination = $helper->getCombinationByCombinationId($countryConfig->options, $data['shipment']['gls']['combination']);

        $serviceValidator = new Zend_Validate_InArray($selectedCombination['addon_services']);
        $serviceValidator->setMessage(Mage::helper('synergeticagency_gls')->__("At least one additional service is not allowed for the current product"),'notInArray');
        if(isset($data['shipment']['gls']['service'])) {
            foreach ($data['shipment']['gls']['service'] AS $serviceId => $value) {
                if ($value === '1') {
                    if (false === $serviceValidator->isValid($serviceId)) {
                        break;
                    }
                }
            }
        }

        return $serviceValidator->getMessages();
    }

    /**
     * Verify if target country is allowed for product
     * businessparcel vs. europarcel
     * business parcel is just allowed for domestic shipment europarcel is just allowed for foreign country shipment
     * @param $data
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    public function isValidDestinationCountry($data,$shipment)
    {
        $helper = Mage::helper('synergeticagency_gls/validate');
        $countryConfig = $helper->getCountriesConfig($shipment);
        $selectedCombination = $helper->getCombinationByCombinationId($countryConfig->options, $data['shipment']['gls']['combination']);
        $messages = array();
        if((false === $selectedCombination['domestic'] && true === $helper->isDomestic($shipment)) || (false === $selectedCombination['foreign'] && false === $helper->isDomestic($shipment))){
            $messages[0] =  Mage::helper('synergeticagency_gls')->__("The selected product is not allowed for shipments target country");
        }

        return $messages;
    }

    /**
     * Check if parcels cash on delivery is(are) allowed in respect of given package data and the corresponding configuration
     * @param $data
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    public function isValidCashService($data,$shipment)
    {
        $helper = Mage::helper('synergeticagency_gls/validate');
        $countryConfig = $helper->getCountriesConfig($shipment);
        $selectedCombination = $helper->getCombinationByCombinationId($countryConfig->options, $data['shipment']['gls']['combination']);
        $messages = array();
        foreach($data['shipment']['packages'] AS $package){
            if(array_key_exists('cashservice',$package) && false === $selectedCombination['cashservice']) {
                $messages[0] = Mage::helper('synergeticagency_gls')->__("Cash on delivery isn't allowed for the selected product");
                break;
            }
        }

        return $messages;
    }
}
