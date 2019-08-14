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
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Grid_Renderer_Pdf
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Grid_Renderer_Pdf extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders the GLS shipment grid HTML for the given GLS shipment PDF row
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if($row->getPrinted()) {
            $html = '<span>'.$this->__('Printed').'</span>';

            $html = sprintf('<button onclick="setLocation(\'%s\');this.disabled=true;this.className += \' disabled\'" disabled="" class=" disabled">%s</button>', $this->getUrl('*/gls_shipment/print', array('id' => $row->getId())), $this->__('Printed'));


        } else {
            $html = sprintf('<button onclick="setLocation(\'%s\');this.disabled=true;this.className += \' disabled\'">%s</button>', $this->getUrl('*/gls_shipment/print', array('id' => $row->getId())), $this->__('Download PDF'));
        }
        return $html;
    }
}
