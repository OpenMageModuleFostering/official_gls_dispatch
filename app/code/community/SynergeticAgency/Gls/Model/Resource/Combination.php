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
 * @package    SynergeticAgency\Gls\Model\Resource
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Model_Resource_Combination
 */
class SynergeticAgency_Gls_Model_Resource_Combination extends Mage_Core_Model_Resource_Abstract {

    /**
     * Get path to (gls service)combination config file
     * @var
     */
    private $path;

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->path = Mage::getModuleDir('','SynergeticAgency_Gls').DS.'config'.DS.'combinations.php';
        return $this;
    }

    /**
     * Retrieve connection for read data
     */
    protected function _getReadAdapter()
    {
        return false;
    }

    /**
     * Retrieve connection for write data
     */
    protected function _getWriteAdapter()
    {
        return false;
    }

    /**
     * Load GLS combination configuration
     * @param SynergeticAgency_Gls_Model_Combination $object
     * @param $value
     * @param null $field
     * @return $this
     */
    public function load(SynergeticAgency_Gls_Model_Combination $object, $value, $field = null)
    {
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }

        if (!is_null($value)) {
            $data = null;
            $combinations = new Zend_Config(include($this->path));
            foreach($combinations->combinations as $combination) {
                if($combination->get($field, null) == $value ) {
                    $data = $combination;
                    break;
                }
            }

            if ($data) {
                $object->setData($data->toArray());
            }
        }

        return $this;
    }

    /**
     * Get the name of the field (array index) holding the combination id
     * @return string
     */
    public function getIdFieldName() {
        return 'id';
    }

    /**
     * Get path to combination config file
     * @return mixed
     */
    public function getPath() {
        return $this->path;
    }

}