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
* @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Gls\Shipment\Edit\Form
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Edit_Form_Packages
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Edit_Form_Packages extends SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Gls_Packages {

    /**
     * Verify if shipment has already existing parcels
     * @return bool
     */
    public function hasParcels() {

        $hasParcels = false;

        if(count($this->getParcels()) > 0 ) {
            $hasParcels = true;
        }

        return $hasParcels;
    }

    /**
     * Returns existing parcels of the current shipment. Otherwise the return value is NULL
     *
     * @return mixed
     */
    public function getParcels() {
        return $this->_getGlsShipment()->getShipmentParcels();
    }

    /**
     * Returns SynergeticAgency_Gls_Model_Shipment object
     *
     * @return SynergeticAgency_Gls_Model_Shipment
     */
    private function _getGlsShipment() {
        return Mage::registry('gls_shipment');
    }
}
