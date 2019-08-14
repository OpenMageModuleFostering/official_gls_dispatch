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
 * GLS shipment model
 *
 * @category    SynergeticAgency
 * @package     SynergeticAgency_Gls
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_Gls_Model_Shipment extends Mage_Core_Model_Abstract {

    const GLS_SHIPMENT_LABEL_PDF_PREFIX_JOB = "job_";

    const GLS_SHIPMENT_MAX_PARCELS = 30;

    const GLS_SHIPMENT_MIN_PARCELS = 1;
    /**
     * Shipments target address
     * @var
     */
    private $_shipmentAddress;

    /**
     * Shipments parcels
     * @var
     */
    private $_shipmentParcels;

    /**
     * @var
     */
    private $_labelTempPath;

    /**
     * Initialize model
     */
    protected function _construct() {
        $this->_init('synergeticagency_gls/shipment');
        $this->setLabelTempPath();
    }

    /**
     * Get the shipment address from GLS Model by id (vs. loading shipment address from Magento Model)
     * @return mixed
     */
    public function getShipmentAddress() {
        if(is_null($this->_shipmentAddress) && $this->getId()) {
            $shipmentAddress = Mage::getModel('synergeticagency_gls/shipment_address')->loadByGlsShipmentId($this->getId());
            if($shipmentAddress && $shipmentAddress->getId()) {
                $this->_shipmentAddress = $shipmentAddress;
            }
        }
        return $this->_shipmentAddress;
    }

    /**
     * Get parcels for a shipment by shipment id)
     * @param string $order
     * @return null|SynergeticAgency_Gls_Model_Resource_Shipment_Parcel_Collection
     */
    public function getShipmentParcels($order=Varien_Data_Collection::SORT_ORDER_ASC) {
        if(is_null($this->_shipmentParcels) && $this->getId()) {
            $shipmentParcels = Mage::getModel('synergeticagency_gls/shipment_parcel')
                ->getCollection()
                ->addFieldToFilter('gls_shipment_id',$this->getId())
                ->setOrder('gls_shipment_parcel_id',$order)
                ->load();
            if($shipmentParcels && count($shipmentParcels)) {
                $this->_shipmentParcels = $shipmentParcels;
            }
        }
        return $this->_shipmentParcels;
    }

    /**
     * Get shipments GLS (service)combination
     * @return SynergeticAgency_Gls_Model_Combination
     */
    public function getCombination() {
        $combinationModel = Mage::getModel('synergeticagency_gls/combination');
        $combinationId = $this->getCombinationId();
        if($combinationId) {
            $combinationModel->load($combinationId);
        }
        return $combinationModel;
    }

    /**
     * Get shipments additional GLS services
     * @param bool $addonOnly
     * @return SynergeticAgency_Gls_Model_Resource_Service_Collection
     */
    public function getServices($addonOnly=false) {
        $serviceCollection = Mage::getModel('synergeticagency_gls/service')->getCollection();
        $serviceIds = $this->getGlsServices();
        if( $serviceIds === NULL ){
            $serviceIds = "";
        }

        if( !empty($serviceIds) ) {
            $serviceIds = explode(',',$serviceIds);
            $serviceCollection->setIdFilter($serviceIds);
            if($addonOnly) {
                $serviceCollection->addFilter('is_addon',true);
            }
            $serviceCollection->load();
            return $serviceCollection;
        }
        return array();
    }

    /**
     * @return null|Mage_Sales_Model_Order_Shipment
     */
    public function getMagentoShipment() {
        $magentoShipment = $this->getData('magento_shipment');
        if(empty($magentoShipment)) {
            $this->setData('magento_shipment',Mage::getModel('sales/order_shipment')->load($this->getShipmentId()));
        }
        return $this->getData('magento_shipment');
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getLabels() {
        // do prechecks
        $this->doLabelChecks();

        $connector = $this->getConnector();
        $connectorShipment = $this->getConnectorShipment();

        $this->setConnectorDeliveryAddress();
        $this->setAlternateShipperAddress();
        $this->setParcels();
        $this->setReturnAddress();
        $connector->setShipment( $connectorShipment );
        Mage::helper("synergeticagency_gls/log")->logApi( __METHOD__, __LINE__, $connector->getShipment()->toJson() );

        if( Mage::helper("synergeticagency_gls")->getGeneralGlsConfig('logging_enabled') == 1 ){
            Mage::log( $connector->getShipment()->toJson(), Zend_Log::DEBUG );
        }

        try {
            // send shipment to the GLS-API
            $connector->createShipment();
        } catch(Exception $e) {
            // handle exceptions of the connector
            $this->setError($e->getMessage(),$e->getCode());
        }

        Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, "GLS-API Response: ConsignmentId = " . $connector->getShipment()->getConsignmentId(), Zend_Log::INFO );
        Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, $connector->getCurlResponse() );


        if( Mage::helper("synergeticagency_gls")->getGeneralGlsConfig('logging_enabled') == 1 ){
            Mage::log( $connector->getCurlResponse(), Zend_Log::DEBUG );
        }

        // process response of an sent shipment ----------------------------
        // handle GLS-API Response Errors
        if( $connector->getError()->hasError() === true ){
            Mage::helper("synergeticagency_gls/log")->logApi( __METHOD__, __LINE__, "GLS-API Connector has caused an error: " . $connector->getError()->getMessage(), Zend_Log::ERR );
            Mage::helper("synergeticagency_gls/log")->logApi( __METHOD__, __LINE__, $connector->getError()->getModelState(true) );

            $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_API_CONNECTOR_ERROR;
            $errorMessage = sprintf(Mage::helper('synergeticagency_gls')->__('GLS-API Connector has caused an error: %s'), $connector->getError()->getMessage()) .
                "<br /><textarea name=\"gls_json_debug\" style=\"width: 80%;\">" . $connector->getShipment()->toJson() . $connector->getError()->getModelState( true ) .
                "</textarea><br />";
            $this->setError($errorMessage,$errorCode);
        }
        // get the shipment object right after the GLS-API Response for further processing
        /** @var SynergeticAgency_GlsConnector_Model_Shipment $shipmentResponse */
        $shipmentResponse = $connector->getShipment();

        $consignmentId = $shipmentResponse->getConsignmentId();
        if (empty($consignmentId)) {
            Mage::helper("synergeticagency_gls/log")->logApi(__METHOD__, __LINE__, "No GLS ConsignmentId available or empty in GLS-API shipment response", Zend_Log::ERR);
            $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_CONSIGNMENTID_MISSING;
            $errorMessage = Mage::helper('synergeticagency_gls')->__('No GLS ConsignmentId available or empty in GLS-API shipment response');
            $this->setError($errorMessage,$errorCode);
        }

        $labels = $shipmentResponse->getLabels();
        if (!count($labels)) {
            $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_LABEL_INVALID;
            $errorMessage = sprintf(Mage::helper('synergeticagency_gls')->__('The label data with GLS-ConsignmentId %s obtained are not correct'),$consignmentId);
            $this->setError($errorMessage,$errorCode);
        }

        foreach ($labels as &$label) {
            $label = base64_decode($label);
            $pdfCheckLine = $label;
            $pdfCheckLine = trim(strtok($pdfCheckLine, "\n"));
            if (!preg_match("/^%PDF-[0-9]+\\.[0-9]+$/", $pdfCheckLine)) {
                Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, sprintf("The label data with GLS-ConsignmentId %s obtained are not correct", $shipmentResponse->getConsignmentId()), Zend_Log::INFO );
                Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, "\$pdfCheckLine=" . substr ( $pdfCheckLine, 0, 25 ), Zend_Log::INFO );
                $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_LABEL_INVALID;
                $errorMessage = sprintf(Mage::helper('synergeticagency_gls')->__('The label data with GLS-ConsignmentId %s obtained are not correct'),$consignmentId);
                $this->setError($errorMessage,$errorCode);
            }
        }

        // add tracking codes to magento shipment
        $this->addTracks();

        // set returned api data back
        $this->setConsignmentId($consignmentId);
        $this->setPrinted(1);
        $this->save();

        // return the labels for further processing
        return $labels;
    }


    /**
     * save given $label string to $file
     *
     * @param   string          $label          PDF string
     * @param   string          $file           full path/filename to save file
     * @return  bool|string     $returnValue    false, if something went wrong,
     *                                          $filename if saved successful
     */

    /**
     * save given $label string to $file
     *
     * @param   string          $label          PDF string
     * @param   string          $file           full path/filename to save file
     * @return  bool|string     $returnValue    false, if something went wrong,
     *                                          $filename if saved successful
     * @throws Exception
     */
    public function saveLabel( $label, $file ){
        Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, "entered", Zend_Log::DEBUG );

        $written = true;
        $returnValue = false;
        if( $label !== false ){
            if (!$handle = fopen($file, "w")) {
                Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, " cannot open file " . $file, Zend_Log::WARN );
                $written = false;
            } else {
                if (!fwrite($handle, $label)) {
                    Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, " cannot write into file " . $file, Zend_Log::WARN );
                    $written = false;
                } else {
                    $returnValue = $file;
                }
                fclose($handle);
            }
        }

        if( $written === false ){
            $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_LABEL_IO_ERROR;
            $errorMessage = sprintf(Mage::helper('synergeticagency_gls')->__('Failed to write %s'),$file) ."<br />".
                sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), $errorCode);
            $this->setError($errorMessage,$errorCode);
        }

        $this->setPdfFile(trim(str_replace(Mage::getBaseDir(),'',$file),DS)); // remove base dir from file path for relative path

        return $returnValue;
    }


    /**
     * @return null|SynergeticAgency_GlsConnector_Connector
     */
    public function getConnector() {
        $connector = $this->getData('connector');
        if(!$connector instanceof SynergeticAgency_GlsConnector_Connector) {
            $connector = Mage::helper("synergeticagency_gls")->getConnector($this->getStore());
            $this->setData('connector',$connector);
        }
        return $connector;
    }

    /**
     * @param int $var
     */
    public function setPrinted($var) {
        $this->setData('printed',$var);
        if($var == 1) { // only remove errors if $var is 1
            $this->setErrorCode('');
            $this->setErrorMessage('');
        }
    }

    /**
     * @return SynergeticAgency_GlsConnector_Model_Shipment
     */
    private function getConnectorShipment() {
        $connectorShipment = $this->getData('connector_shipment');
        if($connectorShipment instanceof SynergeticAgency_GlsConnector_Model_Shipment) {
            return $connectorShipment;
        }

        $connectorShipment = new SynergeticAgency_GlsConnector_Model_Shipment();

        // reformat date without time
        $shippingDate = date('Y-m-d',strtotime($this->getShippingDate()));
        $this->setShippingDate($shippingDate);
        // if shipping date is in the past and label is not printed set it to now
        if(strtotime($shippingDate) < strtotime(date('Y-m-d'))) {
            $shippingDate = date('Y-m-d');
            $this->setShippingDate($shippingDate);
            $this->save();
        }
        $magentoShipment = $this->getMagentoShipment();
        $store = $this->getStore();

        // set up shipment basic values
        $shipperId =    Mage::getStoreConfig('gls/general/customer_id', $store) .
            " " .
            Mage::getStoreConfig('gls/general/contact_id', $store);
        $connectorShipment
            ->setCustomerId( Mage::getStoreConfig('gls/general/customer_id',$store) )
            ->setContactId( Mage::getStoreConfig('gls/general/contact_id',$store) )
            ->setShipperId($shipperId)
            ->setShipmentDate($this->getShippingDate() )
            ->setReference( $magentoShipment->getOrder()->getIncrementId() );

        // set labelsize
        $connectorShipment->setLabelSize( Mage::helper("synergeticagency_gls/data")->getShipmentGlsConfig('labelsize') );

        // add useful references for shipment such as
        // shipment id concatinated with dash and parcel id, printed date and the controller information
        $shipmentReferences = array();
        /** @var SynergeticAgency_Gls_Model_Shipment_Parcel $glsShipmentParcel */
        foreach( $this->getShipmentParcels() AS $glsShipmentParcel){
            $shipmentReferences[] = $this->getId() . "-" . $glsShipmentParcel->getData()['gls_shipment_parcel_id'];
        }
        $shipmentReferences[] = date( "r" );
        $shipmentReferences[] = basename( __FILE__ );

        $connectorShipment->setReferences( $shipmentReferences );

        $this->setData('connector_shipment',$connectorShipment);
        return $connectorShipment;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function addTracks() {
        $parcels = $this->getConnector()->getShipment()->getParcels();
        $magentoShipment = $this->getMagentoShipment();
        foreach($parcels as $parcel) {
            $glsParcel = Mage::getModel('synergeticagency_gls/shipment_parcel')->load($parcel->getReferences()[0]);
            if($glsParcel->getId()) {
                $glsParcel->setTrackId($parcel->getTrackId());
                $glsParcel->setLocation($parcel->getLocation());
                $glsParcel->setParcelNumber($parcel->getParcelNumber());
                $glsParcel->save();

                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($parcel->getParcelNumber()) //tracking number / awb number
                    ->setCarrierCode(SynergeticAgency_Gls_Model_Carrier::CODE) //carrier code
                    ->setTitle(SynergeticAgency_Gls_Model_Carrier::CARRIER_TITLE); //carrier title
                $magentoShipment->addTrack($track);
            }
        }
        $magentoShipment->save();
        return $this;
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore() {
        if($this->getMagentoShipment() && $this->getMagentoShipment()->getId()) {
            $store = $this->getMagentoShipment()->getStore();
        } else {
            $store = Mage::app()->getStore();
        }
        return $store;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function doLabelChecks() {
        $magentoShipment = $this->getMagentoShipment();
        if(!$magentoShipment || !$magentoShipment->getId()) {
            $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_MAGE_SHIPMENT_MISSING;
            $errorMessage = Mage::helper('synergeticagency_gls')->__('Magento shipment is missing');
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, "Magento shipment is missing(Error-Code: $errorCode)", Zend_Log::ERR );
            $this->setError($errorMessage,$errorCode);
        }

        if($this->getPrinted() == '1') {
            $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_LABEL_ALREADY_PRINTED;
            $errorMessage = Mage::helper('synergeticagency_gls')->__('GLS Label is already printed');
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, "GLS Label is already printed(Error-Code: $errorCode)", Zend_Log::ERR );
            $this->setError($errorMessage,$errorCode);
        }

        $check = Mage::helper('synergeticagency_gls')->checkConfigSettings();
        if($check !== true) {
            $errorDetails = '';
            if( is_array($check) ){
                $errorDetails = "<br />";
                foreach( $check AS $key => $value ){
                    $errorDetails.= $key . ": " . $value . "<br />";
                }
            }
            $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_CONFIGURATION_INCOMPLETE;
            $errorMessage = sprintf(Mage::helper('synergeticagency_gls')->__('The GLS %s has errors or is not complete'), Mage::helper('synergeticagency_gls')->__('Basic configuration'))." <br />".$errorDetails;

            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, "The GLS Basic configuration has errors or is not complete(Error-Code: $errorCode)", Zend_Log::ERR );
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, var_export($check,1), Zend_Log::INFO );

            $this->setError($errorMessage,$errorCode);
        }
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function setConnectorDeliveryAddress() {
        $connectorShipment = $this->getConnectorShipment();
        /** @var SynergeticAgency_GlsConnector_Model_Address $connectorDeliveryAddress */
        $connectorDeliveryAddress = $connectorShipment->getAddresses( SynergeticAgency_GlsConnector_Model_Address::GLS_API_ADDRESS_DELIVERY );
        $glsShipmentAddress = $this->getShipmentAddress();
        if(!$glsShipmentAddress || !$glsShipmentAddress->getId()) {
            $errorMessage = Mage::helper('synergeticagency_gls')->__('GLS Shipment could not be created');
            $errorCode = SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_SHIPMENT_NOT_CREATED;
            $this->setError($errorMessage,$errorCode);
        }

        $connectorDeliveryAddress
            ->setName1( $glsShipmentAddress->getName1())
            ->setName2( $glsShipmentAddress->getName2())
            ->setName3( $glsShipmentAddress->getName3())
            ->setStreet1( $glsShipmentAddress->getStreet1())
            ->setCountry( $glsShipmentAddress->getCountry())
            ->setZipCode($glsShipmentAddress->getZipCode())
            ->setCity( $glsShipmentAddress->getCity())
            ->setEmail( $glsShipmentAddress->getEmail())
            ->setPhone( $glsShipmentAddress->getPhone());

        $parcelShopId = $this->getParcelshopId();
        if(!empty($parcelShopId)) {
            // todo: check in name context
            $connectorDeliveryAddress->setContact( $glsShipmentAddress->getName1() );
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function setAlternateShipperAddress() {
        $store = $this->getStore();
        if(Mage::getStoreConfig('gls/alternative_shipper/alternative_shipper_enabled',$store) === '1' &&  Mage::helper('synergeticagency_gls')->checkAlternativeShipper($store)) {
            $addressAlternativeShipping = new SynergeticAgency_GlsConnector_Model_Address();

            $addressAlternativeShipping->setType(SynergeticAgency_GlsConnector_Model_Address::GLS_API_ADDRESS_ALTERNATIVESHIPPING)
                ->setName1(Mage::getStoreConfig('gls/alternative_shipper/name1',$store))
                ->setName2(Mage::getStoreConfig('gls/alternative_shipper/name2',$store))
                ->setName3(Mage::getStoreConfig('gls/alternative_shipper/name3',$store))
                ->setStreet1(Mage::getStoreConfig('gls/alternative_shipper/street1',$store))
                ->setCountry(Mage::getStoreConfig('gls/alternative_shipper/country',$store))
                ->setZipCode(Mage::getStoreConfig('gls/alternative_shipper/zip_code',$store))
                ->setCity(Mage::getStoreConfig('gls/alternative_shipper/city',$store));
            $this->getConnectorShipment()->addAddress( $addressAlternativeShipping );
        }
        return $this;
    }

    /**
     * @return $this
     * @throws Zend_Locale_Exception
     */
    private function setParcels() {

        $glsServices = $this->getServices();
        $store = $this->getStore();

        $connectorShipment = $this->getConnectorShipment();
        $magentoShipment = $this->getMagentoShipment();
        $resultingServices = array();
        if($glsServices && count($glsServices)) {
            foreach($glsServices as $glsService) {
                $service = new SynergeticAgency_GlsConnector_Model_Service();
                switch($glsService->getId()) {
                    case SynergeticAgency_Gls_Model_Gls::SERVICE_EMAILNOTIFICATION:
                        //$service = new SynergeticAgency_GlsConnector_Model_Service();
                        //$service->setName(  );
                        // todo not available anymore??
                        //$service->setNotificationEmail($glsShipmentAddress->getEmail());
                        break;
                    case SynergeticAgency_Gls_Model_Gls::SERVICE_FLEXDELIVERY:
                        $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_FLEXDELIVERYSERVICE );
                        break;
                    case SynergeticAgency_Gls_Model_Gls::SERVICE_THINKGREEN:
                        $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_THINKGREENSERVICE);
                        break;
                    case SynergeticAgency_Gls_Model_Gls::SERVICE_PRIVATEDELIVERY:
                        $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_PRIVATEDELIVERYSERVICE);
                        break;
                    case SynergeticAgency_Gls_Model_Gls::SERVICE_GUARANTEED:
                        $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_GUARANTEED24SERVICE);
                        break;
                }
                if(!is_null($service->getName()) && $service->getName() !== '') {
                    $resultingServices[] = $service;
                }
            }
        }

        if($this->getReturnLabel() === '1' && Mage::helper('synergeticagency_gls')->checkReturnAddress($store)) {
            $service = new SynergeticAgency_GlsConnector_Model_Service();
            $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_SHOPRETURNSERVICE);
            $connectorShipment->setReturnLabel(true);
            $resultingServices[] = $service;
        }

        foreach($this->getShipmentParcels() as $parcel) {
            $connectorParcel = new SynergeticAgency_GlsConnector_Model_Parcel();
            $connectorParcel->setWeight($parcel->getWeight());
            $connectorParcel->setReferences( array($parcel->getId()) );

            if($parcel->getCashservice() > 0) {
                $service = new SynergeticAgency_GlsConnector_Model_Service();
                $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_CASHONDELIVERY);
                $symbols = Zend_Locale_Data::getList(Mage::getStoreConfig('general/locale/code', $store) , 'symbols');
                $info = new SynergeticAgency_GlsConnector_Model_Info();
                $info->setName('amount');
                $info->setValue(round(floatval(str_replace($symbols['decimal'], '.', $parcel->getCashservice())),2));
                // special case: if amount has no dezimal point, add decimal point and zero,
                // because info object is defined as string, but GLS needs "float string"
                if( !strpos($info->getValue() , '.') ){
                    $info->setValue( $info->getValue() . ".0" );
                }
                $service->pushInfo($info);

                $info = new SynergeticAgency_GlsConnector_Model_Info();
                $info->setName('reference');
                $info->setValue($magentoShipment->getOrder()->getIncrementId());
                $service->pushInfo($info);
                $connectorParcel->pushService($service);
            }

            if($this->getParcelshopId() !== '' && !is_null($this->getParcelshopId())) {
                $service = new SynergeticAgency_GlsConnector_Model_Service();
                $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_SHOPDELIVERYSERVICE);
                $info = new SynergeticAgency_GlsConnector_Model_Info();
                $info->setName('parcelshopid');
                $info->setValue($this->getParcelshopId());
                $service->pushInfo($info);
                $connectorParcel->pushService($service);
            }
            if(count($resultingServices))
            {
                foreach($resultingServices as $service) {
                    $connectorParcel->pushService($service);
                }
            }
            $connectorShipment->pushParcel($connectorParcel);
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function setReturnAddress() {
        if($this->getReturnLabel() == '1') {
            $store = $this->getStore();
            $connectorShipment = $this->getConnectorShipment();
            if (Mage::helper('synergeticagency_gls')->checkReturnAddress($store)) {
                $addressReturn = new SynergeticAgency_GlsConnector_Model_Address();
                $addressReturn->setType(SynergeticAgency_GlsConnector_Model_Address::GLS_API_ADDRESS_RETURN)
                    ->setName1(Mage::getStoreConfig('gls/return_address/name1', $store))
                    ->setName2(Mage::getStoreConfig('gls/return_address/name2', $store))
                    ->setName3(Mage::getStoreConfig('gls/return_address/name3', $store))
                    ->setStreet1(Mage::getStoreConfig('gls/return_address/street1', $store))
                    ->setCountry(Mage::getStoreConfig('gls/return_address/country', $store))
                    ->setZipCode(Mage::getStoreConfig('gls/return_address/zip_code', $store))
                    ->setCity(Mage::getStoreConfig('gls/return_address/city', $store));

                $connectorShipment->addAddress($addressReturn);
            }
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabelTempPath()
    {
        return $this->_labelTempPath;
    }

    /**
     * @param bool $labelTempPath
     * @return $this
     */
    public function setLabelTempPath( $labelTempPath = false )
    {
        if( $labelTempPath !== false && is_dir($labelTempPath) && is_writable($labelTempPath) ){
            $this->_labelTempPath = $labelTempPath;
        } else {
            $this->_labelTempPath = Mage::getBaseDir('var').DS."tmp".DS."gls";
        }

        return $this;
    }


    /**
     * @param $errorMessage
     * @param $errorCode
     * @param bool $exception
     * @throws Exception
     */
    private function setError($errorMessage,$errorCode,$exception=true) {
        $this->setErrorCode($errorCode);
        $this->setErrorMessage($errorMessage);
        if($this->getId()) {
            // only save if id is present
            $this->save(); // save error
        }
        if($exception) {
            throw Mage::exception('SynergeticAgency_Gls',get_class($this).': '.$errorMessage . ' ' .sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'),$errorCode),$errorCode);
        }
    }
}