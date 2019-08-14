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
class SynergeticAgency_GlsConnector_Model_Parcel
{

	const GLS_API_PARCEL_ROUTING_PRIMARY2D = "primary2D";
	const GLS_API_PARCEL_ROUTING_SECONDARY2D = "secondary2D";
	const GLS_API_PARCEL_ROUTING_NATIONALREF = "nationalRef";


	/**
	 * @var	array	the parcel object converted into an array i.e. to generate correct GLS-JSON
	 */
	private $glsArray;


    /**
     * @var	string	$parcelNumber	The parcel number sent in GLS-Response as numeric stringi.e. 60715018107
     * 						        This is a request payload entity
     */
    private $parcelNumber;

    /**
     * @var	string	$trackId	The parcel tracking ID sent in GLS-Response as alphanumeric uppercase string i.e. ZHYAQHYZ
     * 						        This is a request payload entity
     */
    private $trackId;

    /**
     * @var	string	$location	The parcel tracking URL sent in GLS-Response as full URL i.e. https://qs.gls-group.eu/track/ZHYAQHYZ
     * 						        This is a request payload entity
     */
    private $location;


	/**
	 * @var	float	$weight	The required weight with format %.2n
	 * 						This is a request payload entity
	 */
	private $weight;

	/**
	 * @var	array	$references	An array of parcel based references.
	 * 							ToDo: ??? This field is required only when GLS_API_SERVICE_CASHSERVICE is given, and the delivery address country is Germany ???
	 * 							This is a request payload entity
	 */
	private $references;

	/**
	 * @var	string	$comment	The optional comment with max length of 40 chars
	 * 							This is a request payload entity
	 */
	private $comment;


    /**
     * @var	array	$services	The required array containing SynergeticAgency_GlsConnector_Model_Service objects
     * 						    This is a request payload entity
     */
    private $services;


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
#echo "<br><br>Entering " . __METHOD__ . " @ line " . __LINE__ . ":";

#echo "<br>\$this->getReferences() = " . var_export($this->getReferences(), 1 ) . "";
#exit();
		$returnValue = array();

		if( $this->getWeight() !== NULL )       $returnValue['weight'] =        $this->getWeight();
		if( $this->getReferences() !== NULL )   $returnValue['references'] =    $this->getReferences();
		if( $this->getComment() !== NULL )      $returnValue['comment'] =       $this->getComment();

        if(count($this->getServices())) {
            foreach ($this->getServices() AS $serviceNum => $service) {
                /** @var $info SynergeticAgency_GlsConnector_Model_Service */
                if ($service instanceof SynergeticAgency_GlsConnector_Model_Service) $returnValue['services'][$serviceNum] = $service->getGlsArray();
            }
        }

#echo "<br>Leave " . __METHOD__ . " @ line " . __LINE__ . ":" . var_export( $returnValue, 1);

#exit();

        $this->setGlsArray( $returnValue );

        return $returnValue;
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
	public function addGlsArrayValue( $key, $value ) {
		$this->glsArray[$key] = $value;
		return $this;
	}

	/**
	 * @return int
	 */
	public function DEPRECATED_getNum() {
		return $this->num;
	}

	/**
	 * @param $num
	 * @return $this
	 */
	public function DEPRECATED_setNum($num) {
		$this->num = $num;
		return $this;
	}

    /**
     * @return string
     */
    public function getParcelNumber()
    {
        return $this->parcelNumber;
    }

    /**
     * @param $parcelNumber
     * @return $this
     */
    public function setParcelNumber($parcelNumber)
    {
        $this->parcelNumber = $parcelNumber;
        $this->toGlsArray();
        return $this;
    }

    /**
     * @return string
     */
    public function getTrackId()
    {
        return $this->trackId;
    }

    /**
     * @param $trackId
     * @return $this
     */
    public function setTrackId($trackId)
    {
        $this->trackId = $trackId;
        $this->toGlsArray();
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        $this->toGlsArray();
        return $this;
    }

	/**
	 * @return float
	 */
	public function getWeight() {
		return $this->weight;
	}

	/**
	 * @param float $weight
	 * @return $this
	 */
	public function setWeight( $weight = 0.0) {
		$this->weight = (Float)$weight;
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getReferences() {
		return $this->references;
	}

	/**
	 * @param array $references
	 * @return $this
	 */
	public function setReferences( $references ) {

        if( is_array($references) ){
            $this->references = $references;
        }

		$this->toGlsArray();
		return $this;
	}

    /**
     * @param $reference
     * @param null $referenceKey
     * @return $this
     */
    public function pushReference( $reference, $referenceKey = NULL ) {

        if( $referenceKey !== NULL && $referenceKey !== '' ){
            $this->references[$referenceKey] = $reference;
        } else {
            $this->references[] = $reference;
        }

        $this->toGlsArray();
        return $this;
    }

	/**
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * @param string $comment
	 * @return $this
	 */
	public function setComment( $comment = "" ) {
		$this->comment = (String)$comment;
		$this->toGlsArray();
		return $this;
	}

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param   array   $services   array, containing SynergeticAgency_GlsConnector_Model_Service objects
     * @return  $this
     */
    public function setServices( $services )
    {
        if( is_array($services) ){
            foreach( $services AS $key => $service){
                if(get_class($service) === "SynergeticAgency_GlsConnector_Model_Service" ){
                    $this->services[] = $service;
                }
            }
        }
        $this->toGlsArray();
    }

    /**
     * @param SynergeticAgency_GlsConnector_Model_Service $service
     * @return $this
     */
    public function pushService( SynergeticAgency_GlsConnector_Model_Service $service )
    {
        $this->services[] = $service;
        $this->toGlsArray();
        return $this;
    }

}
