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
* @package    SynergeticAgency\Gls\Model\Resource\Combination
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Model_Resource_Combination_Collection
 */
class SynergeticAgency_Gls_Model_Resource_Combination_Collection extends Varien_Data_Collection {

    /**
     * path to combination config file
     * @var
     */
    private $path;

    /**
     * constructor - set path and parent construct
     */
    public function __construct()
    {
        $this->path = Mage::getResourceModel('synergeticagency_gls/combination')->getPath();
        parent::__construct();
    }
    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items)) {
            $combinations = new Zend_Config(include($this->path));

            foreach ($combinations->combinations as $combination) {
                $add = true;

                foreach($this->_filters as $filter) {
                    $product = Mage::getModel('synergeticagency_gls/product')->load($combination->product);
                    if($filter['field'] == 'national' && $product->getNational() != $filter['value']) {
                        $add = false;
                    }
                }
                if($add) {
                    $item = Mage::getModel('synergeticagency_gls/combination');
                    $item->setData($combination->toArray());
                    $this->addItem($item);
                }
            }
        }
        return $this;
    }
}
