<?php
# ToDo add license text here

/**
 * Class to work with GLS API
 *
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Connector
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_GlsConnector_Connector
{
	const GLS_API_CONNECTOR_FUNC_SHIPPING = "shipments";
	const GLS_API_CONNECTOR_FUNC_JSON = "shipments/configuration";

	private $host;
	private $schema;
	private $port;
	private $baseUri;
	private $apiEnpointPath;
	private $apiEnpointFunction;
	private $glsApiUsername;
	private $glsApiPassword;
    private $glsApiAuthUsername;
    private $glsApiAuthPassword;
	private $glsApiUrl;
	private $glsApiLang;
	private $glsApiSandbox;

	private $glsApiJsonUrl;

	private $customerid;
	private $contactid;

	private $curl;
	private $curlOpts = array();
	private $curlAuthCookie = "";
	private $curlResponse;
	private $curlResponseHeaderSize;
	private $curlResponseHeader;
	private $curlResponseBody;
	private $curlErrno;
	private $curlConnectTimeoutMs = 10000;
	private $curlTimeoutMs = 10000;
	private $curlErrorRetry = 3;
	private $curlErrorRetryCount = 0;
	private $curlInfo;

	private $currentFunction;
	private $currentCall;

	private $log;
	private $error;

	private $sleepSeconds = 0;
	private $slept = 0;

	private $shipment;

	private $jsonConfig;

    /**
     *
     */
	function __construct(){
		// init logger
		$this->setLog();

		$logMessage = 'entered';
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );

		$error = new SynergeticAgency_GlsConnector_Model_Error();
		$this->setError( $error );

		//use "GLS-API Sandbox" per default
		$this->setGlsApiSandbox( true );
	}

    /**
     * @return $this
     */
	public function initCurl() {
		$logMessage = 'entered';
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );

		$this->curl = false;
		$this->curlOpts = array();
		$this->setCurl( curl_init() );
		$this->setCurlOpts(
			array(
				CURLOPT_ENCODING => 'UTF-8',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT_MS => $this->getCurlConnectTimeoutMs(),
				CURLOPT_TIMEOUT_MS => $this->getCurlTimeoutMs(),
				CURLINFO_HEADER_OUT => 1,
                #CURLOPT_SSL_VERIFYHOST => false,
                #CURLOPT_SSL_VERIFYPEER => false,

                CURLOPT_HEADER => 1,
				// the following basic auth will be used in the next generation of the GLS API(see also "PR178DED Web Shop eCom Plugins")
				CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $this->getGlsApiAuthUsername() . ":" . $this->getGlsApiAuthPassword()
                #CURLOPT_USERPWD => "webapi:webapi"
			)
		);

        $this->getLog()->write( __METHOD__, __LINE__, "ATTENTION, in productive system, remove CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER from curl opt setting above", SynergeticAgency_GlsConnector_Log::ERR );

		// if curl debug mode is set to true, you can debug the curl exec using
		// var_export(curl_getinfo($this->curl))
		#if( $this->getCurlDebugMode() === true ){
		#	$this->addCurlOpts(
		#		array(
		#			CURLINFO_HEADER_OUT => 1,
		#		)
		#	);
		#}

		#// Cookie relevant codes START
		#// if curlAuthCookie is present, add curl option CURLOPT_HTTPHEADER with curlAuthCookie value
		#if( $this->getCurlAuthCookie() !== "" ){
		#	$this->addCurlOpts(
		#		array(
		#			CURLOPT_HTTPHEADER => array( $this->getCurlAuthCookie() ),
		#		)
		#	);
		#}
		#// Cookie relevant codes STOP

		return $this;
	}



	/**
	 * executes the request for $this->curl
	 */
	private function curlExec(){
		$logMessage = 'entered';
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );

		$this->setCurlResponse( curl_exec($this->getCurl()) );
		$this->setCurlErrno( curl_errno($this->getCurl()) );

		$this->setCurlResponseHeaderSize(curl_getinfo($this->getCurl(), CURLINFO_HEADER_SIZE) );
		$this->setCurlResponseHeader( substr($this->getCurlResponse(), 0, $this->getCurlResponseHeaderSize()) );
		$this->setCurlResponseBody( substr($this->getCurlResponse(), $this->getCurlResponseHeaderSize()) );
		$this->setCurlInfo();

		$logMessage = "curl_errno: " . $this->getCurlErrno();
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );

		// handle CURLE_OPERATION_TIMEOUTED or CURLE_COULDNT_RESOLVE_HOST errors
		if( $this->getCurlErrorRetryCount() < $this->getCurlErrorRetry() &&
			(
				$this->getCurlErrno() === CURLE_OPERATION_TIMEOUTED ||
				$this->getCurlErrno() === CURLE_COULDNT_RESOLVE_HOST
			)
		){
			$this->increaseCurlErrorRetryCount();
			$function = $this->getCurrentFunction();

			switch( $this->getCurlErrno() ){
				case CURLE_OPERATION_TIMEOUTED:
					$logMessage = 'CURLE_OPERATION_TIMEOUTED: Operation timeout (' . $this->getCurlTimeoutMs() / 1000 . "s) @ " . $function;
					$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::ALERT );
					break;
				case CURLE_COULDNT_RESOLVE_HOST:
					$logMessage = 'CURLE_COULDNT_RESOLVE_HOST: Cannot resolve host (' . $this->getHost() . ") @ " . $function;
					$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::ALERT );
					break;
			}

			$logMessage = 'retry no ' . $this->getCurlErrorRetryCount() . ' to execute function $this->' . $function . '} @ ' . $function;
			$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );
            $this->curlExec();
		} else {
			if($this->getCurlErrorRetryCount() === $this->getCurlErrorRetry()) {
				$logMessage = 'retry curlExec failed for ' . $this->getCurlErrorRetryCount() . ' times';
				$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::EMERG );
				throw new Exception( $logMessage );
			}
			$this->setCurlErrorRetryCount( 0 );
		}

		#// Cookie relevant codes START
		#$this->checkCookieExpired();
		#// Cookie relevant codes STOP
	}


	/**
	 * @throws Exception
	 */
	public function createShipment(){

		$logMessage = 'entered';
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );

		$this->setCurrentFunction( __FUNCTION__ );
		$this->setCurrentCall( $this->getGlsApiUrl() .	SynergeticAgency_GlsConnector_Connector::GLS_API_CONNECTOR_FUNC_SHIPPING );
		// in the next generation of the GLS API(see also "PR178DED Web Shop eCom Plugins"), set constant GLS_API_CONNECTOR_FUNC_SHIPPING to "shipmenmts"

		if( $this->getGlsApiSandbox() !== false ){
			$logMessage = 'create shipment against GLS-API Sandbox(' . $this->getGlsApiUrl() . ')';
			$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::NOTICE );
			$this->getShipment()->setSandbox( true );
		} else {
			$logMessage = 'create shipment against GLS-API productive system(' . $this->getGlsApiUrl() . ')';
            $this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::NOTICE );
            $this->getShipment()->setSandbox( false );
		}

#echo "\n\nCURLOPT_POSTFIELDS: \n" . $this->getShipment()->toJson();
#exit();

		$this->sleeper();
		$this->initCurl();
		$this->addCurlOpts(
			array(
				CURLOPT_URL => $this->getCurrentCall(),
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $this->getShipment()->toJson(),
				CURLOPT_HEADER => 1,
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen( $this->getShipment()->toJson() )
				),
			)
		);

        $this->getLog()->write( __METHOD__, __LINE__, $this->getShipment()->toJson(), SynergeticAgency_GlsConnector_Log::INFO );

		curl_setopt_array( $this->getCurl(), $this->getCurlOpts() );
		$this->curlExec();

		// on Error, return something other than $this
		if( $this->getCurlErrno() !== 0 ){
			$logMessage = 'CURL ERROR NUMBER: ' . $this->getCurlErrno();
			$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::ERR );

			$logMessage = '\$this->getCurlResponse(): ' . var_export( $this->getCurlResponse(), 1);
			$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );

		} else {
			$responseArray = json_decode($this->getCurlResponseBody());

            //handle HTTP-Statuscodes 2xx
            $httpStausCodes2xx = array(200,201,202,203,204,205,206,207,208,226);
			#if( $this->getCurlInfo( 'http_code' ) !== 200 ){
            if( !in_array($this->getCurlInfo( 'http_code' ), $httpStausCodes2xx) ){
                $this->getError()->setHasError( true );
				if( isset($responseArray->{'errors'}) ){
                    if(is_array($responseArray->{'errors'})){
                        foreach($responseArray->{'errors'} AS $responseError ){
                            $errorMessage = "";
                            if( isset($responseError->{'exitMessage'}) ){
                                $errorMessage.= $responseError->{'exitMessage'} . " ";
                            }
                            if( isset($responseError->{'description'}) ){
                                $errorMessage.= $responseError->{'description'} . " ";
                            }
                            if( isset($responseError->{'exitCode'}) ){
                                $errorMessage.= "(ExitCode " . $responseError->{'exitCode'} . ", HTTP-Status: " . $this->getCurlInfo( 'http_code' ) . ")";
                            }

                            if( $errorMessage === "" ){
                                $errorMessage = get_class($this) . "An unknown error has occurred";
                            }
                            $this->getError()->setMessage( trim($errorMessage) );
                        }
                    }
				}

				#if( isset($responseArray->{'ModelState'}) ){
				#	$this->getError()->setModelState( $responseArray->{'ModelState'} );
				#}

			} else {


#echo "\n\n\n\$responseArray: " . var_export($responseArray->{'parcels'}, 1);
#exit();

				if( $responseArray->{'consignmentId'} ){
					$this->getShipment()->setconsignmentId( $responseArray->{'consignmentId'} );
				}

                if( is_array($responseArray->{'labels'}) ){
                    foreach( $responseArray->{'labels'} as $num => $label ){
                        if (base64_decode($label, true)) {
                            $this->getShipment()->pushLabel( $label );
                        }
                    }
                }

				#if (base64_decode($responseArray->{'PDF'}, true)) {
				#	$this->getShipment()->setPdf( $responseArray->{'PDF'} );
				#}

				if( is_array($responseArray->{'parcels'}) ){
					foreach( $responseArray->{'parcels'} as $num => $parcel ){
#echo "\nWORK ON PARCEL NUM " . $num;
						$this->getShipment()->getParcels()[$num]->setParcelNumber($parcel->parcelNumber );
						$this->getShipment()->getParcels()[$num]->setTrackId($parcel->trackId );
						$this->getShipment()->getParcels()[$num]->setLocation($parcel->location );

                        // routing is DEPRECATED
						#$routing = array();
						#foreach( $parcel->Routing as $key => $value){
						#	$routing[$key] = $parcel->Routing->{$key};
						#}
						#$this->getShipment()->getParcels()[$num]->setRouting( $routing );
					}
				}
				$this->getError()->hasError( false );
#exit();
			}
		}
	}

	/**
	 *
	 */
	public function loadJsonConfig(){
		$logMessage = 'entered';
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );
		// in an further version of the GLS-API service, use cUrl to load the JSON-configuration
		$json = file_get_contents( $this->getGlsApiJsonUrl() );
		if( $this->isJson($json) ){
			$this->setJsonConfig( $json );
		}
	}

	/**
	 * @param $string
	 * @return bool
	 */
	public function isJson($string) {
		$logMessage = 'entered';
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	/**
	 * performs a application sleep in order of configured seconds to sleep
	 * see also configuration
	 */
	private function sleeper(){
		$logMessage = 'entered';
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );
		if($this->slept == 0 ){
			for( $i=0; $i<$this->getSleepSeconds(); $i++ ){
				//echo "\n\n" . __METHOD__ . " @ line " . __LINE__ . ": " . "sleep: " . $i;
				sleep(1);
			}
			#$this->slept = 1;
		}
	}


	/**
	 * @return mixed
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param $host
	 * @return $this
	 */
	public function setHost($host) {
		$this->host = $host;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSchema() {
		return $this->schema;
	}

	/**
	 * @param $schema
	 * @return $this
	 */
	public function setSchema($schema) {
		$this->schema = $schema;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param $port
	 * @return $this
	 */
	public function setPort($port) {
		$this->port = $port;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBaseUri() {
		return $this->baseUri;
	}

	/**
	 * @param $baseUri
	 * @return $this
	 */
	public function setBaseUri($baseUri) {
		$this->baseUri = "/" . trim( $baseUri, "/" );
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getApiEnpointPath() {
		return $this->apiEnpointPath;
	}

	/**
	 * @param $apiEnpointPath
	 * @return $this
	 */
	public function setApiEnpointPath($apiEnpointPath) {
		$this->apiEnpointPath = "/" . trim( $apiEnpointPath, "/" );
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getApiEnpointFunction() {
		return $this->apiEnpointFunction;
	}

	/**
	 * @param $apiEnpointFunction
	 * @return $this
	 */
	public function setApiEnpointFunction($apiEnpointFunction) {
		$this->apiEnpointFunction = $apiEnpointFunction;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function DEPRECATED_getGlsApiUsername() {
		return $this->glsApiUsername;
	}

    /**
     * @param $glsApiUsername
     * @return $this
     */
	public function DEPRECATED_setGlsApiUsername($glsApiUsername) {
		$this->glsApiUsername = $glsApiUsername;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function DEPRECATED_getGlsApiPassword() {
		return $this->glsApiPassword;
	}

    /**
     * @param $glsApiPassword
     * @return $this
     */
	public function DEPRECATED_setGlsApiPassword($glsApiPassword) {
		$this->glsApiPassword = $glsApiPassword;
		return $this;
	}

    /**
     * @return mixed
     */
    public function getGlsApiAuthUsername()
    {
        return $this->glsApiAuthUsername;
    }

    /**
     * @param $glsApiAuthUsername
     * @return $this
     */
    public function setGlsApiAuthUsername($glsApiAuthUsername)
    {
        $this->glsApiAuthUsername = $glsApiAuthUsername;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGlsApiAuthPassword()
    {
        return $this->glsApiAuthPassword;
    }

    /**
     * @param $glsApiAuthPassword
     * @return $this
     */
    public function setGlsApiAuthPassword($glsApiAuthPassword)
    {
        $this->glsApiAuthPassword = $glsApiAuthPassword;
        return $this;
    }



	/**
	 * @return mixed
	 */
	public function getCurl() {
		return $this->curl;
	}

	/**
	 * @param $curl
	 * @return $this
	 */
	public function setCurl( $curl ) {
		$this->curl = $curl;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCurlConnectTimeoutMs() {
		return $this->curlConnectTimeoutMs;
	}

	/**
	 * @param $curlConnectTimeoutMs
	 * @return $this
	 */
	public function setCurlConnectTimeoutMs($curlConnectTimeoutMs) {
		$this->curlConnectTimeoutMs = $curlConnectTimeoutMs;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCurlTimeoutMs() {
		return $this->curlTimeoutMs;
	}

	/**
	 * @param $curlTimeoutMs
	 * @return $this
	 */
	public function setCurlTimeoutMs($curlTimeoutMs) {
		$this->curlTimeoutMs = $curlTimeoutMs;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCurlErrorRetry() {
		return $this->curlErrorRetry;
	}

	/**
	 * @param $curlErrorRetry
	 * @return $this
	 */
	public function setCurlErrorRetry($curlErrorRetry) {
		$this->curlErrorRetry = $curlErrorRetry;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCurlErrorRetryCount() {
		return $this->curlErrorRetryCount;
	}

	/**
	 * @param $curlErrorRetryCount
	 * @return $this
	 */
	public function setCurlErrorRetryCount($curlErrorRetryCount) {
		$this->curlErrorRetryCount = $curlErrorRetryCount;
		return $this;
	}

	/**
	 * @param int $increase
	 * @return $this
	 */
	private function increaseCurlErrorRetryCount( $increase = 1 ){

		if( $increase > 0 ){
			$this->curlErrorRetryCount = $this->curlErrorRetryCount + $increase;
		}

		return $this;
	}

	/**
	 * @param int $reduce
	 * @return $this
	 */
	private function reduceCurlErrorRetryCount( $reduce = 1 ){
		if( $reduce < 0 ){
			$this->curlErrorRetryCount = $this->curlErrorRetryCount - $reduce;
		}

		return $this;
	}



	/**
	 * @return array
	 */
	private function getCurlOpts() {
		return $this->curlOpts;
	}

	/**
	 * @param $curlOpts
	 * @return $this
	 */
	private function setCurlOpts( $curlOpts ) {
		$this->curlOpts = $curlOpts;
		return $this;
	}

	/**
	 * @param $curlOpts
	 * @return $this
	 */
	private function addCurlOpts( $curlOpts ) {
		$logMessage = 'entered';
		$this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );

		// merge the CURLOPT_HTTPHEADER values,
		// because the $this->curlOpts = $this->curlOpts + (Array)$curlOpts
		// will destroy the CURLOPT_HTTPHEADER values
		// see also comment "do not use array_merge because of integer values of curl opt constants" below
		if( isset($this->curlOpts[CURLOPT_HTTPHEADER]) && isset($curlOpts[CURLOPT_HTTPHEADER]) ){
			$actualCurlOptHttpHeaders = $this->curlOpts[CURLOPT_HTTPHEADER];
			$additionalCurlOptHttpHeaders = $curlOpts[CURLOPT_HTTPHEADER];
			unset( $this->curlOpts[CURLOPT_HTTPHEADER] );
			unset( $curlOpts[CURLOPT_HTTPHEADER] );

			foreach( $actualCurlOptHttpHeaders as $key => $value ){
				$this->curlOpts[CURLOPT_HTTPHEADER][] = $value;
			}
			foreach( $additionalCurlOptHttpHeaders as $key => $value){
				$this->curlOpts[CURLOPT_HTTPHEADER][] = $value;
			}
		}

		// do not use array_merge because of integer values of curl opt constants
		$this->curlOpts = $this->curlOpts + (Array)$curlOpts;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurlResponse() {
		return $this->curlResponse;
	}

	/**
	 * @param $curlResponse
	 * @return $this
	 */
	public function setCurlResponse($curlResponse) {
		$this->curlResponse = $curlResponse;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurlResponseHeaderSize() {
		return $this->curlResponseHeaderSize;
	}

	/**
	 * @param $curlResponseHeaderSize
	 * @return $this
	 */
	public function setCurlResponseHeaderSize($curlResponseHeaderSize) {
		$this->curlResponseHeaderSize = $curlResponseHeaderSize;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurlResponseHeader() {
		return $this->curlResponseHeader;
	}

	/**
	 * @param $curlResponseHeader
	 * @return $this
	 */
	public function setCurlResponseHeader($curlResponseHeader) {
		$this->curlResponseHeader = $curlResponseHeader;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurlResponseBody() {
		return $this->curlResponseBody;
	}

	/**
	 * @param $curlResponseBody
	 * @return $this
	 */
	public function setCurlResponseBody($curlResponseBody) {
		$this->curlResponseBody = $curlResponseBody;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurlErrno() {
		return $this->curlErrno;
	}

	/**
	 * @param $curlErrno
	 * @return $this
	 */
	public function setCurlErrno($curlErrno) {
		$this->curlErrno = $curlErrno;
		return $this;
	}

	/**
	 * @param $param
	 * @return mixed
	 */
	public function getCurlInfo( $param ){
		$returnValue = $this->curlInfo;

		if( isset($returnValue[$param]) ){
			$returnValue = $returnValue[$param];
		}

		return $returnValue;
	}

	/**
	 * @return $this
	 */
	public function setCurlInfo() {
		$this->curlInfo = curl_getinfo( $this->getCurl() );
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurrentFunction() {
		return $this->currentFunction;
	}

	/**
	 * @param $currentFunction
	 * @return $this
	 */
	public function setCurrentFunction($currentFunction) {
		$this->currentFunction = $currentFunction;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCurrentCall() {
		return $this->currentCall;
	}

	/**
	 * @param $currentCall
	 * @return $this
	 */
	public function setCurrentCall($currentCall) {
		$this->currentCall = $currentCall;
		return $this;
	}

	/**
	 * @return null|SynergeticAgency_GlsConnector_Log
	 */
	public function getLog() {
		return $this->log;
	}

	/**
	 * @return $this
	 */
	public function setLog() {
		$this->log = new SynergeticAgency_GlsConnector_Log();
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * @param $error
	 * @return $this
	 */
	public function setError($error) {
		$this->error = $error;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSleepSeconds() {
		return $this->sleepSeconds;
	}

	/**
	 * @param $sleepSeconds
	 * @return $this
	 */
	public function setSleepSeconds($sleepSeconds) {
		$this->sleepSeconds = $sleepSeconds;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCustomerid() {
		return $this->customerid;
	}

	/**
	 * @param $customerid
	 * @return $this
	 */
	public function setCustomerid($customerid) {
		$this->customerid = $customerid;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getContactid() {
		return $this->contactid;
	}

	/**
	 * @param $contactid
	 * @return $this
	 */
	public function setContactid($contactid) {
		$this->contactid = $contactid;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGlsApiUrl() {
		return $this->glsApiUrl;
	}

	/**
	 * @param bool $url
	 * @return $this
	 */
	public function setGlsApiUrl( $url = false ) {

		$this->glsApiUrl = trim( (String)$url, "/" ) . "/";
		if( $url === false ){
			$port = "";
			if( is_numeric($this->getPort()) ){
				$port= ":" . $this->getPort();
			}

			$this->glsApiUrl = $this->getSchema() . "://" .
			$this->getHost() .
			$port .
			#"/" . trim( $this->getGlsApiLang(), "/" ) .
			"/" . trim( $this->getBaseUri(), "/" ) .
			"/" . trim( $this->getApiEnpointPath(), "/" ) . "/";
		}
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGlsApiLang() {
		return $this->glsApiLang;
	}

	/**
	 * @param $glsApiLang
	 * @return $this
	 */
	public function setGlsApiLang($glsApiLang) {
		$this->glsApiLang = $glsApiLang;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGlsApiSandbox() {
		return $this->glsApiSandbox;
	}

	/**
	 * @param mixed $glsApiSandbox
	 */
	public function setGlsApiSandbox($glsApiSandbox) {
		$this->glsApiSandbox = $glsApiSandbox;
	}

	/**
	 * @return mixed
	 */
	public function getGlsApiJsonUrl() {
		return $this->glsApiJsonUrl;
	}

	/**
	 * @param $glsApiJsonUrl
	 * @return $this
	 */
	public function setGlsApiJsonUrl($glsApiJsonUrl) {
		$this->glsApiJsonUrl = $glsApiJsonUrl;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getShipment() {
		return $this->shipment;
	}

	/**
	 * @param $shipment
	 * @return $this
	 */
	public function setShipment($shipment) {
		$this->shipment = $shipment;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getJsonConfig() {
		return $this->jsonConfig;
	}

	/**
	 * @param	string	$jsonConfig	JSON fomated string
	 * @return	$this
	 */
	public function setJsonConfig( $jsonConfig ) {
		$this->jsonConfig = $jsonConfig;
		return $this;
	}

    /**
     * @param $labelFiles
     * @return int
     */
    public function removeLabels( $labelFiles ){
        $returnValue = 0;
        foreach( $labelFiles AS $key => $labelFile ){
            if( is_writable($labelFile )){
                $returnValue = $returnValue + (Integer)unlink($labelFile);

            }
        }

        return $returnValue;
    }


    /**
     * @param   array       $labels     Array that contains serveral labels saved on the filesystem
     *                                  Note, that these files must be base64 decoded,
     *                                  because the used ZEND PDF can only handle base64_decoded files
     * @param   bool        $target
     * @return  bool|string $returnValue    returns false, if something went wrong
     *                                      returns full path of combined file
     * @throws  Zend_Pdf_Exception
     */
    public function combineLabels( $labels, $target = false )
    {

        $returnValue = false;
        $labelCount = 0;
        $labelPagesCount = 0;
        $maxLabelCount = 100;

        // just to simulate more files than given in array $labels
        #$labels500 = array();
        #for ($i = 0; $i <= 10000; $i++) {
        #    $labels500 = array_merge($labels, $labels500);
        #    if (count($labels500) >= 500) {
        #        break;
        #    }
        #}
        #$labels = $labels500;

        // init Zend PDF
        // Note, that ZEND PDF can only handle base64_decoded files!
        $zendPDF = new Zend_Pdf();
        $zendPDF->properties['encoding'] = "utf-8";

        $labelMerged = new Zend_Pdf();
        $labelMerged->properties['encoding'] = "utf-8";

        $zendPDF = new Zend_Pdf();
        $loadedLabelFiles = array();
        $firstFile = false;
        foreach ($labels AS $key => $label) {
            if($firstFile === false ){
                $firstFile = $label;
            }

            $logMessage = "clone Label No " . ++$labelCount;
            $this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );
            try {
                $loadedLabelFiles[$key] = $zendPDF::load($label);

                // add all pages from the first PDF to our new document
                foreach ($loadedLabelFiles[$key]->pages as $page) {
                    $clonedPage = clone $page;
                    $labelMerged->pages[] = $clonedPage;
                    $logMessage = "clone Page No " . ++$labelPagesCount . " for Label No " . $labelCount;
                    $this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::INFO );
                }
            } catch (Exception $e) {
                $logMessage = $e->getMessage() . " => " . $label;
                $this->getLog()->write( __METHOD__, __LINE__, $logMessage, SynergeticAgency_GlsConnector_Log::ERR );
            }

            if ($labelCount >= $maxLabelCount) {
                break;
            }
        }

        // send the merged PDF document to browser
        #header('Content-type: application/pdf');
        #echo $labelMerged->render();

        // or save it into filesystem
        if( $target !== false && file_exists(dirname($target)) ){
            $returnValue = $target;

        } else {
            $returnValue = $firstFile . ".combined.pdf";
        }
        $labelMerged->save( $returnValue );

        return $returnValue;
    }

}