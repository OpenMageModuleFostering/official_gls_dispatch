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
 * @package    SynergeticAgency\Gls\Helper
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GLS helper log
 *
 * @category    SynergeticAgency
 * @package     SynergeticAgency_Gls
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_Gls_Helper_Log extends Mage_Core_Helper_Abstract {

	const GLS_LOG_FILE_NAME = "SynergeticAgency_Gls.log";
	const GLS_API_LOG_FILE_NAME = "SynergeticAgency_GlsApi.log";

	/**
	 * Container, if logging should be forced or not
     *
     * @var bool
	 */
	var $forceLog = false;

    /**
     * If gls/general/debug_enabled is enabled or the given level not equals to Zend_Log::DEBUG,
     * this function logs all messages into the $this::GLS_LOG_FILE_NAME file
     *
     * @param $method
     * @param $line
     * @param $message
     * @param int $level
     */
	public function log( $method, $line, $message, $level = Zend_Log::DEBUG ){
		if( Mage::getStoreConfig('gls/general/debug_enabled') || $level !== Zend_Log::DEBUG ){
			$message = $method . " @ line " . $line . ": " . $message;
			Mage::log( $message, $level, $this::GLS_LOG_FILE_NAME, $this->isForceLog() );
		}
	}

    /**
     * If the configuration "gls/general/debug_enabled" is set to '1' or the given level not equals to Zend_Log::DEBUG,
     * this function logs all messages into the $this::GLS_API_LOG_FILE_NAME file
     *
     * @param $method
     * @param $line
     * @param $message
     * @param int $level    use corresponding Zend_Log levels such as DEBUG, ERR, ALERT, etc
     */
    public function logApi( $method, $line, $message, $level = Zend_Log::DEBUG ){
        if( Mage::getStoreConfig('gls/general/debug_enabled') == '1' || $level !== Zend_Log::DEBUG ){
			$message = $method . " @ line " . $line . ": " . $message;
			Mage::log( $message, $level, $this::GLS_API_LOG_FILE_NAME, $this->isForceLog() );
		}
	}

    /**
     * If the configuration "gls/general/debug_enabled" is set to '1',
     * this function logs all messages into the $this::GLS_API_LOG_FILE_NAME file
     * and adds an Error into magentos session with several information about the exception
     *
     * @param Exception $e
     * @param $method
     * @param $line
     * @param $level
     * @param $message
     * @param $code
     */
    public function logException( Exception $e, $method, $line, $level, $message, $code ){

        $exceptionMessage = "";
        if( Mage::getStoreConfig('gls/general/debug_enabled') ){
            $exceptionMessage = sprintf(Mage::helper('synergeticagency_gls')->__('(Exception: %s)'), $e->getMessage()) . " ";
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('synergeticagency_gls')->__($message) . " " .
            $exceptionMessage .
            sprintf(Mage::helper('synergeticagency_gls')->__('(Error-Code: %s)'), $code)
        );
        $this->log( $method, $line, "Exception: " . $e->getMessage(), $level );
    }



	/**
	 * Getter function for the member var forceLog
     * @return boolean
	 */
	public function isForceLog() {
		return $this->forceLog;
	}

	/**
     * Setter function for the member var forceLog
     *
	 * @param $forceLog
	 * @return $this
	 */
	public function setForceLog($forceLog) {
		$this->forceLog = (boolean)$forceLog;
		return $this;
	}
}
