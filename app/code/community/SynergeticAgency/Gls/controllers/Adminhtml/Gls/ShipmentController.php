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
     * Updates parcel quantity via ajax
     */
    public function updateqtyAction() {
        $id = $this->getRequest()->getParam('id',null);
        $qty = $this->getRequest()->getParam('qty',null);

        if(!is_numeric($id) || !is_numeric($qty)) {
            echo json_encode(array('error' => $this->__('Request invalid')));
            die();
        }
        $id = intval($id);
        $qty = intval($qty);
        $glsShipment = Mage::getModel('synergeticagency_gls/shipment')->load($id);
        if(!$glsShipment || !$glsShipment->getId()) {
            echo json_encode(array('error' => $this->__('Request invalid')));
            die();
        }
        $parcels = $glsShipment->getShipmentParcels();
        $origQty = $parcels->count();
        if($qty === $origQty || $qty < SynergeticAgency_Gls_Model_Shipment::GLS_SHIPMENT_MIN_PARCELS || $qty > SynergeticAgency_Gls_Model_Shipment::GLS_SHIPMENT_MAX_PARCELS) {
            echo json_encode(array('error' => sprintf($this->__('No valid number. Please enter numbers between %s and %s.'),SynergeticAgency_Gls_Model_Shipment::GLS_SHIPMENT_MIN_PARCELS, SynergeticAgency_Gls_Model_Shipment::GLS_SHIPMENT_MAX_PARCELS)));
            die();
        }
        if(Mage::helper('synergeticagency_gls/validate')->isCashService($glsShipment->getMagentoShipment())) {
            echo json_encode(array('error' => $this->__('Not available with cash on delivery')));
            die();
        }
        if($glsShipment->getPrinted()) {
            echo json_encode(array('error' => $this->__('Shipment already printed')));
            die();
        }
        if($glsShipment->getJobId()) {
            echo json_encode(array('error' => $this->__('Shipment in mass action')));
            die();
        }
        if($qty < $origQty) {
            $parcels = $glsShipment->getShipmentParcels(Varien_Data_Collection::SORT_ORDER_DESC); // different order
            $qtyRemove = $origQty-$qty;
            $i=0;
            foreach($parcels as $parcel) {
                if($i < $qtyRemove) {
                    $parcel->delete();
                } else {
                    break;
                }
                $i++;
            }
        } else {
            $qtyAdd = $qty-$origQty;
            $firstParcel = $parcels->getFirstItem();
            for($i=0;$i<$qtyAdd;$i++) {
                $newParcel = clone $firstParcel;
                $newParcel->setId(null);
                $newParcel->save();
            }
        }
        echo json_encode(array('success' => true));
        die();
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
     * Mass creation of magento shipments and gls shipments from order grid(sales_order_grid_massaction-select: createGlsShipment)
     */
    public function masscreateAction() {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if(!is_array($orderIds) || !count($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('Please select orders to process')
            );
            $this->_redirectReferer();
            return null;
        }
        $helper = Mage::helper('synergeticagency_gls');
        $createdOrderIds = array();
        foreach($orderIds as $orderId) {
            try {
                $order = Mage::getModel('sales/order')->load((int)$orderId);
                if (!$order || !$order->getId()) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synergeticagency_gls')->__('Order is not available anymore. OrderId:').' '.$orderId);
                    continue;
                }

                if($order->getShipmentsCollection() && count($order->getShipmentsCollection())) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synergeticagency_gls')->__('Shipment could not be created. Shipment already exists. OrderId:').' '.$order->getIncrementId());
                    //$helper->setOrderStatus($order,$helper->__('GLS shipment creating error:').' '.$helper->__('Shipment already exists'),true);
                    // we don't add error here, because its common to select already created ones and we don't want to have errors everywhere
                    continue;
                }

                if(!$order->canShip()) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synergeticagency_gls')->__('Shipment could not be created. Please check order data manually. OrderId:').' '.$order->getIncrementId());
                    $helper->setOrderStatus($order,$helper->__('GLS shipment creating error:').' '.$helper->__('Undefined error'),true);
                    continue;
                }
                $store = $order->getStore();
                $doNotify = (int)Mage::getStoreConfig('gls/shipment/notify_customer_shipment',$store);

                $shipment = $order->prepareShipment();

                $glsModel = Mage::getModel('synergeticagency_gls/gls');
                $data = $glsModel->prepareGlsShipmentData($shipment);
                if($data === false) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synergeticagency_gls')->__('Shipment could not be created. Please check order data manually. OrderId:').' '.$order->getIncrementId());
                    $helper->setOrderStatus($order,$helper->__('GLS shipment creating error:').' '.$helper->__('Invalid order data for GLS shipment'),true);
                    continue;
                }
                $check = $glsModel->validateGlsShipmentData($data,$shipment);
                if($check !== true) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synergeticagency_gls')->__('Shipment could not be created. Please check order data manually. OrderId:').' '.$order->getIncrementId()." Error Message: ".$check);
                    $helper->setOrderStatus($order,$helper->__('GLS shipment creating error:').' '.$helper->__('Invalid order data for GLS shipment'),true);
                    continue;
                }

                $shipment->register();
                $shipment->setEmailSent($doNotify);
                $shipment->getOrder()->setIsInProcess(true);
                Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

                if ($doNotify) $shipment->sendEmail($doNotify, '');

                // create gls shipment
                try {
                    $glsModel->saveGlsShipment($data, $shipment);
                    $helper->setOrderStatus($order,$helper->__('GLS shipment created successful'));
                    $createdOrderIds[] = $order->getIncrementId();
                } catch(Exception $e) {
                    $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "\$data: " . var_export($data, 1), Zend_Log::INFO);
                    $this->getGlsHelperLog()->logException( $e, __METHOD__, __LINE__, Zend_Log::ERR, 'GLS Shipment could not be saved', SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_SHIPMENT_NOT_CREATED);
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synergeticagency_gls')->__('Shipment could not be created. Please check order data manually. OrderId:').' '.$order->getIncrementId());
                    $helper->setOrderStatus($order,$helper->__('GLS shipment creating error:').' '.$helper->__('Undefined error'),true);
                    continue;
                }
                unset($shipment);
            } catch(Exception $e) {
                if (!empty($order) && $order->getIncrementId()) {
                    $orderId = $order->getIncrementId();
                    $helper->setOrderStatus($order,$helper->__('GLS shipment creating error:').' '.$helper->__('Undefined error'),true);
                }
                Mage::getSingleton('adminhtml/session')->addError('Exception (Order # ' . $orderId . '): ' . $e->getMessage());
            }
        }

        if(count($createdOrderIds)) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                Mage::helper('synergeticagency_gls')->__('Shipments created for following order ids:') . ' ' . implode(' / ', $createdOrderIds)
            );
        }
        $this->_redirectReferer();
    }

    /**
     * Mass print from gls shipment grid. This will prepare cron data(synergeticagency_gls_shipment_grid_massaction-select: print)
     * This function will operate with gls-shipment ID's
     *
     */
    public function massprintshipmentAction() {
        $glsShipmentIds = $this->getRequest()->getParam('gls_shipment_id');
        $shipmentsCount = 0;
        if(!is_array($glsShipmentIds) || !count($glsShipmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('Please select shipments to process')
            );
            $this->_redirectReferer();
            return null;
        }

        $maxShipments = (int)Mage::getStoreConfig('gls/shipment/max_batch_amount');
        if(empty($maxShipments)) {
            $maxShipments = 100;
        }

        $glsShipmentCollection = Mage::getModel('synergeticagency_gls/shipment')->getCollection();
        $glsShipmentCollection
            ->addFieldToSelect('*') // needed fore save - otherwise we would have incomplete objects
            ->addFieldToFilter('gls_shipment_id',array('in' => $glsShipmentIds))
            ->addFieldToFilter('printed',array('neq' => 1))
            ->addFieldToFilter('job_id',array('null' => true))
            ->addOrder('gls_shipment_id',$glsShipmentCollection::SORT_ORDER_ASC) // FIFO
            ->load();

        if($glsShipmentCollection->count() > 0)  {
            $job = Mage::getModel( 'synergeticagency_gls/shipment_job' );
            $job->setDataChanges( true ); // otherwise no save is possible and we need the id
            $job->setCreatedAt(Mage::getModel('core/date')->gmtDate());
            $job->save();
            foreach($glsShipmentCollection as $glsShipment) {
                if($shipmentsCount >= $maxShipments) {
                    break;
                }
                $glsShipment->setJobId( $job->getId() );
                $glsShipment->save();
                $shipmentsCount++;
            }

            Mage::getSingleton('adminhtml/session')->addNotice(
                sprintf(Mage::helper('synergeticagency_gls')->__('Successfully created mass print job with %s items'),$shipmentsCount)
            );
            $job->setQtyItemsOpen($shipmentsCount);
            $job->save();

            $this->_redirect('adminhtml/gls_shipment_job/index/');
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('No labels could be printed')
            );
            $this->_redirectReferer();
        }
    }

    /**
     * Mass print from order grid. This will prepare cron data(sales_order_grid_massaction-select: printGlsLabels)
     * This function will operate with order ID's
     *
     * @return null
     * @throws Exception
     */
    public function massprintAction() {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if(!is_array($orderIds) || !count($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('Please select orders to process')
            );
            $this->_redirectReferer();
            return null;
        }

        // sort orders by "first in, first out"
        sort($orderIds,SORT_NUMERIC);

        $maxShipments = (int)Mage::getStoreConfig('gls/shipment/max_batch_amount');
        if(empty($maxShipments)) {
            $maxShipments = 100;
        }
        $notProcessedOrders = array();
        // use direct, to combine the labesls without using the cron. In this case a header will be sent including all labels.
        // currently this "direct" implementation is implemented for testing purposes. However, this could be used as additional feature later on.
        $direct = false;
        $labels = array();
        $errorMessages = array();
        $shipmentsCount = 0;
        $job = Mage::getModel('synergeticagency_gls/shipment_job');
        $job->setDataChanges(true); // otherwise no save is possible and we need the id
        $job->setCreatedAt(Mage::getModel('core/date')->gmtDate());
        $job->save();
        foreach($orderIds as $orderId) {
            $glsShipmentCollection = Mage::getModel('synergeticagency_gls/shipment')->getCollection();
            $glsShipmentCollection
                ->addFieldToFilter('printed',array('neq' => 1))
                ->addFieldToFilter('job_id',array('null' => true))
                ->addFieldToFilter('order_id',$orderId)
                ->load();
            if(!$glsShipmentCollection->count()) {
                $notProcessedOrders[] = $orderId;
                continue;
            }
            foreach($glsShipmentCollection as $glsShipment) {
                if($direct) {
                    try {
                        $glsShipmentLabels = $glsShipment->getLabels();
                        if(!count($glsShipmentLabels)) {
                            $notProcessedOrders[] = $orderId;
                            continue;
                        }
                        $labels = array_merge($labels,$glsShipmentLabels);
                    } catch (Exception $e) {
                        if(!$glsShipment->getErrorMessage()) {
                            $glsShipment->setErrorMessage($e->getMessage());
                            $glsShipment->setErrorCode(SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_UNDEFINED); // undefined
                            $glsShipment->save();
                        }
                        $errorMessages[] = $e->getMessage();
                        $notProcessedOrders[] = $orderId;
                    }
                } else {
                    if($shipmentsCount > $maxShipments) {
                        break;
                    }
                    $shipmentsCount++;
                    $glsShipment->setJobId($job->getId());
                    $glsShipment->save();
                }
            }
            if($shipmentsCount > $maxShipments) {
                break;
            }
        }

        if(count($notProcessedOrders)) {
            // get the increment ids
            $incrementIds = array();
            $orderCollection = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToSelect('increment_id')
                ->addFieldToFilter('entity_id',array('in' => $notProcessedOrders))
                ->load();
            foreach($orderCollection as $order) {
                $incrementIds[] = $order->getIncrementId();
            }
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('synergeticagency_gls')->__('Following orders could not be processed:').' '.implode(' / ',$incrementIds)
            );
        }
        if($direct) {
            if(count($labels)) {
                $connector = new SynergeticAgency_GlsConnector_Connector();
                if(count($labels) === 1) {
                    $pdf = array_shift($labels);
                } else {
                    $pdf = $connector->combineLabels($labels);
                    // optional enhancement: If you want to save the combined label in the filesstem,
                    // feel free to use i.e. the PHP function file_put_contents(http://php.net/manual/en/function.file-put-contents.php)
                }
                $this->_prepareDownloadResponse(
                    'combined_label_' .date('Y-m-d_His'). '.pdf',
                    $pdf,
                    'application/pdf'
                );
            } else {
                if(count($errorMessages)) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        implode('<br />',$errorMessages)
                    );
                }
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('synergeticagency_gls')->__('No labels could be printed')
                );
                $this->_redirectReferer();
            }
        } else {
            if(!$shipmentsCount) {
                // delete the job again;
                $job->delete();
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('synergeticagency_gls')->__('No labels could be printed')
                );
                $this->_redirectReferer();
            } else {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    sprintf(Mage::helper('synergeticagency_gls')->__('Successfully created mass print job with %s items'),$shipmentsCount)
                );
                $job->setQtyItemsOpen($shipmentsCount);
                $job->save();
                $this->_redirect('adminhtml/gls_shipment_job/index/');
            }
        }
    }

    /**
     * collect data for shipment and create shipment using GLS API Connector
     */
    public function printAction() {

        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "entered", Zend_Log::DEBUG );

        $glsShipmentId = $this->getRequest()->getParam('id');
        $glsShipment = Mage::getModel('synergeticagency_gls/shipment')->load($glsShipmentId);
        $glsShipment->setSandbox( Mage::getStoreConfig('gls/general/sandbox',$glsShipment->getStore()) );
        try {
            $labels = $glsShipment->getLabels();
        } catch(Exception $e) {
            if(!$glsShipment->getErrorMessage()) {
                $glsShipment->setErrorMessage($e->getMessage());
                $glsShipment->setErrorCode(SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_UNDEFINED);
                $glsShipment->save();
            }
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirectReferer();
            return null;
        }

        if(empty($labels)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synergeticagency_gls')->__('The label data with GLS-ConsignmentId %s obtained are not correct'),$glsShipment->getConsignementId() .
            "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_LABEL_INVALID));
            $this->_redirectReferer();
        }

        // combine labels, if shipment response has more than one label
        $labelHeaderData = false;
        if( count($labels) > 1 ){
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, count($labels) . " Labels in GLS response", Zend_Log::INFO );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "combine " . count($labels) . " GLS Labels into one file", Zend_Log::INFO );
            try {
                $labelHeaderData = $glsShipment->getConnector()->combineLabels($labels);
            } catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synergeticagency_gls')->__('The label data with GLS-ConsignmentId %s obtained are not correct'),$glsShipment->getConsignementId() .
                    "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_LABEL_INVALID));
                $this->_redirectReferer();
            }
        } else {
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "One label in GLS response", Zend_Log::INFO );
            $labelHeaderData = array_shift($labels);
        }

        $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "\$labelHeaderData=" . substr ( $labelHeaderData, 0, 25 ), Zend_Log::DEBUG );
        if( $labelHeaderData !== false ){
            $this->_prepareDownloadResponse(
                $glsShipment->getConsignmentId() . '.pdf',
                $labelHeaderData,
                'application/pdf'
            );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, "Label"  . $glsShipment->getConsignmentId() . ".pdf sent to header", Zend_Log::INFO );

        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                sprintf(Mage::helper('synergeticagency_gls')->__('Creation of the label with the GLS-ConsignmentId %s failed'), $glsShipment->getConsignmentId()) .
                "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_LABEL_INVALID)
            );
            $this->getGlsHelperLog()->log( __METHOD__, __LINE__, sprintf('Creation of the label with the GLS-ConsignmentId %s failed'), $glsShipment->getConsignmentId(), Zend_Log::ERR );
            $this->_redirectReferer();
            return null;
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
                "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_SHIPMENT_EDIT_IMPOSSIBLE)
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
                "<br />" . sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_LABEL_ALREADY_PRINTED)
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
            $this->getGlsHelperLog()->logException( $e, __METHOD__, __LINE__, Zend_Log::ERR, 'GLS Shipment could not be saved', SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_SHIPMENT_NOT_SAVED );
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

}
