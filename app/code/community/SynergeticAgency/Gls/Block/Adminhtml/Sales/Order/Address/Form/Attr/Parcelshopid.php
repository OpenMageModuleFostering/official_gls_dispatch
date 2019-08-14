<?php
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Address_Form_Attr_Parcelshopid
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface {
    public function __construct()
    {
        $this->setTemplate('gls/sales/order/address/form/attr/parcelshopid.phtml'); //set a template
    }
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        return $this->toHtml();
    }
}