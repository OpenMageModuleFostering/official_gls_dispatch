<?php
# ToDo add license text here

/**
 * Class to work with archives
 *
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Model
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_GlsConnector_Model_JsonConfig
{

	/**
	 * @var	array	the service object converted into an array i.e. to generate correct GLS-JSON
	 */
	private $glsArray;

	/**
	 * @var string $countryCode	ISO-31661 alpha 2
	 */
	private $countryCode;


	/**
	 *
	 */
	function __construct() {

	}

	/**
	 * @return array
	 */
	private function toGlsArray() {
		#echo "\n\nEntering " . __METHOD__ . " @ line " . __LINE__ . ":" . $this->getShopDelivery();
		$returnValue = array();

		/** @var $parcel SynergeticAgency_GlsConnector_Model_Services */
		if( $this->getShopDelivery() !== NULL )			$returnValue['ShopDelivery'] =		$this->getShopDelivery();
		if( $this->getNotificationEmail() !== NULL )	$returnValue['NotificationEmail'] =	$this->getNotificationEmail();
		if( $this->getDeposit() !== NULL )				$returnValue['Deposit'] =			$this->getDeposit();
		if( $this->getShopReturn() !== NULL )			$returnValue['ShopReturn'] =		$this->getShopReturn();

		#echo "\n\nLeave " . __METHOD__ . " @ line " . __LINE__ . ":" . var_export( $returnValue, 1);

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
	public function getCountryCode() {
		return $this->countryCode;
	}

	/**
	 * @param string $countryCode
	 */
	public function setCountryCode($countryCode) {
		$this->countryCode = $countryCode;
	}


}