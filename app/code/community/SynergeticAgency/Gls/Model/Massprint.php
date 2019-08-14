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
 * Class SynergeticAgency_Gls_Model_Massprint
 */
class SynergeticAgency_Gls_Model_Massprint extends Mage_Core_Model_Abstract{

    private $_lockFile;
    private $_lockFilePath;
    private $_debug=false;
    /**
     * Constructor - instantiate parent constructor and set path to config file
     */
	function _construct() {
		parent::_construct();
        $this->_lockFilePath =  Mage::getBaseDir('var') . DS . 'tmp' . DS . 'gls' . DS . 'massprint';
	}

    /**
     * runs normally with cron
     * background process for printing gls labels
     */
	public function run(){
        // locking with lockfile - process should run only once at a time
        try {
            $this->lock();
        } catch(Exception $e) {
            if($this->getDebug()) {
                echo "\n".$e->getMessage();
            }
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, $e->getMessage(), Zend_Log::DEBUG );
            return null;
        }

        // determine next job to work on
        $jobCollection = Mage::getModel( 'synergeticagency_gls/shipment_job' )->getCollection();

        $jobCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter( 'completed',array('eq' => 0) )
            //->addFieldToFilter( 'in_process',array('eq' => 0) )
            ->setOrder('gls_shipment_job_id', 'asc')
            ->setPageSize(1) // work on one job only
            ->setCurPage(1)
            ->load();

        if(!$jobCollection->count()) {
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, 'No available jobs to process', Zend_Log::DEBUG);
            return null;
        }

        /** @var Synergeticagency_Gls_Model_Shipment_Job $job */
        $job =  $jobCollection->getFirstItem();

        if($this->getDebug()) {
            echo "\nDATA FOUND: JobId=" . $job->getGlsShipmentJobId();
        }

        // actualize actual working status into jobs table
        if(!$job->getInProcess()) {
            $job->setInProcess(1);
            $job->save();
        }

        $glsShipmentCollection = $job->getUnprintedShipments();

        if($glsShipmentCollection->count()) {
            foreach ($glsShipmentCollection as $glsShipment) {
                $glsShipment = Mage::getModel('synergeticagency_gls/shipment')->load($glsShipment->getData('gls_shipment_id'));
                // todo think about that... this sets the sandbox flag to the current setting and not the setting when it was created...
                $glsShipment->setSandbox(Mage::getStoreConfig('gls/general/sandbox', $glsShipment->getStore()));

                if($this->getDebug()) {
                    echo "\nwork on glsShipment with id " . $glsShipment->getData('gls_shipment_id');
                }

                try {
                    $labels = $glsShipment->getLabels();
                    $targetTmpLabelFile = DS . trim($glsShipment->getLabelTempPath(), DS) . DS .
                        $glsShipment::GLS_SHIPMENT_LABEL_PDF_PREFIX_JOB . $job->getId().'_'.$glsShipment->getConsignmentId() . ".pdf";
                    $glsShipment->saveLabel(
                        $glsShipment->getConnector()->combineLabels($labels),
                        $targetTmpLabelFile
                    );
                    $glsShipment->save(); // save the label path
                    $job->setQtyItemsSuccessful((Integer)$job->getQtyItemsSuccessful() + 1); // increase successful
                } catch (Exception $e) {
                    if($this->getDebug()) {
                        echo "\nException, drop shipment " . $glsShipment->getData('gls_shipment_id') . " from job " . $glsShipment->getData('job_id');
                    }
                    $job->setQtyItemsError((Integer)$job->getQtyItemsError() + 1); // increase errors
                    $errorItems = $job->getErrorItems();
                    if(!empty($errorItems)) {
                        $errorItems = json_decode($errorItems);
                    } else {
                        $errorItems = array();
                    }
                    if($glsShipment->getMagentoShipment()) {
                        $errorItems[] = array(
                            'shipment_id' => $glsShipment->getMagentoShipment()->getId(),
                            'shipment_increment_id' => $glsShipment->getMagentoShipment()->getIncrementId(),
                        );
                    }
                    if(count($errorItems)) {
                        $job->setErrorItems(json_encode($errorItems));
                    }
                    // note, that $glsShipment->getLabels() will set printed to 1
                    // in case of an error, the shipment should not defined as "printed=1"
                    // It is also very important to drop a unsuccessful shipment from the job
                    $glsShipment->setJobId(NULL);
                    $glsShipment->setPrinted(0);
                    $glsShipment->save();
                }
                // update the job for displaying it in the current state in the backend
                $job->setQtyItemsOpen((Integer)$job->getQtyItemsOpen() - 1);
                $job->save();
                $glsShipment->clearInstance(); // freeup ram
            }// end: foreach
        }

        $glsShipments = $job->getShipments();
        if(!$glsShipments->count()) {
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, 'Mass print job has no shipments', Zend_Log::ERR );
            if($this->getDebug()) {
                echo "\nMass print job has no shipments";
            }
            $job->setInProcess(0);
            $job->setCompleted(1);
            $job->setErrorMessage(Mage::helper("synergeticagency_gls")->__('Mass print job has no shipments'));
            $job->save();
            return null;
        }
        $massprintLabels = array();
        $filesToDelete = array();
        foreach($glsShipments as $glsShipment) {
            $labelFile = $glsShipment->getPdfFile();
            if(empty($labelFile)) {
                if($this->getDebug()) {
                    echo "\nNo pdf file in shipment with id: ".$glsShipment->getId();
                }
                if(!$glsShipment->getErrorMessage()) {
                    // don't overwrite existing errors
                    $glsShipment->setErrorMessage(get_class($this).': '.Mage::helper("synergeticagency_gls")->__('No PDF-file or file is not readable'));
                    $glsShipment->setErrorCode(SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_MASS_LABEL_MISSING);
                }
                // remove it from job
                $glsShipment->setJobId(NULL);
                $glsShipment->setPrinted(0);
                $glsShipment->save();
                continue;
            }
            $file = Mage::getBaseDir().DS.$labelFile;
            if(!is_file($file) || !is_readable($file)) {
                if($this->getDebug()) {
                    echo "\nNo PDF-file or file is not readable in shipment with id: ".$glsShipment->getId();
                }
                if(!$glsShipment->getErrorMessage()) {
                    // don't overwrite existing errors
                    $glsShipment->setErrorMessage(get_class($this).': '.Mage::helper("synergeticagency_gls")->__('No PDF-file or file is not readable'));
                    $glsShipment->setErrorCode(SynergeticAgency_Gls_Exception::GLS_ERROR_CODE_MASS_LABEL_MISSING);
                }
                // remove it from job
                $glsShipment->setJobId(NULL);
                $glsShipment->setPrinted(0);
                $glsShipment->save();
                continue;
            }
            $massprintLabels[] = file_get_contents($file);
            $filesToDelete[] = $file;
        }

        if(!count($massprintLabels)) {
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, 'Mass print job has no labels to print', Zend_Log::ERR );
            if($this->getDebug()) {
                echo "\nMass print job has no labels to print";
            }
            $job->setInProcess(0);
            $job->setCompleted(1);
            $job->setErrorMessage(Mage::helper("synergeticagency_gls")->__('Mass print job has no labels to print'));
            $job->save();
            return null;
        }

        $connector = new SynergeticAgency_GlsConnector_Connector();
        try {
            $combinedLabel = $connector->combineLabels($massprintLabels);
            if($combinedLabel === false) {
                if($this->getDebug()) {
                    echo "\nPDF combination error: Check the logs for detail";
                }
                Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, 'PDF combination error: Check the logs for details', Zend_Log::ERR );
                $job->setErrorMessage(Mage::helper("synergeticagency_gls")->__('PDF combination error: Check the logs for details'));
                // leave the job in an open state - so it can run again and someone has to take care about
                $job->save();
                return null;
            }
            $targetTmpCombinedLabelFile =   DS . trim( $job->getLabelTempPath(), DS ) . DS .
                $job::GLS_SHIPMENT_JOB_LABEL_PDF_PREFIX_JOB.$job->getId() . ".pdf";
            $ret = file_put_contents($targetTmpCombinedLabelFile,$combinedLabel);
            if($ret === false) {
                if($this->getDebug()) {
                    echo "\nPDF could not be written: Check file system permissions";
                }
                Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, 'PDF could not be written: Check file system permissions', Zend_Log::ERR );
                $job->setErrorMessage(Mage::helper("synergeticagency_gls")->__('PDF could not be written: Check file system permissions'));
                // leave the job in an open state - so it can run again and someone has to take care about
                $job->save();
                return null;
            }
            // job is done
            $job->setPdfFile(trim(str_replace(Mage::getBaseDir(),'',$targetTmpCombinedLabelFile),DS));
            $job->setInProcess(0);
            $job->setCompleted(1);
            $job->save();
        } catch(Exception $e) {
            if($this->getDebug()) {
                echo "\n".$e->getMessage();
            }
            Mage::helper("synergeticagency_gls/log")->log( __METHOD__, __LINE__, 'Unspecific Error: '.$e->getMessage(), Zend_Log::ERR );
            $job->setErrorMessage(Mage::helper("synergeticagency_gls")->__('Unspecific Error:').' '.$e->getMessage());
            // leave the job in an open state - so it can run again and someone has to take care about
            $job->save();
            return null;
        }

        // cleanup
        if(count($filesToDelete)) {
            foreach($filesToDelete as $fileToDelete) {
                @unlink($fileToDelete);
            }
            foreach($glsShipments as $glsShipment) {
                $glsShipment->setPdfFile(NULL);
                $glsShipment->save();
            }
        }


        $this->unlock();
	}

    /**
     * locks lock file
     * @throws Exception
     */
    private function lock() {
        //check locking
        $this->_lockFile = fopen($this->_lockFilePath, 'w') or die('Cannot create lock file');
        if (!flock($this->_lockFile, LOCK_EX | LOCK_NB)) {
            throw new Exception("currently locked by another process");
        }
    }

    /**
     * unlocks the lock file
     */
    private function unlock() {
        fclose($this->_lockFile);
    }

    /**
     * sets the internal debug flag for testing
     * @param bool $var
     * @return $this
     */
    public function setDebug($var) {
        $this->_debug = $var;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDebug() {
        return $this->_debug;
    }

}