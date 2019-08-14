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
* @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Gls
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Job
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Job extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * The Constructor initiates the blockGroup, the controller and the headerText of the used Mage_Adminhtml_Block_Widget_Grid_Container
     * The Connector also removes the button "add" from the used Mage_Adminhtml_Block_Widget_Grid_Container
     */
    public function __construct()
    {
        $this->_blockGroup = 'synergeticagency_gls';
        $this->_controller = 'adminhtml_sales_gls_shipment_job';
        $this->_headerText = Mage::helper('synergeticagency_gls')->__('GLS mass action list');
        parent::__construct();
        $this->_removeButton('add');
    }
}
