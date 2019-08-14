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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category   SynergetigAgency
 * @package    SynergeticAgency\Gls\Model
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class SynergeticAgency_Gls_Model_Jsonimport
 */
class SynergeticAgency_Gls_Model_Jsonimport extends Mage_Core_Model_Abstract{

    /**
     * Path to file containing GLS configuration json data
     * @var
     */
	private $jsonConfigFile;

    /**
     * GLS configuration json data
     * @var
     */
    private $_jsonConfig;

    /**
     * Constructor - instantiate parent constructor and set path to config file
     */
	function __construct() {
		parent::__construct();
		$this->setJsonConfigFile( DS . trim( Mage::getBaseDir('var') . DS . "import" . DS . "gls" . DS . "json", DS) . DS . "glsconfig.json" );
	}

    /**
     * cron function
     * Refresh GLS configuration on a regular base
     */
	public function run(){

        $json = $this->getJsonConfigFromConnector();

		if ( $json && $this->saveJson( $json ) ){
			// JSON Config saved successful
		} else {
			//ToDo: implement fallback
		};
	}

    /**
     * Get the GLS json configuration directly from GLS
     * The Connector is a lib handling Magento unrelated GLS specific tasks
     * @return bool|mixed
     */
    private function getJsonConfigFromConnector() {
        /** @var $connector SynergeticAgency_GlsConnector_Connector */
        $connector = new SynergeticAgency_GlsConnector_Connector();

        if( Mage::getStoreConfig('gls/general/sandbox') !== '1' ){
            //$this->getGlsHelperLog()->log( __METHOD__, __LINE__, "gls/general/sandbox is set to '1'", Zend_Log::INFO );
            $connector->setGlsApiSandbox( false );
        }

        $connector->setGlsApiUrl( Mage::getStoreConfig('gls/general/api_url') );

        // set GLS-API-JSON-CONFIG-URI possibility 1 START
        // do not use this possibility for the new GLS-API provided by GLS in the future,
        // because in the new version, JSON-Config will be delivered from the GLS-API service(see setGlsApiUrl() and shipment)
        $connector->setGlsApiJsonUrl( Mage::getStoreConfig('gls/general/json_url') );

        $connector->loadJsonConfig();
        $json = $connector->getJsonConfig();
        if(!$connector->isJson($json)) {
            $json = false;
        }
        return $json;
    }

	/**
     * Save configuration data provided by GLS to a local cached json configuration file
	 * @param $json
	 * @return bool
	 */
	public function saveJson( $json ){

		$returnValue = true;

		// ToDo: use Mage filehandler instead...
		if (!$handle = fopen($this->getJsonConfigFile(), "w")) {
			print "Cannot open " . $this->getJsonConfigFile();
			$returnValue = false;
		}

		if ( $returnValue === true ) {
			if (!fwrite($handle, $json)) {
				print "Cannot write \$json into " . $this->getJsonConfigFile();
				$returnValue = false;
			}
		} else {
			fclose($handle);
		}

		return $returnValue;
	}

	/**
     * Get the path to GLS configuration file
	 * @return mixed
	 */
	private function getJsonConfigFile() {
		return $this->jsonConfigFile;
	}

	/**
     * Set the path to GLS configuration file
	 * @param $jsonConfigFile
	 * @return $this
	 */
	private function setJsonConfigFile($jsonConfigFile) {
		$this->jsonConfigFile = $jsonConfigFile;
		return $this;
	}

    /**
     * Reading full GLS configuration from (cached) file
     * Cached in this case means the configuration file located on a GLS server is stored on the server where the magento is running for performance reasons
     * @return bool|string
     */
    private function getJsonConfigFromFile() {
        $json = false;
        if(is_file($this->getJsonConfigFile())) {
            $json = file_get_contents($this->getJsonConfigFile());
        }
        return $json;
    }

    /**
     * Getting the full GLS configuration based on several fallback layers
     * 1. From Magento cache
     * 2. From local cache File
     * 3. Directly from GLS
     * @return null|Zend_Config
     */
    public function getJsonConfig() {
        if(empty($this->_jsonConfig)) {
            // try to get it from cache
            $cacheKey = 'gls-jsonconfig';
            $cache = Mage::app()->getCache();
            //TODO: Check why this
            //$jsonConfig = $cache->load($cacheKey);
            $jsonConfig = false;
            $fromCache = true;
            if(!$jsonConfig) {
                $fromCache = false;
                // first try to get it from file
                $jsonConfig = $this->getJsonConfigFromFile();
            }
            if(!$jsonConfig) {
                // try to get it from connector
                $jsonConfig = $this->getJsonConfigFromConnector();
                if($jsonConfig) {
                    $this->saveJson($jsonConfig);
                }
            }
            if($jsonConfig) {
                if($fromCache) {
                    // unserialize data from cache - it's a zend config object already
                    $jsonConfig = unserialize($jsonConfig);
                } else {
                    // save data in cache - first build zend config object then serialize for cache
                    $jsonConfig = new Zend_Config_Json($jsonConfig);
                    $cache->save(serialize($jsonConfig), $cacheKey, array('SYNERGETIC_GLS'), 86400);
                }
                $this->_jsonConfig = $jsonConfig;
            }
        }
        return $this->_jsonConfig;
    }

    /**
     * Get GLS configuration for a specific country
     * @param string $countryId
     * @return null|Zend_Config
     */
    public function getCountry($countryId) {
        $jsonConfig = $this->getJsonConfig();
        $country = null;
        if($jsonConfig && $jsonConfig->get('config',null)) {
            $country = $jsonConfig->get('config')->get($countryId, null);
        }
        return $country;
    }

    /**
     * Get configuration for all available shipping destination countries based on origin shipping country
     * which are allowed for shipping origin country by GLS
     * @param string $countryId
     * @return null|Zend_Config The configuration or null if destination shipping country not available for origin country
     */
    public function getForeignCountriesByOriginCountryId($countryId) {
        $country = $this->getCountry($countryId);
        $foreignCountries = null;
        if($country) {
            if($country->get('foreign',null)) {
                $foreignCountries = $country->get('foreign',null)->get('countries');
            }
        }
        return $foreignCountries;
    }

    /**
     * Get an configuration value for inland shipping
     * @param string $countryId
     * @param string $fieldName
     * @return null|string The config value or null if country not allowed by GLS
     */
    public function getDomesticValueByOriginCountryId($countryId,$fieldName) {
        $country = $this->getCountry($countryId);
        $returnValue = null;
        if($country) {
            $returnValue = $country->get('domestic')->get($fieldName,null);
        }
        return $returnValue;
    }

    /**
     * Get an configuration value for a destination shipping country based on origin country
     * @param $originCountryId
     * @param $foreignCountryId
     * @param $fieldName
     * @return null|string The config value or null if destination shipping country not available for origin country
     */
    public function getForeignValueByOriginAndForeignCountryId($originCountryId,$foreignCountryId,$fieldName) {
        $country = $this->getCountry($originCountryId);
        $returnValue = null;
        if($country) {
            $foreignCountry = $country->get('foreign')->get('countries')->get($foreignCountryId,null);
            if($foreignCountry) {
                $returnValue = $foreignCountry->get($fieldName,null);
            }
        }
        return $returnValue;
    }
}