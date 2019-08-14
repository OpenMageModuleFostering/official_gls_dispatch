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
 * @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Order\Shipment\Create\Gls
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Gls_Packages
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Gls_Packages extends Mage_Adminhtml_Block_Widget {
    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gls/sales/order/shipment/create/gls/packages.phtml');
    }

    /**
     * Get final order amount
     * @return string
     */
    public function getCashAmount() {
        return $this->getStore()->roundPrice($this->_getCurrentShipment()->getOrder()->getGrandTotal());
    }

    /**
     * Get order currency
     * @return string
     */
    public function getCurrency() {
        return $this->_getCurrentShipment()->getOrder()->getBaseCurrency()->getCurrencyCode();
    }

    /**
     * Get default weight.
     * @return float
     */
    public function getWeight()
    {
        $weight = 0;
        $items = $this->_getCurrentShipment()->getAllItems();
        if(count($items)) {
            foreach($items as $item) {
                $weight += $item->getWeight() * $item->getQty();
            }
        }
        if(empty($weight)) {
            $weight = $this->getDefaultWeight();
        }
        return $weight;
    }

    /**
     * Get default weight.
     * @return float
     */
    public function getDefaultWeight()
    {
       return Mage::getStoreConfig('gls/shipment/weight', $this->getStore());
    }

    /**
     * Verify if payment is gls cash on delivery
     * @return bool
     */
    public function isCashService() {
        $shipment = $this->_getCurrentShipment();
        if($shipment) {
            if($shipment->getOrder()->getPayment()->getMethodInstance() instanceof SynergeticAgency_Gls_Model_Glscashondelivery) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get shipments store
     * @return Mage_Core_Model_Store
     */
    public function getStore() {
        return $this->_getCurrentShipment()->getStore();
    }

    /**
     * Getting the current watched shipment from mage registry
     * @return Mage_Sales_Model_Order_Shipment
     */
    private function _getCurrentShipment() {
        return Mage::registry('current_shipment');
    }
}