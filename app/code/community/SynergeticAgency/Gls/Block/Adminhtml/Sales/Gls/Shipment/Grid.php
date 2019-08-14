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
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Grid
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * The Constructor initiates the id, the defaultSort and the defaultDir of the used Mage_Adminhtml_Block_Widget_Grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('synergeticagency_gls_shipment_grid');
        $this->setDefaultSort('gls_shipment_id');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare grid collection object
     *
     * @return SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('synergeticagency_gls/shipment_collection');
        $collection->getSelect()->joinLeft(
        array(
            'shipment' => $collection->getTable('sales/shipment_grid')
        ),
        'main_table.shipment_id=shipment.entity_id',
        array(
            'shipment_increment_id' => 'increment_id',
            'created_at'         => 'created_at'
        )
        )->joinLeft(
            array(
                'order' => $collection->getTable('sales/order_grid')
            ),
            'shipment.order_id=order.entity_id',
            array(
                'order_id'           => 'entity_id',
                'order_increment_id' => 'increment_id',
                'shipping_name'      => 'shipping_name',
                'order_date'         => 'created_at'
            )
        );

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('synergeticagency_gls');

        $this->addColumn('gls_shipment_id', array(
            'header' => $helper->__('GLS Shipment ID'),
            'index'  => 'gls_shipment_id'
        ));

        $this->addColumn('consignment_id', array(
            'header' => $helper->__('GLS Consignment ID'),
            'index'  => 'consignment_id'
        ));

        $this->addColumn('order', array(
            'header'    => Mage::helper('sales')->__('Order'),
            'align'     => 'left',
            'index'     => 'order.increment_id',
            'renderer'  => 'synergeticagency_gls/adminhtml_sales_gls_shipment_grid_renderer_order'
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('shipment', array(
            'header'    => Mage::helper('sales')->__('Shipments'),
            'align'     => 'left',
            'index'     => 'shipment.increment_id',
            'renderer'  => 'synergeticagency_gls/adminhtml_sales_gls_shipment_grid_renderer_shipment',
        ));
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Date Shipped'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('pdf', array(
            'header'    => Mage::helper('sales')->__('Action'),
            'align'     => 'left',
            'renderer'  => 'synergeticagency_gls/adminhtml_sales_gls_shipment_grid_renderer_pdf',
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }
}
