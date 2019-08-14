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
* @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Gls\Shipment
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Edit
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Edit extends Mage_Adminhtml_Block_Template {

    /**
     * The Constructor initiates the template for the used Mage_Adminhtml_Block_Template
     */
    public function _construct() {
        $this->setTemplate('gls/shipment/edit.phtml');
        parent::_construct();
    }

    /**
     * return the translated version for the header text
     * @return mixed
     */
    public function getHeaderText()
    {
        return Mage::helper('synergeticagency_gls')->__('Edit GLS Shipment');
    }

    /**
     * Return the HTML for the buttons used in the "edit shipment" template
     * @return string
     */
    public function getButtonsHtml()
    {
        $addSaveButtonData = array(
            'id'    => 'save',
            'label' => Mage::helper('adminhtml')->__('Save'),
            'onclick' => 'editForm.submit();',
            'class' => 'save',
        );
        $addBackButtonData = array(
            'id' => 'back',
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('adminhtml/sales_shipment/view', array(
                    'shipment_id'=>Mage::registry('current_shipment')->getId())) . '\')',
            'class'     => 'back',
        );

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($addBackButtonData)->toHtml();
        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setData($addSaveButtonData)->toHtml();

        return $html;
    }
}
