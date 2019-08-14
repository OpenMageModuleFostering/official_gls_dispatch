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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category   SynergetigAgency
 * @package    SynergeticAgency\Gls\Model\Shipment
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GLS shipment job model
 *
 * @category    SynergeticAgency
 * @package     SynergeticAgency_Gls
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_Gls_Model_Shipment_Job extends Mage_Core_Model_Abstract {

    const GLS_SHIPMENT_JOB_LABEL_PDF_PREFIX_JOB = "gls_combined_";

    private $_labelTempPath;

    /**
     * This Constructor initiates synergeticagency_gls/shipment_job using the magic _init function of Mage_Core_Model_Abstract
     */
    protected function _construct() {
        $this->_init('synergeticagency_gls/shipment_job');
        $this->setLabelTempPath();
    }

    /**
     * @return SynergeticAgency_Gls_Model_Resource_Shipment_Collection
     */
    public function getShipments() {
        $glsShipmentCollection = Mage::getModel('synergeticagency_gls/shipment')->getCollection();
        $glsShipmentCollection
            ->addFieldToFilter( 'job_id', array('eq' => $this->getGlsShipmentJobId()) )
            ->addOrder('gls_shipment_id',$glsShipmentCollection::SORT_ORDER_ASC)
            ->load();
        return $glsShipmentCollection;
    }

    /**
     * @return SynergeticAgency_Gls_Model_Resource_Shipment_Collection
     */
    public function getUnprintedShipments() {
        $glsShipmentCollection = Mage::getModel('synergeticagency_gls/shipment')->getCollection();
        $glsShipmentCollection
            ->addFieldToFilter( 'job_id', array('eq' => $this->getGlsShipmentJobId()) )
            ->addFieldToFilter('printed', array('neq' => '1'))
            ->addOrder('gls_shipment_id',$glsShipmentCollection::SORT_ORDER_ASC)
            ->load();
        return $glsShipmentCollection;
    }

    /**
     * @return mixed
     */
    public function getLabelTempPath()
    {
        return $this->_labelTempPath;
    }

    /**
     * @param bool $labelTempPath
     * @return $this
     */
    public function setLabelTempPath( $labelTempPath = false )
    {
        if( $labelTempPath !== false && is_dir($labelTempPath) && is_writable($labelTempPath) ){
            $this->_labelTempPath = $labelTempPath;
        } else {
            $this->_labelTempPath = Mage::getBaseDir('var').DS."tmp".DS."gls";
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus() {
        $jobQty = intval($this->getQtyItemsOpen()) + intval($this->getQtyItemsSuccessful()) + intval($this->getQtyItemsError());
        $itemsSuccessful = intval($this->getQtyItemsSuccessful());
        $itemsError = intval($this->getQtyItemsError());
        $ret =  sprintf(Mage::helper('synergeticagency_gls')->__('%s of %s GLS shipments successfully processed.'), $itemsSuccessful, $jobQty);
        if($itemsError) {
            $ret .= '<br />'.sprintf(Mage::helper('synergeticagency_gls')->__('%s shipments could not be propcessed. Please check them manually.'), $itemsError);
        }
        return $ret;
    }
}