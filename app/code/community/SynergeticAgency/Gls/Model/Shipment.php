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
 * @package    SynergeticAgency\Gls\Model
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GLS shipment model
 *
 * @category    SynergeticAgency
 * @package     SynergeticAgency_Gls
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_Gls_Model_Shipment extends Mage_Core_Model_Abstract {

    /**
     * Shipments target address
     * @var
     */
    private $_shipmentAddress;

    /**
     * Shipments parcels
     * @var
     */
    private $_shipmentParcels;

    /**
     * Initialize resource model
     *
     */
    protected function _construct() {
        $this->_init('synergeticagency_gls/shipment');
    }

    /**
     * Get the shipment address from GLS Model by id (vs. loading shipment address from Magento Model)
     * @return mixed
     */
    public function getShipmentAddress() {
        if(is_null($this->_shipmentAddress) && $this->getId()) {
            $shipmentAddress = Mage::getModel('synergeticagency_gls/shipment_address')->loadByGlsShipmentId($this->getId());
            if($shipmentAddress && $shipmentAddress->getId()) {
                $this->_shipmentAddress = $shipmentAddress;
            }
        }
        return $this->_shipmentAddress;
    }

    /**
     * Get parcels for a shipment by shipment id)
     * @return mixed
     */
    public function getShipmentParcels() {
        if(is_null($this->_shipmentParcels) && $this->getId()) {
            $shipmentParcels = Mage::getModel('synergeticagency_gls/shipment_parcel')->getCollection()->addFieldToFilter('gls_shipment_id',$this->getId())->load();
            if($shipmentParcels && count($shipmentParcels)) {
                $this->_shipmentParcels = $shipmentParcels;
            }
        }
        return $this->_shipmentParcels;
    }

    /**
     * Get shipments GLS (service)combination
     * @return SynergeticAgency_Gls_Model_Combination
     */
    public function getCombination() {
        $combinationModel = Mage::getModel('synergeticagency_gls/combination');
        $combinationId = $this->getCombinationId();
        if($combinationId) {
            $combinationModel->load($combinationId);
        }
        return $combinationModel;
    }

    /**
     * Get shipments additional GLS services
     * @param bool $addonOnly
     * @return SynergeticAgency_Gls_Model_Resource_Service_Collection
     */
    public function getServices($addonOnly=false) {
        $serviceCollection = Mage::getModel('synergeticagency_gls/service')->getCollection();
        $serviceIds = $this->getGlsServices();
        if( $serviceIds === NULL ){
            $serviceIds = "";
        }

        if( !empty($serviceIds) ) {
            $serviceIds = explode(',',$serviceIds);
            $serviceCollection->setIdFilter($serviceIds);
            if($addonOnly) {
                $serviceCollection->addFilter('is_addon',true);
            }
            $serviceCollection->load();
            return $serviceCollection;
        }
        return array();
    }
}