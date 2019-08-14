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
* @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Gls\Shipment\Grid\Renderer
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Grid_Renderer_Qtyupdate
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Grid_Renderer_Qtyupdate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders the GLS shipment grid HTML for the given GLS shipment row
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if($row->getPrinted()) {
            $html = '<span>' . $this->__('Already printed'). '</span>';
        } elseif($row->getJobId()) {
            $html = '<span>' . $this->__('In mass action'). '</span>';
        } elseif(Mage::helper('synergeticagency_gls/validate')->isCashService($row->getMagentoShipment())) {
            $html = '<span>' . $this->__('Not available with cash on delivery').'</span>';
        } else {
            $disabledDecrease = '';
            $disabledIncrease = '';
            if($row->getShipmentParcels()->count() == SynergeticAgency_Gls_Model_Shipment::GLS_SHIPMENT_MIN_PARCELS) {
                $disabledDecrease = ' disabled';
            }
            if($row->getShipmentParcels()->count() >= SynergeticAgency_Gls_Model_Shipment::GLS_SHIPMENT_MAX_PARCELS) {
                $disabledIncrease = ' disabled';
            }
            $html = '<div id="qty-parcels-wrap-'.$row->getId().'" class="qty-parcels-wrap" style="white-space: nowrap;">
                        <button class="qty-parcels-decrease qty-parcels-change'.$disabledDecrease.'" data-id="'.$row->getId().'" data-dir="-">-</button>&nbsp;
                        <input class="input-text qty-parcels-qty" autocomplete="off" value="'.$row->getShipmentParcels()->count().'" type="text" style="width: 50px;" name="qty_parcels['.$row->getId().']">&nbsp;
                        <button class="qty-parcels-increase qty-parcels-change'.$disabledIncrease.'" data-id="'.$row->getId().'" data-dir="+">+</button>&nbsp;&nbsp;
                        <button class="qty-parcels-submit" data-qty="'.$row->getShipmentParcels()->count().'" data-id="'.$row->getId().'"> '.$this->__('Update').' </button>
                    </div>';
            $html .= '<span id="qty-parcels-printed-'.$row->getId().'" style="display:none;">' . $this->__('Already printed'). '</span>';
        }
        // the anchor is added to disable the click action for the checkbox
        return '<a style="display: none" href=""></a>'.$html;
    }
}
