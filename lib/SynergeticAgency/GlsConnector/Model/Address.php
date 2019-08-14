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
class SynergeticAgency_GlsConnector_Model_Address {


	const GLS_API_ADDRESS_DELIVERY = "delivery";
	const GLS_API_ADDRESS_ALTERNATIVESHIPPING = "alternativeShipper";
	const GLS_API_ADDRESS_RETURN = "return";


	/**
	 * @var	array	the address object converted into an array i.e. to generate correct GLS-JSON
	 */
	private $glsArray;

	/**
	 *
	 * @var	string	$type	The available address types:
	 * 							GLS_API_ADDRESS_DELIVERY:
	 * 								required
	 *							GLS_API_ADDRESS_ALTERNATIVESHIPPING:
	 * 								optional
	 *								Used when the customer wants to customize the shipment address on the label.
	 *								Does not work with “Pick&Ship Service” and Pick&Return Service”
	 * 								(both services are not implemented currently).
	 * 							GLS_API_ADDRESS_PICKUP:
	 * 								mandatory
	 *								for shop return service (SRS)
	 */
	private $type;

    /**
     * @var	string	$id	The required id with min length of 2 to 40 chars
     * 						This is a request payload entity
     */
    private $id;

    /**
     * @var	string	$name1	The required name with min length of 2 to 40 chars
     * 						This is a request payload entity
     */
    private $name1;

	/**
	 * @var	string	$name2	An optional name with max length of 40 chars
	 * 						This is a request payload entity
	 */
	private $name2;

	/**
	 * @var	string	$name3	An optional name with max length of 40 chars
	 * 						This is a request payload entity
	 */
	private $name3;

    /**
     * @var	string	$street1	The required street with length of 3 to 40 chars
     * 							This is a request payload entity
     */
    private $street1;

    /**
     * @var	string	$street2	The required street with length of 3 to 40 chars
     * 							This is a request payload entity
     */
    private $street2;

    /**
     * @var	string	$blockNo1	The required street with length of 3 to 40 chars
     * 							This is a request payload entity
     */
    private $blockNo1;

    /**
     * @var	string	$blockNo2	The required street with length of 3 to 40 chars
     * 							This is a request payload entity
     */
    private $blockNo2;

	/**
	 * @var	string	$country	The required ISO 3166-1 numeric country with length of 3 chars
	 * 							This is a request payload entity
	 */
	private $country;

	/**
	 * @var	string	$zipCode	The required zip code with length of 1 to 10 chars
	 * 							This is a request payload entity
	 */
	private $zipCode;

	/**
 * @var	string	$city	The required city with length of 2 to 40 chars
 * 						This is a request payload entity
 */
    private $city;

    /**
     * @var	string	$province	The required city with length of 2 to 40 chars
     * 						This is a request payload entity
     */
    private $province;

	/**
	 * @var	string	$contact	The contact with length of 2 to 40 chars
	 * 							This field is required when SynergeticAgency_GlsConnector_Model_Services:name = GLS_API_SHOPDELIVERY is given
	 * 							This is a request payload entity
	 */
	private $contact;

	/**
	 * @var	string	$email	The email with max length of 100 chars
	 * 						This field is required when SynergeticAgency_GlsConnector_Model_Services:name = GLS_API_SHOPDELIVERY is given
	 * 						When SynergeticAgency_GlsConnector_Model_Services:name = GLS_API_FLEXDELIVERYSERVICE is requested,
	 * 						either $email or $mobile has to be present
	 * 						This is a request payload entity
	 */
	private $email;

	/**
	 * @var	string	$phone	An optional phone with max length of 40 chars
	 * 						This is a request payload entity
	 */
	private $phone;

	/**
	 * @var	string	$mobile	The email with max length of 40 chars
	 * 						This field is required when SynergeticAgency_GlsConnector_Model_Services:name = GLS_API_SHOPDELIVERY is given
	 * 						When SynergeticAgency_GlsConnector_Model_Services:name = GLS_API_FLEXDELIVERYSERVICE is requested,
	 * 						either $email or $mobile has to be present
	 * 						This is a request payload entity
	 */
	private $mobile;

    /**
     * @var	string	$comments	An optional phone with max length of 40 chars
     * 						This is a request payload entity
     */
    private $comments;


	function __construct(){

	}


	/**
	 * @return array
	 */
	private function toGlsArray(){
		#echo "\n\nEntering " . __METHOD__ . " @ line " . __LINE__ . ":" . $this->getName1();
		$returnValue = array();

        if( $this->getId() !== NULL ) 	    $returnValue['id'] = 		    (string)$this->getId();
        if( $this->getName1() !== NULL ) 	$returnValue['name1'] = 		(string)$this->getName1();
		if( $this->getName2() !== NULL ) 	$returnValue['name2'] = 		(string)$this->getName2();
		if( $this->getName3() !== NULL ) 	$returnValue['name3'] = 		(string)$this->getName3();
        if( $this->getStreet1() !== NULL ) 	$returnValue['street1'] = 		(string)$this->getStreet1();
        if( $this->getStreet2() !== NULL ) 	$returnValue['street2'] = 		(string)$this->getStreet2();
        if( $this->getBlockNo1() !== NULL ) $returnValue['blockNo1'] = 		(string)$this->getBlockNo1();
        if( $this->getBlockNo2() !== NULL ) $returnValue['blockNo2'] = 		(string)$this->getBlockNo2();
		if( $this->getCountry() !== NULL )  $returnValue['country'] = 	    (string)$this->getCountry();
		if( $this->getZipCode() !== NULL ) 	$returnValue['zipCode'] = 		(string)$this->getZipCode();
		if( $this->getCity() !== NULL )		$returnValue['city'] = 			(string)$this->getCity();
        if( $this->getProvince() !== NULL )	$returnValue['province'] = 		(string)$this->getProvince();
        if( $this->getContact() !== NULL )	$returnValue['contact'] = 		(string)$this->getContact();
		if( $this->getEmail() !== NULL )	$returnValue['email'] = 		(string)$this->getEmail();
		if( $this->getPhone() !== NULL )	$returnValue['phone'] = 		(string)$this->getPhone();
        if( $this->getMobile() !== NULL )	$returnValue['mobile'] = 		(string)$this->getMobile();
        if( $this->getComments() !== NULL )	$returnValue['comments'] = 		(string)$this->getComments();

		#echo "\n\nLeave " . __METHOD__ . " @ line " . __LINE__ . ":" . $this->getName1();

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
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param $type
	 * @return $this
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->toGlsArray();
        return $this;
    }

    /**
     * @return string
     */
    public function getName1() {
        return $this->name1;
    }

	/**
	 * @param $name1
	 * @return $this
	 */
	public function setName1($name1) {
        //TODO: Implement filter in all setters (php filter)
		$this->name1 = substr($name1,0,40);
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName2() {
		return $this->name2;
	}

	/**
	 * @param $name2
	 * @return $this
	 */
	public function setName2($name2) {
		$this->name2 = substr($name2,0,40);
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName3() {
		return $this->name3;
	}

	/**
	 * @param $name3
	 * @return $this
	 */
	public function setName3($name3) {
		$this->name3 = substr($name3,0,40);
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStreet1() {
		return $this->street1;
	}

	/**
	 * @param $street1
	 * @return $this
	 */
	public function setStreet1($street1) {
		$this->street1 = substr($street1,0,40);
		$this->toGlsArray();
		return $this;
	}

    /**
     * @return string
     */
    public function getStreet2() {
        return $this->street2;
    }

    /**
     * @param $street2
     * @return $this
     */
    public function setStreet2($street2) {
        $this->street2 = substr($street2,0,40);
        $this->toGlsArray();
        return $this;
    }

    /**
     * @return string
     */
    public function getBlockNo1()
    {
        return $this->blockNo1;
    }

    /**
     * @param string $blockNo1
     * @return $this
     */
    public function setBlockNo1($blockNo1)
    {
        $this->blockNo1 = $blockNo1;
        $this->toGlsArray();
        return $this;
    }

    /**
     * @return string
     */
    public function getBlockNo2()
    {
        return $this->blockNo2;
    }

    /**
     * @param string $blockNo2
     * @return $this
     */
    public function setBlockNo2($blockNo2)
    {
        $this->blockNo2 = $blockNo2;		$this->toGlsArray();
        return $this;
    }

	/**
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}

    /**
     * @param $country
     * @return $this
     */
	public function setCountry( $country ) {

        //$country = str_pad($country, 3, '0', STR_PAD_LEFT);
		$this->country = "$country";
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getZipCode() {
		return $this->zipCode;
	}

	/**
	 * @param $zipCode
	 * @return $this
	 */
	public function setZipCode($zipCode) {
		$this->zipCode = $zipCode;
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @param $city
	 * @return $this
	 */
	public function setCity($city) {
		$this->city = $city;
		$this->toGlsArray();
		return $this;
	}

    /**
     * @return string
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @param string $province
     * @return $this
     */
    public function setProvince($province)
    {
        $this->province = $province;
        $this->toGlsArray();
        return $this;
    }



	/**
	 * @return string
	 */
	public function getContact() {
		return $this->contact;
	}

	/**
	 * @param $contact
	 * @return $this
	 */
	public function setContact($contact) {
		$this->contact = $contact;
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param $email
	 * @return $this
	 */
	public function setEmail($email) {
		$this->email = $email;
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * @param $phone
	 * @return $this
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
		$this->toGlsArray();
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * @param $mobile
	 * @return $this
	 */
	public function setMobile($mobile) {
		$this->mobile = $mobile;
		$this->toGlsArray();
		return $this;
	}

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     * @return $this
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        $this->toGlsArray();
        return $this;
    }
}
