<?php
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Template_Grid_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Assign custom value to Mage_Adminhtml_Block_Sales_Order_Grid real_order_id column
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

    /**
     * Assemble custom value for Mage_Adminhtml_Block_Sales_Order_Grid real_order_id column
     * @param Varien_Object $row
     * @return string
     */
    protected function _getValue(Varien_Object $row)
    {
        $glsLogo = '';
        $orderId = $row->getData($this->getColumn()->getIndex());
        $logoWwwPath = Mage::getDesign()->getSkinUrl('images/gls/logo_thumb.png');
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $glsShipment = Mage::getModel('synergeticagency_gls/shipment')->load($order->getId(),'order_id');

        if(strstr($order->getShippingMethod(),'synergeticagency_gls') || $glsShipment->getId()){
            $glsLogo = ' <img src="'.$logoWwwPath.'" alt="'.$this->__('GLS Shipment').'" title="'.$this->__('GLS Shipment').'" />';
        }

        return $orderId.$glsLogo;
    }
}