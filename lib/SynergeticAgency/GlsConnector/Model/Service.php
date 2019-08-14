<?php
# ToDo add license text here

/**
 * Class to work with archives
 *
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Model
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_GlsConnector_Model_Service
{


    const GLS_API_SERVICE_CASHONDELIVERY =          "cashondelivery";
    const GLS_API_SERVICE_DELIVERYATWORKSERVICE =   "deliveryatworkservice";
    const GLS_API_SERVICE_SHOPDELIVERYSERVICE =     "shopdeliveryservice";
    const GLS_API_SERVICE_ADDONLIABILITYSERVICE =   "addonliabilityservice";
    const GLS_API_SERVICE_DEPOSITSERVICE =          "depositservice";
    const GLS_API_SERVICE_IDENTPINSERVICE =         "identpinservice";
    const GLS_API_SERVICE_INTERCOMPANYSERVICE =     "intercompanyservice";
    const GLS_API_SERVICE_SHOPRETURNSERVICE =       "shopreturnservice";
    const GLS_API_SERVICE_GUARANTEED24SERVICE =     "guaranteed24service";
    const GLS_API_SERVICE_FLEXDELIVERYSERVICE =     "flexdeliveryservice";
    const GLS_API_SERVICE_THINKGREENSERVICE =       "thinkgreenservice";
    const GLS_API_SERVICE_DOCUMENTRETURNSERVICE =   "documentreturnservice";
    const GLS_API_SERVICE_PROOFSERVICE =            "proofservice";
    const GLS_API_SERVICE_PRIVATEDELIVERYSERVICE =  "privatedeliveryservice";
    const GLS_API_SERVICE_EXWORKSSERVICE =          "exworksservice";
    const GLS_API_SERVICE_EXCHANGESERVICE =         "exchangeservice";


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
        #echo "\n\nEntering " . __METHOD__ . " @ line " . __LINE__ . ":";
        #echo "\nLeave " . __METHOD__ . " @ line " . __LINE__ . ":";
	}

	/**
	 * @return array
	 */
	private function toGlsArray() {
		#echo "\n\nEntering " . __METHOD__ . " @ line " . __LINE__ . ":";
		$returnValue = array();

		if( $this->getName() !== NULL )     $returnValue['name'] =  $this->getName();

        if(count($this->getInfos())) {
            foreach ($this->getInfos() AS $infoNum => $info) {
                /** @var $info SynergeticAgency_GlsConnector_Model_Info */
                if ($info instanceof SynergeticAgency_GlsConnector_Model_Info) $returnValue['infos'][$infoNum] = $info->getGlsArray();
            }
        }

		#echo "\nLeave " . __METHOD__ . " @ line " . __LINE__ . ":" . var_export( $returnValue, 1);

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
     * @param string $name
     * @return $this
     */
    public function setName( $name )
    {
        $this->name = $name;
        $this->toGlsArray();
        return $this;
    }

    /**
     * @return array
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * @param   array   $infos   array, containing SynergeticAgency_GlsConnector_Model_SInfo objects
     * @return  $this
     */
    public function setInfos( $infos )
    {
        if( is_array($infos) ){
            foreach( $infos AS $key => $info){
                if(get_class($info) === "SynergeticAgency_GlsConnector_Model_Info" ){
                    $this->infos[] = $info;
                }
            }
        }
        $this->toGlsArray();

        return $this;
    }

    /**
     * @param SynergeticAgency_GlsConnector_Model_Info $info
     * @return $this
     */
    public function pushInfo( SynergeticAgency_GlsConnector_Model_Info $info )
    {
        #echo "\n\nEntering " . __METHOD__ . " @ line " . __LINE__ . ":" . var_export($info, 1);

        $this->infos[] = $info;
        $this->toGlsArray();

        #echo "\nLeave " . __METHOD__ . " @ line " . __LINE__ . ":" . var_export( $this->infos, 1);

        return $this;
    }

}
