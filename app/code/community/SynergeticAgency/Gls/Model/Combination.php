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
* @package    SynergeticAgency\Gls\Model
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Model_Combination
 */
class SynergeticAgency_Gls_Model_Combination extends Mage_Core_Model_Abstract {

    /**
     * Gls products i. e.: business parcel
     * @var
     */
    private $_product;

    /**
     * Gls service i. e.: think green
     * @var
     */
    private $_services;

    /**
     * Initialize resource model
     *
     */
    protected function _construct() {
        $this->_init('synergeticagency_gls/combination');
    }

    /**
     * Loading products from GLS products configuration
     * @return SynergeticAgency_Gls_Model_Product
     */
    public function getProduct() {
        if(is_null($this->_product)) {
            $this->_product = Mage::getModel('synergeticagency_gls/product')->load($this->getData('product'));
        }
        return $this->_product;
    }

    /**
     * Loading services from GLS services configuration
     * @return SynergeticAgency_Gls_Model_Resource_Service_Collection
     */
    public function getServices() {
        $serviceIds = $this->getData('services');
        if(is_null($this->_services) && !empty($serviceIds)) {
            $this->_services = Mage::getModel('synergeticagency_gls/service')->getCollection()->setIdFilter($serviceIds)->load();
        }
        return $this->_services;
    }

    /**
     * Get the name of a GLS service or product
     * @return string
     */
    public function getName() {
        $label = '';
        $product = $this->getProduct();
        $services = $this->getServices();
        $servicesArray = array();
        if(!empty($services)) {
            foreach ($services as $service) {
                $servicesArray[] = Mage::helper('synergeticagency_gls')->__($service->getName());
            }
        }
        if($product && $product->getId()) {
            $label = Mage::helper('synergeticagency_gls')->__($product->getName());
        }
        if(count($servicesArray)) {
            $label .= ' + '.implode(' + ',$servicesArray);
        }
        return $label;
    }
}
