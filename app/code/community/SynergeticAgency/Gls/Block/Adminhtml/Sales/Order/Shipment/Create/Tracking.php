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
 * @package    SynergeticAgency\Gls\Block\Adminhtml\Sales\Order\Shipment\Create
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Tracking
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Order_Shipment_Create_Tracking extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Tracking {

    /**
     * Extend tracking block to attach gls form.
     *
     * @return string
     */
    public function _toHtml()
    {
        $store = $this->getShipment()->getStore();
        if (!Mage::getStoreConfig('gls/general/active' , $store)) {
            return parent::_toHtml();
        }

        /** @var Mage_Core_Block_Abstract $block */
        $block = $this->getLayout()->createBlock('synergeticagency_gls/adminhtml_sales_order_shipment_create_gls','gls_parcel_form');
        $block->setChild('packages', $this->getLayout()->createBlock(
            'synergeticagency_gls/adminhtml_sales_order_shipment_create_gls_packages',
            'packages_form'));
        return parent::_toHtml() . $block->_toHtml();
    }

}