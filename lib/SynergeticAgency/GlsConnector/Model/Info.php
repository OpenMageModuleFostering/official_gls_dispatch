<?php
/**
 * Magento
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
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Model
 * @copyright  Copyright (c) 2006-2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with archives
 *
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Model
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_GlsConnector_Model_Info
{

    /**
     * @var	array	the info object converted into an array i.e. to generate correct GLS-JSON
     */
    private $glsArray;

    /**
     * @var	string	$name	The required name for the info
     * 						This is a request payload entity
     */
    private $name;

    /**
     * @var	string	$value	The required value for the info
     * 						This is a request payload entity
     */
    private $value;

    /**
     *
     */
    function __construct() {
        #echo "\n\nEntering " . __METHOD__ . " @ line " . __LINE__ . ":";
        #echo "\nLeave " . __METHOD__ . " @ line " . __LINE__ . ":";
    }


    /**
     * @return array
     */
    private function toGlsArray() {

        $returnValue = array();

        if( $this->getName() !== NULL )     $returnValue['name'] =  (String)$this->getName();
        if( $this->getValue() !== NULL )    $returnValue['value'] = (String)$this->getValue();

        $this->setGlsArray( $returnValue );
    }

    /**
     * @return array
     */
    public function getConstants() {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * @return mixed
     */
    public function getGlsArray() {
        return $this->glsArray;
    }

    /**
     * @param $glsArray
     * @return $this
     */
    public function setGlsArray($glsArray) {
        $this->glsArray = $glsArray;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function DEPRECATED_addGlsArrayValue( $key, $value ) {
        $this->glsArray[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName( $name )
    {
        $this->name = $name;
        $this->toGlsArray();
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue( $value )
    {
        $this->value = (String)$value;
        $this->toGlsArray();
        return $this;
    }
}
