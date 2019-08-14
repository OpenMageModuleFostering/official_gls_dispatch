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
* @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Gls\Shipment\Edit
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Edit_Form
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Edit_Form extends SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Gls {

    /**
     * The Constructor here call the parent constructor of SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Gls
     */
    public function _construct() {
        parent::_construct();
    }

    /**
     * Returns true, if current GLS shipment label should be delivered with return label
     * Returns false, if current GLS shipment label should be delivered without return label
     *
     * @return bool
     */
    public function getReturnLabel() {
        return $this->_getGlsShipment()->getReturnLabel() === '1';
    }


    /**
     * Returns true, if current GLS shipment combination id equals to the given parameter
     * Returns true, if current GLS shipment combination id not equals to the given parameter
     *
     * @param int $combinationId
     * @return bool
     */
    public function isCombinationSelected($combinationId) {
        return $combinationId == $this->_getGlsShipment()->getCombinationId();
    }

    /**
     * Retrurns true, if given GLS service id is part of the current GLS shipment
     * Retrurns false, if given GLS service id is not part of the current GLS shipment
     *
     * @param int $serviceId
     * @return bool
     */
    public function isServiceSelected($serviceId) {
        $services = $this->_getGlsShipment()->getGlsServices();
        if(empty($services)) {
            return false;
        }
        $services = explode(',',$services);
        return in_array($serviceId,$services);
    }

    /**
     * Returns the "d.m.Y" formatted date string representation of the current GLS shipping date
     *
     * @return bool|string
     */
    public function getShippingDate() {
        return date('d.m.Y', strtotime($this->_getGlsShipment()->getShippingDate()));
    }

    /**
     * Returns the SynergeticAgency_Gls_Model_Shipment object
     *
     * @return SynergeticAgency_Gls_Model_Shipment
     */
    private function _getGlsShipment() {
        return Mage::registry('gls_shipment');
    }

}
