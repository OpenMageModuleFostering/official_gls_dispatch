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
 * Class SynergeticAgency_Gls_Model_Observer
 */
class SynergeticAgency_Gls_Model_Observer {


    /**
     * Adding some javascript to add GLS Logos for shipment methods if it's enabled in magento config
     * @param Varien_Event_Observer $observer
     */

    public function setGlsShippingLogos(Varien_Event_Observer $observer) {

        if($observer->getBlock() instanceof Mage_Checkout_Block_Onepage){
            $block              = $observer->getBlock();
            $transport          = $observer->getTransport();
            $fileName           = $block->getTemplateFile();

            if($fileName) {
                $store = Mage::app()->getStore();

                // Getting full carrier config for for module synergeticagency_gls
                $config = Mage::getStoreConfig(
                    'carriers/synergeticagency_gls',
                    $store
                );

                // Checking if GLS module is active at all
                if ($config['active'] == '1') {
                    $logos = array();
                    // Getting Store Country to build logo path
                    $country = Mage::getStoreConfig('general/country/default');
                    $countryCode = strtolower(Mage::getModel('directory/country')->loadByCode($country)->getIso2Code());

                    // Getting logo configuration
                    // Careful with config array key naming in case new delivery options are added - stick to convention
                    $logoConfigKeys = preg_filter('/^show_logo_(.*)/', '$1', array_keys($config));
                    foreach ($logoConfigKeys AS $logoKey) {

                        // Just render javascript for logo if shipment option is active and show_logo_* is active
                        if ($config['active_' . $logoKey] === '1' && $config['show_logo_' . $logoKey] === '1') {
                            //Getting Store Country -> Logo per country and available shipping method
                            //need also a default in case country logo not available
                            $logoWwwPath = Mage::getDesign()->getSkinUrl('images/gls/logo/' . $countryCode . '/' . $logoKey . '.png');
                            $logoLocalPath = Mage::getSingleton('core/design_package')->getFilename('images/gls/logo/' . $countryCode . '/' . $logoKey . '.png', array('_type' => 'skin', '_default' => false));
                            if (!file_exists($logoLocalPath)) {
                                $logoWwwPath = Mage::getDesign()->getSkinUrl('images/gls/logo/default/' . $logoKey . '.png');
                            }
                            $logos[$logoKey] = $logoWwwPath;
                        }
                    }
                    // Create a new block for JavaScript and set Logo Data
                    $layout = Mage::app()->getLayout();
                    $jsBlock = $layout->createBlock(
                        'Mage_Core_Block_Template',
                        'synergeticagency_logo_js',
                        array(
                            'template' => 'gls/logoJs.phtml'
                        )
                    )->setLogos($logos);

                    // Append Javascript block to Mage_Checkout_Block_Onepage
                    $html = $transport->getHtml();
                    $html = $html . $jsBlock->toHtml();
                    $transport->setHtml($html);
                }
            }
        }
    }

    /**
     * Adding some js to adminhtml configuration shipping methods to show/hide the 'show errormessage' field in dependency to show shipping also if not possible
     */
    public function setCountryModel()
    {

        $layout = Mage::app()->getLayout();

        $layout->getBlock('js')->append($layout
            ->createBlock('adminhtml/template')
            ->setTemplate('gls/system/shipping/gls_applicable_country.phtml'));
    }

    /**
     * Saves additional gls data to shipment
     *
     * @return null|void
     */
    public function saveShipmentData()
    {

        if(Mage::registry('gls_shipment_error') === true) {
            return null;
        }

        $shipment = Mage::registry('current_shipment');
        // Return if current shipment is null.
        if (is_null($shipment)):
            return null;
        endif;

        $helper = Mage::helper('synergeticagency_gls');

        $store = $shipment->getStore();
        // Return if global gls config is disabled.
        if (!Mage::getStoreConfig('gls/general/active' , $store)) {
            return null;
        }

        // get post data
        $data = Mage::app()->getRequest()->getPost();
        if(!isset($data['ship_with_gls']) || $data['ship_with_gls'] !== '1') {
            // shipment with gls is not selected
            return null;
        }

        // Return if validation fails.
        if ( false === $this->_validateGlsShipmentData($data,$shipment) ){
            return null;
        }

        $glsModel = Mage::getModel('synergeticagency_gls/gls');
        $glsModel->saveGlsShipment($data, $shipment);
        // set order status
        $helper->setOrderStatus($shipment->getOrder(),$helper->__('GLS shipment successfully saved'));

    }

    /**
     * sets the gls order state on invoice save
     * @param $observer
     */
    public function saveGlsOrderStatus($observer) {
        $invoice = Mage::registry('current_invoice');
        $order = $invoice->getOrder();
        $glsShipments = Mage::getModel('synergeticagency_gls/shipment')->getCollection();
        $glsShipments->addFieldToFilter('order_id',array('eq' => $order->getId()));
        if($glsShipments->count()) {
            $helper = Mage::helper('synergeticagency_gls');
            $helper->setOrderStatus($order,$helper->__('Order with GLS-Shipment'));
        }
    }


    /**
     * Server side validation of gls shipment data in sales_order view
     * This validation should never be triggered due to client side validation
     * @param $data
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return bool
     */
    public function _validateGlsShipmentData($data,$shipment) {

        if (empty($data['shipment']['packages']) ) {
            return false;
        }

        if ( empty($data['shipment']['gls']) ) {
            return false;
        }

        $errorMessages = Mage::getModel('synergeticagency_gls/gls')->validateGlsShipmentData($data,$shipment);
        if(true !== $errorMessages) {
            return false;
        }

        return $errorMessages;
    }


    /**
     * Checks GLS shipment data and throws exception if not valid
     * saving of shipment is not executed then and data can be corrected by the user
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkGlsShipmentData(Varien_Event_Observer $observer)
    {
        $data = Mage::app()->getRequest()->getPost();
        $shipment = $observer->getEvent()->getShipment();
        //at the moment this method is also getting called when label is printed
        //check if post data...
        if(isset($data['shipment']['gls']) && isset($data['shipment']['gls']['ship_with_gls']) && $data['shipment']['gls']['ship_with_gls'] == '1') {
            $check = Mage::getModel('synergeticagency_gls/gls')->validateGlsShipmentData($data,$shipment);
            if ($check !== true) {
                Mage::register('gls_shipment_error', true);
                Mage::throwException($check);
            }
        }
    }

    /**
     * Adding a button to print a gls shipment label to sales_order view
     * @param Varien_Event_Observer $observer
     */
    public function addGlsPdfButtonToOrder(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            $orderId = $block->getOrderId();
            $glsShipmentCollection = Mage::getModel('synergeticagency_gls/shipment')
                ->getCollection()
                ->addFieldToFilter('order_id',$orderId)
                ->addFieldToFilter('printed','0')
                ->addFieldToFilter('job_id',array('null' => true))
                ->setOrder('gls_shipment_id');
            $hasUnprintedGlsShipments = $glsShipmentCollection->count();
            if($hasUnprintedGlsShipments) {
                $glsShipment = $glsShipmentCollection->getFirstItem();
                $block->addButton('printLastLabel',
                    array(
                        'label' => Mage::helper('synergeticagency_gls')->__('Print GLS Label'),
                        'onclick' => "setLocation('{$block->getUrl('adminhtml/gls_shipment/print', array('id' => $glsShipment->getId()))}');this.disabled=true;this.className += ' disabled';",
                        'class' => 'save'
                    ), 0, 1
                );
            }
        }
    }

    /**
     * Observe Mage_Adminhtml_Block_Sales_Order_Grid to add GLS logo
     * @param Varien_Event_Observer $observer
     */

    public function beforeBlockToHtml(Varien_Event_Observer $observer) {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            $this->_modifyOrderGrid($block);
        }
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Address_Form) {
            $this->_modifyParcelShopId($block);
        }
    }

    /**
     * @param $observer
     * @throws Exception
     */
    public function addMassActionToOrderGrid($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction && $block->getRequest()->getControllerName() == 'sales_order')
        {
            $block->addItem('createGlsShipment', array(
                'label' => Mage::helper('synergeticagency_gls')->__('Create GLS shipments'),
                'url' => Mage::app()->getStore()->getUrl('adminhtml/gls_shipment/masscreate'),
            ));

            $block->addItem('printGlsLabels', array(
                'label' => Mage::helper('synergeticagency_gls')->__('Print GLS labels'),
                'url' => Mage::app()->getStore()->getUrl('adminhtml/gls_shipment/massprint'),
            ));
        }
    }


    /**
     * @param Mage_Adminhtml_Block_Sales_Order_Grid $grid
     * @throws Exception
     */
    protected function _modifyOrderGrid(Mage_Adminhtml_Block_Sales_Order_Grid $grid) {
        $grid->addColumn('real_order_id', array(
            'header' => Mage::helper('core')->__('Order #'),
            'align' => 'left',
            'index' => 'increment_id',
            'width'     => '100',
            'renderer' => 'SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Template_Grid_Renderer_Order'
        ));

    }

    /**
     * @param Mage_Adminhtml_Block_Sales_Order_Address_Form $observer
     * @throws Exception
     */
    protected function _modifyParcelShopId(Mage_Adminhtml_Block_Sales_Order_Address_Form $observer) {
        $form = $observer->getForm();
        $customValues = $form->getElement('parcelshop_id');
        if ($customValues) {
            $customValues->setRenderer(
                Mage::app()->getLayout()->createBlock('SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Address_Form_Attr_Parcelshopid')
            ); //set a custom renderer to your attribute
        }



    }

}