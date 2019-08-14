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
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Job_Grid_Renderer_Erroritems
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Job_Grid_Renderer_Erroritems extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{

    /**
     * Renders the job status
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $ret = '';
        $errorItems = $row->getErrorItems();
        if(!empty($errorItems)) {
            $errorItems = json_decode($errorItems);
            if(count($errorItems)) {
                $retArray = array();
                foreach($errorItems as $errorItem) {
                    $retArray[] = sprintf('<a href="%s">%s</a>', Mage::helper('adminhtml')->getUrl('adminhtml/sales_shipment/view', array('shipment_id' => $errorItem->shipment_id)), $errorItem->shipment_increment_id);
                }

                $ret = implode(', ',$retArray);
            }
        }
        return '<span>'.$ret.'</span>';
    }
}