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
 * @package     SynergeticAgency_GlsConnector_Connector
 * @copyright  Copyright (c) 2006-2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with GLS API
 *
 * @category    SynergetigAgency
 * @package     SynergeticAgency_GlsConnector_Log
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_GlsConnector_Log
{

	const EMERG = 0;
	const ALERT = 1;
	const CRIT = 2;
	const ERR = 3;
	const WARN = 4;
	const NOTICE = 5;
	const INFO = 6;
	const DEBUG = 7;

	/**
	 * @var string
	 */
	private $logFile = "/tmp/log/SynergeticAgency_GlsConnector_Connector.log";

	/**
	 * @var bool
	 */
	private $on = false;

	/**
	 * @var bool
	 */
	private $flat = false;

	/**
	 * @var bool
	 */
	private $echo = false;

	/**
	 * @var	array
	 */
	private $logMessages = array();

	/**
	 *
	 */
	function __construct() {}


	/**
	 * @param $method
	 * @param $line
	 * @param $message
	 * @param int $level
	 */
	public function write( $method, $line, $message, $level = SynergeticAgency_GlsConnector_Log::DEBUG ){

		$this->addLogMessage( $method, $line, $message, $level );

        if(!file_exists($this->getLogFile()) ){
            // suppress errors here just in case
            $handle = @fopen($this->getLogFile(), "w+");
            @fclose($handle);
        }

        if( $this->isOn() ){
            if(file_exists($this->getLogFile()) && is_writable($this->getLogFile()) ){
                if( $handle = fopen($this->getLogFile(), "a+") ) {
                    $date = date('c');

                    if( $this->isFlat() ){
                        $message = "\n" . $date . " " . $this->levelToString($level) . " (" . $level . "): " . $method . " @ Line " . $line . ": " . strip_tags( preg_replace("/\r|\n/", "", $message) );
                        if( $this->isEcho() ){
                            echo $message;
                        }
                    } else {
                        $message = "\n" . $date . " " . $this->levelToString($level) . " (" . $level . "): " . $method . " @ Line " . $line . ": " . $message;
                        if( $this->isEcho() ){
                            echo $message;
                        }
                    }

                    fwrite( $handle, $message );
                    fclose($handle);
                }
            } else {
                error_log("GLS Connector: Logfile: ".$this->getLogFile() . " is Not writable!!!");
            }
        } else {
            //error_log("GLS Connector: logging is NOT activated: " . (Integer)$this->isOn());
        }
	}

	/**
	 * @return $this
	 */
	public function activate(){
		$this->setOn( true );
		return $this;
	}

	/**
	 *
	 */
	public function deactivate(){
		$this->setOn( false );
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLogFile() {
        if(is_null($this->logFile)) {
            // fallback
            $file = join(DIRECTORY_SEPARATOR,
                array(
                    DIRECTORY_SEPARATOR . trim(dirname(__FILE__), DIRECTORY_SEPARATOR),
                    "Log",
                    "Connector.log",
                )
            );
            $this->setLogFile( $file );
        }
		return $this->logFile;
	}

	/**
	 * @param $logFile
	 */
	public function setLogFile( $logFile ){

		if( is_writable($logFile) ){
			$this->logFile = $logFile;
			$this->setOn( true );
		} else {
			if ($handle = fopen($logFile, "a")) {
				fwrite( $handle, "\n\n" . date('c') . " " . $this->levelToString(SynergeticAgency_GlsConnector_Log::INFO) . " (" . SynergeticAgency_GlsConnector_Log::INFO . "): " . __METHOD__ . " @ line " . __LINE__ . ": initial creation of logfile " . $logFile . "\n\n" );
				fclose($handle);
			}
		}
	}

	/**
	 * @return boolean
	 */
	public function isOn() {
		return $this->on;
	}

	/**
	 * @param $on
	 * @return $this
	 */
	public function setOn($on) {
		$this->on = $on;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isFlat() {
		return $this->flat;
	}

	/**
	 * @param $flat
	 * @return $this
	 */
	public function setFlat($flat) {
		$this->flat = $flat;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isEcho() {
		return $this->echo;
	}

	/**
	 * @param $echo
	 * @return $this
	 */
	public function setEcho($echo) {
		$this->echo = $echo;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getLogMessages() {
		return $this->logMessages;
	}

	/**
	 * @param $logMessages
	 * @return $this
	 */
	public function setLogMessages( $logMessages ) {
		$this->logMessages = $logMessages;
		return $this;
	}

	/**
	 * @param $method
	 * @param $line
	 * @param $message
	 * @param $level
	 * @return $this
	 */
	public function addLogMessage( $method, $line, $message, $level = SynergeticAgency_GlsConnector_Log::DEBUG ) {
		$cnt = count( $this->logMessages ) + 1;
		$this->logMessages[$cnt]['method'] = $method;
		$this->logMessages[$cnt]['line'] = $line;
		$this->logMessages[$cnt]['message'] = $message;
		$this->logMessages[$cnt]['level'] = $level;
		return $this;
	}

	/**
	 * @param $level
	 * @return string
	 */
	private function levelToString( $level ){

		switch( $level ){
			case $this::EMERG:
				$returnValue = "EMERG";
				break;
			case $this::ALERT:
				$returnValue = "ALERT";
				break;
			case $this::CRIT:
				$returnValue = "CRIT";
				break;
			case $this::ERR:
				$returnValue = "ERR";
				break;
			case $this::WARN:
				$returnValue = "WARN";
				break;
			case $this::NOTICE:
				$returnValue = "NOTICE";
				break;
			case $this::INFO:
				$returnValue = "INFO";
				break;
			case $this::DEBUG:
				$returnValue = "DEBUG";
				break;
			default:
				$returnValue = "UNKNOWN";
				break;
		}

		return $returnValue;
	}
}