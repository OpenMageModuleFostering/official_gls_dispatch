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
 * Class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Job_Grid
 */
class SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Job_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * The Constructor initiates the id, the defaultSort and the defaultDir of the used Mage_Adminhtml_Block_Widget_Grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('synergeticagency_gls_shipment_job_grid');
        $this->setDefaultSort('gls_shipment_job_id');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare grid collection object
     *
     * @return SynergeticAgency_Gls_Block_Adminhtml_Sales_Gls_Shipment_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('synergeticagency_gls/shipment_job_collection');
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

        $this->addColumn('gls_shipment_job_id', array(
            'header'            => $helper->__('GLS Mass print ID'),
            'index'             => 'gls_shipment_job_id',
            'width'             => '100px'
        ));

        $this->addColumn('job_status', array(
            'header'            => $helper->__('Process status'),
            'renderer'          => 'synergeticagency_gls/adminhtml_sales_gls_shipment_job_grid_renderer_jobstatus',
            'index'             => 'job_status',
            'sortable'          => false,
            'filter'            => false,
        ));

        $this->addColumn('error_messages', array(
            'header'            => $helper->__('Error messages'),
            'index'             => 'error_messages',
            'column_css_class'  => 'job_error_messages',
            'sortable'          => false,
            'filter'            => false,
        ));

        $this->addColumn('error_items', array(
            'header'            => $helper->__('Error shipment items'),
            'renderer'          => 'synergeticagency_gls/adminhtml_sales_gls_shipment_job_grid_renderer_erroritems',
            'index'             => 'error_items',
            'column_css_class'  => 'job_error_items',
            'sortable'          => false,
            'filter'            => false,
        ));

        $this->addColumn('completed', array(
            'header'            => $helper->__('Completed'),
            'index'             => 'completed',
            'type'              => 'options',
            'width'             => '50px',
            'column_css_class'  => 'job_completed',
            'options'           => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray()
        ));

        $this->addColumn('printed', array(
            'header'            => $helper->__('Printed'),
            'index'             => 'printed',
            'type'              => 'options',
            'width'             => '50px',
            'column_css_class'  => 'job_printed',
            'options'           => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray()
        ));

        $this->addColumn('created_at', array(
            'header'            => Mage::helper('sales')->__('Date Created'),
            'index'             => 'created_at',
            'type'              => 'datetime',
            'width'             => '100px'
        ));

        $this->addColumn('action', array(
            'header'            => Mage::helper('sales')->__('Action'),
            'align'             => 'left',
            'column_css_class'  => 'job_action',
            'renderer'          => 'synergeticagency_gls/adminhtml_sales_gls_shipment_job_grid_renderer_action',
            'filter'            => false,
            'sortable'          => false,
            'width'             => '150px',
        ));

        return parent::_prepareColumns();
    }

    /**
     * @param $row Varien_Object
     * @return string
     */
    public function getRowUrl($row) {
        return '';
    }
}
