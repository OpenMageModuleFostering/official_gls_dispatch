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
* @package    SynergeticAgency\Gls\Model\Resource\Service
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Model_Resource_Service_Collection
 */
class SynergeticAgency_Gls_Model_Resource_Service_Collection extends Varien_Data_Collection {

    /**
     * path to service config file
     * @var
     */
    private $path;

    /**
     * service id to get service by id
     * @var
     */
    private $idFilter;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->path = Mage::getResourceModel('synergeticagency_gls/service')->getPath();
        parent::__construct();
    }

    /**
     * Load GLS services configuration
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items)) {
            $services = new Zend_Config(include($this->path));

            foreach ($services->services as $service) {
                $add=true;

                $idFilter = $this->getIdFilter();
                if(!empty($idFilter)) {
                    if(!in_array($service->id,$idFilter)) {
                        $add = false;
                    }
                }

                foreach($this->_filters as $filter) {
                    if(isset($service->{$filter['field']}) && $service->{$filter['field']} != $filter['value']) {
                        $add = false;
                    }
                }
                if($add) {
                    $item = Mage::getModel('synergeticagency_gls/service');
                    $item->setData($service->toArray());
                    $this->addItem($item);
                }
            }
        }
        return $this;
    }

    /**
     * setter for id filter value
     * @param array $ids
     * @return $this
     */
    public function setIdFilter(array $ids) {
        if(count($ids)) {
            $this->idFilter = $ids;
        }
        return $this;
    }

    /**
     * getter for id filter value
     * @return mixed
     */
    public function getIdFilter() {
        return $this->idFilter;
    }
}
