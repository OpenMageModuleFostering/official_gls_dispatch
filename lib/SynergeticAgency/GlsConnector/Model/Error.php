<?php
# ToDo add license text here

/**
 * Class to work with archives
 *
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Model
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_GlsConnector_Model_Error
{

	private $hasError = false;

	/**
	 * @var
	 */
	private $message;

	/**
	 * @var
	 */
	private $modelState;

	/**
	 * @var	array	the error object converted into an array i.e. to generate correct GLS-JSON
	 */
	private $glsArray;

	/**
	 *
	 */
	function __construct() {

	}

	/**
	 * @return array
	 */
	private function toGlsArray() {
		$returnValue = array();
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
	 * @return boolean
	 */
	public function hasError() {
		return $this->hasError;
	}

	/**
	 * @param $hasError
	 * @return $this
	 */
	public function setHasError($hasError) {
		$this->hasError = $hasError;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @param $message
	 * @return $this
	 */
	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getModelState( $flat = false ) {
		$returnValue = $this->modelState;

		if( $flat === true ){

			$returnValue = "";
            if(is_array($this->modelState)) {
                foreach ($this->modelState as $key => $values) {
                    if (is_array($values)) {
                        foreach ($values as $num => $message) {
                            $returnValue = trim($returnValue . " " . $message, " ");
                        }
                    }
                }
            }
		}
		return $returnValue;
	}

	/**
	 * @param $modelState
	 * @return $this
	 */
	public function setModelState($modelState) {
		$this->modelState = $modelState;
		return $this;
	}


}