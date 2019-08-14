<?php
# ToDo add license text here

/**
 * Class to work with archives
 *
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Model
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_GlsConnector_Model_Services
{

	/**
	 * @var	array	the service object converted into an array i.e. to generate correct GLS-JSON
	 */
	private $glsArray;

    /**
     * @var	string	$name	The required name for the service
     * 						This is a request payload entity
     */
    private $name;

    /**
     * @var	array	$infos	The required array containing SynergeticAgency_GlsConnector_Model_Info objects
     * 						This is a request payload entity
     */
    private $infos;

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
		if( $this->getShopDelivery() !== NULL )			$returnValue['ShopDelivery'] =      $this->getShopDelivery();
		if( $this->getNotificationEmail() !== NULL )	$returnValue['NotificationEmail'] = $this->getNotificationEmail();
		if( $this->getDeposit() !== NULL )				$returnValue['Deposit'] =           $this->getDeposit();
		if( $this->getShopReturn() !== NULL )			$returnValue['ShopReturn'] =        $this->getShopReturn();
		if( $this->getFlexDeliveryService() !== NULL )  $returnValue['FlexDelivery'] =      $this->getFlexDeliveryService();
		if( $this->getThinkGreenService() !== NULL )    $returnValue['ThinkGreen'] =        $this->getThinkGreenService();
		if( $this->getPrivateDelivery() !== NULL )      $returnValue['privateDelivery'] =   $this->getPrivateDelivery();
		if( $this->getGuaranteed24Service() !== NULL )  $returnValue['G24'] =               $this->getGuaranteed24Service();

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
	 * @param $guaranteed24Service  allowed values: true, 'true', 'Y'
	 * @return $this
	 */
	public function setGuaranteed24Service($guaranteed24Service) {
        $this->guaranteed24Service = NULL;
        if( $guaranteed24Service === true || $guaranteed24Service === 'true' || $guaranteed24Service === 'Y' ){
            $this->guaranteed24Service = 'Y';
        }
		$this->toGlsArray();
		return $this;
	}

}