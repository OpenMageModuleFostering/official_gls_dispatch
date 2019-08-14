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
 * Class SynergeticAgency_Gls_Model_Country
 */
class SynergeticAgency_Gls_Model_Country extends Mage_Core_Model_Abstract {

    /**
     * gls countries
     * @var
     */
    private $_options;

    /**
     * Initialize resource model
     *
     */
    protected function _construct() {
        $this->_init('synergeticagency_gls/country');
    }

    /**
     * get available combinations for country
     * @return array
     */
    public function getOptions() {
        $options = $this->getData('options');
        if(is_null($this->_options) && !empty($options)) {
            foreach($options as $option) {
                if(empty($option['combination'])) continue;

                $optionObject = new Varien_Object();
                $optionObject->setCombination(Mage::getModel('synergeticagency_gls/combination')->load($option['combination']));
                if(!empty($option['addon_services']) && is_array($option['addon_services'])) {
                    $optionObject->setAddonServices(Mage::getModel('synergeticagency_gls/service')->getCollection()->setIdFilter($option['addon_services'])->load());
                }
                $this->_options[] = $optionObject;
            }
        }
        return $this->_options;
    }

    /**
     * @param int $combinationId
     * @return null|SynergeticAgency_Gls_Model_Resource_Service_Collection
     */
    public function getAddonServicesByCombination($combinationId) {
        $options = $this->getOptions();
        if(empty($options)) return null;
        foreach($options as $option) {
            if($option->getCombination()->getId() == $combinationId) {
                return $option->getAddonServices();
            }
        }
        return null;
    }
}
