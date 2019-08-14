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
* @package    SynergeticAgency\Gls\controllers\Adminhtml\Gls
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Adminhtml_Gls_ShipmentController
 */
class SynergeticAgency_Gls_Adminhtml_Gls_ShipmentController extends Mage_Adminhtml_Controller_Action {

    /**
     * Logger to track several things in application
     * @var $glsHelperLog SynergeticAgency_Gls_Helper_Log
     */
    private $glsHelperLog;


    /**
     * Data helper
     * @var $glsHelperData SynergeticAgency_Gls_Helper_Data
     */
    private $glsHelperData;

    /**
     * The Constructor. Used i.e. to init the logger
     */
    function  _construct(){
        $this->setGlsHelperLog( Mage::helper("synergeticagency_gls/log") );
        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "entered", Zend_Log::DEBUG );

        $this->setGlsHelperData( Mage::helper("synergeticagency_gls/data") );
    }


    /**
     * Initialising the sales GLS deliveries view
     */
    public function indexAction()
    {
        $this->_title($this->__('GLS'))->_title($this->__('GLS shipment list'));
        $this->loadLayout();
        $this->_setActiveMenu('sales/sales');
        $this->_addContent($this->getLayout()->createBlock('synergeticagency_gls/adminhtml_sales_gls_shipment'));
        $this->renderLayout();
    }

    /**
     * Initialising the sales GLS deliveries grid view
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('synergeticagency_gls/adminhtml_sales_gls_shipment_grid')->toHtml()
        );
    }

    /**
     * collect data for shipment and create shipment using GLS API Connector
     * @todo Refactor printAction: Too much code in one function ;-(
     * @return null
     * @throws Exception
     * @throws Zend_Locale_Exception
     */
    public function printAction() {

        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "entered", Zend_Log::DEBUG );

        $glsShipmentId = $this->getRequest()->getParam('id');
        $glsShipment = Mage::getModel('synergeticagency_gls/shipment')->load($glsShipmentId);
        $glsShipment->setSandbox( Mage::getStoreConfig('gls/general/sandbox',$glsShipment->getStore()) );
        $glsShipmentAddress = $glsShipment->getShipmentAddress();
        $glsShipmentParcels = $glsShipment->getShipmentParcels();

        $magentoShipment = Mage::getModel('sales/order_shipment')->load($glsShipment->getShipmentId());
        $store = $magentoShipment->getStore();

        // do prechecks
        $check = $this->doChecks($magentoShipment,$glsShipment);
        if($check === false) {
            $this->_redirectReferer();
            return null;
        }

        $connector = $this->getConnector($store);
        $shipment = $this->getShipment($magentoShipment,$glsShipment,$store);
        $deliveryAddress = $this->getDeliveryAddress($shipment,$glsShipmentAddress,$glsShipment);
        if($deliveryAddress === false) {
            $this->_redirectReferer();
            return null;
        }
        $check = $this->setAlternateShipperAddress($shipment,$store);
        if($check === false) {
            $this->_redirectReferer();
            return null;
        }

        $this->setParcels($glsShipment,$glsShipmentParcels,$store,$magentoShipment,$shipment);

        if($glsShipment->getReturnLabel() == '1') {
            $check = $this->setReturnAddress($shipment,$store);
            if($check === false) {
                $this->_redirectReferer();
                return null;
            }
        }


        // set labelsize
        $shipment->setLabelSize( $this->getGlsHelperData()->getShipmentGlsConfig('labelsize') );

        // add useful references for shipment such as
        // shipment id concatinated with dash and parcel id, printed date and the controller information
        $shipmentReferences = array();
        foreach( $glsShipmentParcels AS $glsShipmentParcel){
            $shipmentReferences[] = $glsShipment->getId() . "-" . $glsShipmentParcel->getdata()['gls_shipment_parcel_id'];
        }
        $shipmentReferences[] = date( "r" );
        $shipmentReferences[] = basename( __FILE__ );

        $shipment->setReferences( $shipmentReferences );

        // add shipment to the connector to be able to send the shipment to the GLS-API
        $connector->setShipment( $shipment );
        $this->getGlsHelperLog()->logApi( __METHOD__, __LINE__, $connector->getShipment()->toJson() );

        if( $this->getGlsHelperData()->getGeneralGlsConfig('logging_enabled') == 1 ){
            Mage::log( $connector->getShipment()->toJson(), Zend_Log::DEBUG );
        }

        // SynergeticAgency_Gls_Helper_Log
        try {
            // send shipment to the GLS-API
            $connector->createShipment();
        } catch( Exception $e ){
            $this->getGlsHelperLog()->logException( $e, __METHOD__, __LINE__, Zend_Log::ERR, 'GLS Shipment could not be created', "00010" );
            $this->_redirectReferer();
            return null;
        }

        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "GLS-API Response: ConsignmentId = " . $connector->getShipment()->getConsignmentId(), Zend_Log::INFO );
        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, $connector->getCurlResponse() );


        if( $this->getGlsHelperData()->getGeneralGlsConfig('logging_enabled') == 1 ){
            Mage::log( $connector->getCurlResponse(), Zend_Log::DEBUG );
        }

        // process response of an sent shipment ----------------------------
        // handle GLS-API Response Errors
        if( $connector->getError()->hasError() === true ){

            Mage::getSingleton('adminhtml/session')->addError(
                sprintf(Mage::helper('synergeticagency_gls')->__('GLS-API Connector has caused an error: %s'), $connector->getError()->getMessage()) .
                "<br /><textarea name=\"gls_json_debug\" style=\"width: 80%;\">" . $connector->getShipment()->toJson() . $connector->getError()->getModelState( true ) .
                "</textarea><br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00005")
            );
            $this->getGlsHelperLog()->logApi( __METHOD__, __LINE__, "GLS-API Connector has caused an error: " . $connector->getError()->getMessage(), Zend_Log::ERR );
            $this->getGlsHelperLog()->logApi( __METHOD__, __LINE__, $connector->getError()->getModelState(true) );

            $this->_redirectReferer();
            return null;
        } else {
            // get the shipment object right after the GLS-API Response for further processing
            // i.e. to write the pdf into a file
            /** @var SynergeticAgency_GlsConnector_Model_Shipment $shipmentResponse */
            $shipmentResponse = $connector->getShipment();

            $consignmentId = $shipmentResponse->getConsignmentId();
            if(empty($consignmentId)) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('synergeticagency_gls')->__('No GLS ConsignmentId available or empty in GLS-API shipment response') .
                    "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00006")
                );
                $this->getGlsHelperLog()->logApi( __METHOD__, __LINE__, "No GLS ConsignmentId available or empty in GLS-API shipment response", Zend_Log::ERR );
                $this->_redirectReferer();
                return null;
            }

            $glsShipment->setConsignmentId($consignmentId);
            $glsShipment->setPrinted(1);
            $glsShipment->save();

            $parcels = $shipmentResponse->getParcels();
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


            // handle GLS label response and send header containing label(s) START
            $labelHeaderData = false;
            $labels = $shipmentResponse->getLabels();
            $tmpLabelFilesArray = false;

            // combine labels, if shipment response has more than one label
            if( is_array($labels) ){
                if( count($labels) > 1 ){
                    $this->getGlsHelperLog()->log( __METHOD__, __LINE__, count($labels) . " Labels in GLS response", Zend_Log::INFO );
                    $tmpLabelFilesArray = array();
                    $tmpLabelPath = rtrim(Mage::getBaseDir('var') ,DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

                    foreach( $labels as $key => $label){
                        $savedLabel = $this->decodeAndSaveLabel( $label, $tmpLabelPath . $shipmentResponse->getConsignmentId() . '.' . str_pad ( $key, 3, 0, STR_PAD_RIGHT ) . '.pdf');
                        if( $savedLabel !== false && is_readable($savedLabel) ){
                            $tmpLabelFilesArray[$key] = $savedLabel;
                        }
                    }

                    if( count($tmpLabelFilesArray) > 1 ){
                        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "combine " . count($labels) . " GLS Labels into one file", Zend_Log::INFO );
                        $combinedFile = $connector->combineLabels( $tmpLabelFilesArray, $target = false );

                        if( is_readable($combinedFile) ){
                            $labelHeaderData = file_get_contents( $combinedFile );
                        }
                    }

                } else {
                    // just for debugging: write and save label START
                    #$tmpLabelPath = rtrim(Mage::getBaseDir('var') ,DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                    #if( base64_decode($shipmentResponse->getLabels()[0]) === false ){
                    #    print "FAILED base64_decode \$shipmentResponse->getLabels()[0]" . $shipmentResponse->getLabels()[0];
                    #    exit();
                    #} else {
                    #    print "SUCCESS base64_decode \$shipmentResponse->getLabels()[0]:<br><pre>" . ($shipmentResponse->getLabels()[0]) . "</pre><br>";
                    #    $this->decodeAndSaveLabel( $shipmentResponse->getLabels()[0], $tmpLabelPath . date("Ymd-His") . "." . $shipmentResponse->getConsignmentId() . '.pdf' );
                    #    exit();
                    #}
                    // just for debugging: write and save label STOP

                    $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "One label in GLS response", Zend_Log::INFO );
                    $labelHeaderData = base64_decode($shipmentResponse->getLabels()[0]);
                }
            }

            $pdfCheckLine = trim(strtok($labelHeaderData, "\n"));
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "\$pdfCheckLine=" . substr ( $pdfCheckLine, 0, 25 ), Zend_Log::INFO );
            if( !preg_match("/^%PDF-[0-9]+\\.[0-9]+$/", $pdfCheckLine) ){

                Mage::getSingleton('adminhtml/session')->addError(
                    sprintf(Mage::helper('synergeticagency_gls')->__('The label data with GLS-ConsignmentId %s obtained are not correct'), $shipmentResponse->getConsignmentId()) .
                    "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00013")
                );
                $this->getGlsHelperLog()->log( __METHOD__, __LINE__, sprintf("The label data with GLS-ConsignmentId %s obtained are not correct", $shipmentResponse->getConsignmentId()), Zend_Log::INFO );

                $glsShipment->setPrinted(0);
                $glsShipment->save();

                $this->_redirectReferer();
                return null;
            }

            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "\$labelHeaderData=" . substr ( $labelHeaderData, 0, 25 ), Zend_Log::DEBUG );
            if( $labelHeaderData !== false ){
                $this->_prepareDownloadResponse(
                    $shipmentResponse->getConsignmentId() . '.pdf',
                    $labelHeaderData,
                    'application/pdf'
                );
                $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "Label"  . $shipmentResponse->getConsignmentId() . ".pdf sent to header", Zend_Log::INFO );

            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    sprintf(Mage::helper('synergeticagency_gls')->__('Creation of the label with the GLS-ConsignmentId %s failed'), $shipmentResponse->getConsignmentId()) .
                    "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00012")
                );
                $this->getGlsHelperLog()->log( __METHOD__, __LINE__, sprintf('Creation of the label with the GLS-ConsignmentId %s failed'), $shipmentResponse->getConsignmentId(), Zend_Log::ERR );
                $this->_redirectReferer();
                return null;
            }

            // remove temporary saved labels
            if( is_array($tmpLabelFilesArray) && count($tmpLabelFilesArray) > 0 ){
                $removedLabelFiles = $connector->removeLabels( $tmpLabelFilesArray );
                $this->getGlsHelperLog()->log( __METHOD__, __LINE__, $removedLabelFiles . " temporary files deleted", Zend_Log::INFO );
            }
            // handle GLS label response and send header containing label(s) STOP
        }
    }

    /**
     * collect data for requested shipment and renders the edit shipment grid
     * @return null
     */
    public function editAction() {
        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "entered", Zend_Log::DEBUG );
        $id = $this->getRequest()->getParam('id');
        $glsShipment = Mage::getModel('synergeticagency_gls/shipment')->load($id);
        $glsShipment->setSandbox( Mage::getStoreConfig('gls/general/sandbox',$glsShipment->getStore()) );
        if (!$glsShipment->getId() || $glsShipment->getPrinted() == '1') {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('GLS Shipment can not be edited') .
                "<br />" . Mage::helper('synergeticagency_gls')->__('The shipment id is not set or found') .
                "<br />" . Mage::helper('synergeticagency_gls')->__('or') .
                "<br />" . Mage::helper('synergeticagency_gls')->__('The label has already been printed') .
                "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00007")
            );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "GLS Shipment can not be edited. " . get_class($glsShipment) . "::id is not set/found or the label has already been printed: " . get_class($glsShipment) . "::printed == 1" , Zend_Log::ERR );
            $this->_redirectReferer();
            return null;
        }
        $shipment = Mage::getModel('sales/order_shipment')->load($glsShipment->getShipmentId());
        Mage::register('current_shipment', $shipment);
        Mage::register('gls_shipment', $glsShipment);
        $this->_title($this->__('GLS'))->_title($this->__('Edit GLS Shipment'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Creates a GLS delivery note by contacting the GLS API
     * @return null
     */
    public function saveAction() {
        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "entered", Zend_Log::DEBUG );
        $request = $this->getRequest();
        $shimentId = $request->getParam('shipment_id');
        $id = $request->getParam('id');
        $data = $request->getPost();
        $shipment = Mage::getModel('sales/order_shipment')->load($shimentId);
        $glsShipment = Mage::getModel('synergeticagency_gls/shipment')->load($id);
        $glsShipment->setSandbox( Mage::getStoreConfig('gls/general/sandbox',$shipment->getStore()) );
        if($glsShipment->getPrinted() == '1') {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('GLS Shipment can not be saved') .
                "<br />" . Mage::helper('synergeticagency_gls')->__('The label has already been printed') .
                "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00008")
            );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "GLS Shipment can not be saved. The label has already been printed: " . get_class($glsShipment) . "::printed == 1" , Zend_Log::ERR );
            $this->_redirect('adminhtml/sales_shipment/view/',array('shipment_id' => $shimentId));
            return null;
        }

        if($data['ship_with_gls'] === '0') {
            $glsShipment->delete();
            Mage::getSingleton('adminhtml/session')->addNotice(
                Mage::helper('synergeticagency_gls')->__('GLS Shipment successfully deleted')
            );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "GLS Shipment successfully deleted" );
            $this->_redirect('adminhtml/sales_shipment/view/',array('shipment_id' => $shimentId));
            return null;
        }

        $glsModel = Mage::getModel('synergeticagency_gls/gls');
        try {
            $glsModel->saveGlsShipment($data, $shipment, $glsShipment);
        } catch(Exception $e) {
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "\$data: " . var_export($data, 1), Zend_Log::INFO);
            //$this->getGlsHelperLog()->log( __METHOD__, __LINE__, "\$shipment: " . var_export($shipment, 1), Zend_Log::INFO);
            //$this->getGlsHelperLog()->log( __METHOD__, __LINE__, "\$glsShipment:" . var_export($glsShipment, 1), Zend_Log::INFO);
            $this->getGlsHelperLog()->logException( $e, __METHOD__, __LINE__, Zend_Log::ERR, 'GLS Shipment could not be saved', "00009" );
            $this->_redirectReferer();
            return null;
        }
        Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('synergeticagency_gls')->__('GLS Shipment successfully saved'));
        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "GLS Shipment successfully saved", Zend_Log::NOTICE );
        $this->_redirect('adminhtml/sales_shipment/view/',array('shipment_id' => $shimentId));
    }

    /**
     * Getting the logger
     * @return SynergeticAgency_Gls_Helper_Log
     */
    public function getGlsHelperLog()
    {
        return $this->glsHelperLog;
    }

    /**
     * Setting the logger
     * @param SynergeticAgency_Gls_Helper_Log $glsHelperLog
     */
    public function setGlsHelperLog($glsHelperLog)
    {
        $this->glsHelperLog = $glsHelperLog;
    }

    /**
     * @return SynergeticAgency_Gls_Helper_Data
     */
    public function getGlsHelperData()
    {
        return $this->glsHelperData;
    }

    /**
     * setting teh data helper
     * @param SynergeticAgency_Gls_Helper_Data $glsHelperData
     */
    public function setGlsHelperData($glsHelperData)
    {
        $this->glsHelperData = $glsHelperData;
    }



    /**
     * @param Mage_Sales_Model_Order_Shipment $magentoShipment
     * @param SynergeticAgency_Gls_Model_Shipment $glsShipment
     * @return bool
     */
    private function doChecks($magentoShipment,$glsShipment) {
        if(!$magentoShipment->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('Magento shipment is missing') .
                sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00001")
            );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "Magento shipment is missing(Error-Code: 00001)", Zend_Log::ERR );
            return false;
        }

        if($glsShipment->getPrinted() == '1') {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('GLS Label is already printed') .
                sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00002")
            );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "GLS Label is already printed(Error-Code: 00002)", Zend_Log::ERR );
            return false;
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

            Mage::getSingleton('adminhtml/session')->addError(
                sprintf(Mage::helper('synergeticagency_gls')->__('The GLS %s has errors or is not complete'), Mage::helper('synergeticagency_gls')->__('Basic configuration')) .
                sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), "00003") .
                "<br />" . $errorDetails
            );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "The GLS Basic configuration has errors or is not complete(Error-Code: 00003)", Zend_Log::ERR );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, var_export($check,1), Zend_Log::INFO );



            return false;
        }
        return true;
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return SynergeticAgency_GlsConnector_Connector
     */
    private function getConnector($store) {
        $connector = new SynergeticAgency_GlsConnector_Connector();

        // in default sandbox is enabled
        if( Mage::getStoreConfig('gls/general/sandbox',$store) !== '1' ){
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "gls/general/sandbox is set to '0'", Zend_Log::INFO );
            $connector->setGlsApiSandbox( false );
        }

        $connector->setGlsApiUrl(Mage::getStoreConfig('gls/general/api_url',$store));
        $connector->setGlsApiAuthUsername( Mage::getStoreConfig('gls/general/apiAuthUsername',$store) );
        $connector->setGlsApiAuthPassword( Mage::helper('core')->decrypt(Mage::getStoreConfig('gls/general/apiAuthPassword', $store)) );

        // disabled by default
        if(Mage::getStoreConfig('gls/general/connector_log_enabled',$store) === '1') {
            $connector->getLog()->activate()->setLogFile(Mage::getBaseDir('log').DS.'SynergeticAgency_GlsConnector.log');
        }
        return $connector;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $magentoShipment
     * @param SynergeticAgency_Gls_Model_Shipment $glsShipment
     * @param Mage_Core_Model_Store $store
     * @return SynergeticAgency_GlsConnector_Model_Shipment
     */
    private function getShipment($magentoShipment,$glsShipment,$store) {
        $shipment = new SynergeticAgency_GlsConnector_Model_Shipment();


        // reformat date without time
        $shippingDate = date('Y-m-d',strtotime($glsShipment->getShippingDate()));
        $glsShipment->setShippingDate($shippingDate);
        // if shipping date is in the past and label is not printed set it to now
        if(strtotime($shippingDate) < strtotime(date('Y-m-d'))) {
            $shippingDate = date('Y-m-d');
            $glsShipment->setShippingDate($shippingDate);
            $glsShipment->save();
        }

        // set up shipment basic values
        $shipperId =    Mage::getStoreConfig('gls/general/customer_id', $store) .
            " " .
            Mage::getStoreConfig('gls/general/contact_id', $store);
        $shipment
            ->setCustomerId( Mage::getStoreConfig('gls/general/customer_id',$store) )
            ->setContactId( Mage::getStoreConfig('gls/general/contact_id',$store) )
            ->setShipperId($shipperId)
            ->setShipmentDate($glsShipment->getShippingDate() )
            ->setReference( $magentoShipment->getOrder()->getIncrementId() );
        return $shipment;
    }

    /**
     * @param SynergeticAgency_GlsConnector_Model_Shipment $shipment
     * @param SynergeticAgency_Gls_Model_Shipment_Address $glsShipmentAddress
     * @param SynergeticAgency_Gls_Model_Shipment $glsShipment
     * @return bool|SynergeticAgency_GlsConnector_Model_Address
     */
    private function getDeliveryAddress($shipment,$glsShipmentAddress,$glsShipment) {

        /** @var SynergeticAgency_GlsConnector_Model_Address $deliveryAddress */
        $deliveryAddress = $shipment->getAddresses( SynergeticAgency_GlsConnector_Model_Address::GLS_API_ADDRESS_DELIVERY );

        try {
            $deliveryAddress
                ->setName1( $glsShipmentAddress->getName1())
                ->setName2( $glsShipmentAddress->getName2())
                ->setName3( $glsShipmentAddress->getName3())
                ->setStreet1( $glsShipmentAddress->getStreet1())
                ->setCountry( $glsShipmentAddress->getCountry())
                ->setZipCode($glsShipmentAddress->getZipCode())
                ->setCity( $glsShipmentAddress->getCity())
                ->setEmail( $glsShipmentAddress->getEmail())
                ->setPhone( $glsShipmentAddress->getPhone());

            $parcelShopId = $glsShipment->getParcelshopId();
            if(!empty($parcelShopId)) {
                // todo: check in name context
                $deliveryAddress->setContact( $glsShipmentAddress->getName1() );
            }
        } catch( Exception $e ){
            $this->getGlsHelperLog()->logException( $e, __METHOD__, __LINE__, Zend_Log::ERR, 'GLS Shipment could not be created', "00010" );
            return false;
        }
        return $deliveryAddress;
    }

    /**
     * @param SynergeticAgency_GlsConnector_Model_Shipment $shipment
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    private function setAlternateShipperAddress($shipment,$store) {
        if(Mage::getStoreConfig('gls/alternative_shipper/alternative_shipper_enabled',$store) === '1' &&  Mage::helper('synergeticagency_gls')->checkAlternativeShipper($store)) {
            $addressAlternativeShipping = new SynergeticAgency_GlsConnector_Model_Address();
            try {
                $addressAlternativeShipping->setType(SynergeticAgency_GlsConnector_Model_Address::GLS_API_ADDRESS_ALTERNATIVESHIPPING)
                    ->setName1(Mage::getStoreConfig('gls/alternative_shipper/name1',$store))
                    ->setName2(Mage::getStoreConfig('gls/alternative_shipper/name2',$store))
                    ->setName3(Mage::getStoreConfig('gls/alternative_shipper/name3',$store))
                    ->setStreet1(Mage::getStoreConfig('gls/alternative_shipper/street1',$store))
                    ->setCountry(Mage::getStoreConfig('gls/alternative_shipper/country',$store))
                    ->setZipCode(Mage::getStoreConfig('gls/alternative_shipper/zip_code',$store))
                    ->setCity(Mage::getStoreConfig('gls/alternative_shipper/city',$store));
            } catch( Exception $e ){
                $this->getGlsHelperLog()->logException( $e, __METHOD__, __LINE__, Zend_Log::ERR, 'GLS Shipment could not be created', "00011" );
                return false;
            }
            $shipment->addAddress( $addressAlternativeShipping );
        }
        return true;
    }

    /**
     * @param SynergeticAgency_GlsConnector_Model_Shipment $shipment
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    private function setReturnAddress($shipment,$store) {
        if(Mage::helper('synergeticagency_gls')->checkReturnAddress($store)) {
            $addressReturn = new SynergeticAgency_GlsConnector_Model_Address();
            try {
                $addressReturn->setType(SynergeticAgency_GlsConnector_Model_Address::GLS_API_ADDRESS_RETURN)
                    ->setName1(Mage::getStoreConfig('gls/return_address/name1',$store))
                    ->setName2(Mage::getStoreConfig('gls/return_address/name2',$store))
                    ->setName3(Mage::getStoreConfig('gls/return_address/name3',$store))
                    ->setStreet1(Mage::getStoreConfig('gls/return_address/street1',$store))
                    ->setCountry(Mage::getStoreConfig('gls/return_address/country',$store))
                    ->setZipCode(Mage::getStoreConfig('gls/return_address/zip_code',$store))
                    ->setCity(Mage::getStoreConfig('gls/return_address/city',$store));
            } catch( Exception $e ){
                $this->getGlsHelperLog()->logException( $e, __METHOD__, __LINE__, Zend_Log::ERR, 'GLS Shipment could not be created', "00011" );
                return false;
            }
            $shipment->addAddress( $addressReturn );
        }
        return true;
    }

    /**
     * @param SynergeticAgency_Gls_Model_Shipment $glsShipment
     * @param array $glsShipmentParcels
     * @param Mage_Core_Model_Store $store
     * @param Mage_Sales_Model_Order_Shipment $magentoShipment
     * @param SynergeticAgency_GlsConnector_Model_Shipment $shipment
     */
    private function setParcels($glsShipment,$glsShipmentParcels,$store,$magentoShipment,$shipment) {

        $glsServices = $glsShipment->getServices();
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

        if($glsShipment->getReturnLabel() === '1' && Mage::helper('synergeticagency_gls')->checkReturnAddress($store)) {
            $service = new SynergeticAgency_GlsConnector_Model_Service();
            $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_SHOPRETURNSERVICE);
            $shipment->setReturnLabel(true);
            $resultingServices[] = $service;
        }

        foreach($glsShipmentParcels as $parcel) {
            $apiParcel = new SynergeticAgency_GlsConnector_Model_Parcel();
            $apiParcel->setWeight($parcel->getWeight());
            #// sorry, but we cannot use the database table fields
            #// synergeticagency_gls_shipment_parcel.gls_shipment_parcel_id and
            #// synergeticagency_gls_shipment_parcel.gls_shipment_id
            #// as reference for the GLS-API, because the GLS-API just accepts non assosiate arrays here
            #$apiParcel->pushReference( $parcel->getId(), "gls_shipment_parcel_id" );
            #$apiParcel->pushReference( $glsShipment->getId(), "gls_shipment_id" );
            $apiParcel->setReferences( array($parcel->getId()) );

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
                $apiParcel->pushService($service);
            }

            if($glsShipment->getParcelshopId() !== '' && !is_null($glsShipment->getParcelshopId())) {
                $service = new SynergeticAgency_GlsConnector_Model_Service();
                $service->setName( SynergeticAgency_GlsConnector_Model_Service::GLS_API_SERVICE_SHOPDELIVERYSERVICE);
                $info = new SynergeticAgency_GlsConnector_Model_Info();
                $info->setName('parcelshopid');
                $info->setValue($glsShipment->getParcelshopId());
                $service->pushInfo($info);
                $apiParcel->pushService($service);
            }
            if(count($resultingServices))
            {
                foreach($resultingServices as $service) {
                    $apiParcel->pushService($service);
                }
            }
            $shipment->pushParcel($apiParcel);
        }
    }


    /**
     * decode base64 string and save to $file
     *
     * @param   string          $label          base64 encoded string
     * @param   string          $file           full path/filename to save file
     * @return  bool|string     $returnValue    false, if something went wrong,
     *                                          $filename if saved successful
     */
    private function decodeAndSaveLabel( $label, $file ){
        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "entered", Zend_Log::DEBUG );

        $returnValue = false;

        $base64decodedLabel = base64_decode($label);
        if( $base64decodedLabel !== false ){
            if (!$handle = fopen($file, "w")) {
                $this->getGlsHelperLog()->log( __METHOD__, __LINE__, " cannot open file " . $file, Zend_Log::WARN );
            } else {
                if (!fwrite($handle, $base64decodedLabel)) {
                    $this->getGlsHelperLog()->log( __METHOD__, __LINE__, " cannot write into file " . $file, Zend_Log::WARN );
                } else {
                    $returnValue = $file;
                }
                fclose($handle);
            }
        }

        return $returnValue;
    }
}
