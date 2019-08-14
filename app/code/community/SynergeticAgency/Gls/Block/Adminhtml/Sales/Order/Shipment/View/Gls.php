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
 * @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Order\Shipment\View
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_View_Gls
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_View_Gls extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Form {


    /**
     * The Constructor initiates the template for the used Mage_Adminhtml_Block_Sales_Order_Shipment_View_Form
     */
    public function _construct()
    {
        $this->setTemplate('gls/sales/order/shipment/view/gls.phtml');
        parent::_construct();
    }


    /**
     * Returns the shipment model by the current shipment id
     *
     * @return SynergeticAgency_Gls_Model_Shipment
     */
    public function getGlsShipment()
    {
        return Mage::getModel('synergeticagency_gls/shipment')->load( $this->getShipment()->getId(), 'shipment_id');
    }

    /**
     * Returns the orders currency code of the current shipment
     *
     * @return string
     */
    public function getCurrency() {
        return $this->getShipment()->getOrder()->getBaseCurrency()->getCurrencyCode();
    }

    /**
     * Returns a formatted price of the current shipment in respect of the orders store configuration
     *
     * @param $price
     * @return mixed
     */
    public function formatPrice($price) {
        return $this->getShipment()->getStore()->formatPrice($this->getShipment()->getStore()->roundPrice($price));
    }
}
