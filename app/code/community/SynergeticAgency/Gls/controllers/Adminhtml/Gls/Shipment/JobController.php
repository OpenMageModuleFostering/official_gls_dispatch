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
* to info@synergetic.ag so we can send you a copy immediately.
*
*
* @category   SynergetigAgency
* @package    SynergeticAgency\Gls\controllers\Adminhtml\Gls
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_Adminhtml_Gls_Shipment_JobController
 */
class SynergeticAgency_Gls_Adminhtml_Gls_Shipment_JobController extends Mage_Adminhtml_Controller_Action {

    /**
     * Initialising the sales GLS job view
     */
    public function indexAction()
    {
        $this->_title($this->__('GLS'))->_title($this->__('GLS mass action list'));
        $this->loadLayout();
        $this->_setActiveMenu('sales/sales');
        $this->_addContent($this->getLayout()->createBlock('synergeticagency_gls/adminhtml_sales_gls_shipment_job'));
        $this->renderLayout();
    }

    /**
     * Initialising the sales GLS job grid view
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('synergeticagency_gls/adminhtml_sales_gls_shipment_job_grid')->toHtml()
        );
    }

    /**
     * echos the current state of the grid as json for an ajax request
     */
    public function ajaxAction() {
        $glsShipmentJobIds = $this->getRequest()->getParam('jobIds');
        if(!is_array($glsShipmentJobIds) || !count($glsShipmentJobIds)) {
            echo json_encode(array());
            die();
        }
        $jobCollection = Mage::getModel('synergeticagency_gls/shipment_job')->getCollection();
        $jobCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('gls_shipment_job_id',array('in' => $glsShipmentJobIds))
            ->load();
        $returnArray = array();
        if($jobCollection->count()) {
            foreach($jobCollection as $job) {
                // can not be done like this bc the state of the job must be submitted
                $noUpdates = 0;
                if($job->getCompleted() && !$job->getPrinted() && !$job->getQtyItemsSuccessful()) {
                    $noUpdates = 1;
                } elseif($job->getCompleted() && $job->getPrinted()) {
                    $noUpdates = 1;
                }
                $block = $this->getLayout()->createBlock('synergeticagency_gls/adminhtml_sales_gls_shipment_job_grid_renderer_action');
                $button = $block->render($job);
                $errorItems = $this->getLayout()->createBlock('synergeticagency_gls/adminhtml_sales_gls_shipment_job_grid_renderer_erroritems')->render($job);
                $returnArray[] = array(
                    'id' => $job->getId(),
                    'status' => $job->getStatus(),
                    'printed' => $job->getPrinted() ? Mage::helper('synergeticagency_gls')->__('Yes') : Mage::helper('synergeticagency_gls')->__('No'),
                    'completed' => $job->getCompleted() ? Mage::helper('synergeticagency_gls')->__('Yes') : Mage::helper('synergeticagency_gls')->__('No'),
                    'error_messages' => $job->getErrorMessages(),
                    'error_items' => $errorItems,
                    'no_updates' => $noUpdates,
                    'action' => $button
                );
            }
        }
        echo json_encode($returnArray);
        die(); // not needed magento takes care about but just to be sure
    }

    /**
     * download of combined pdf
     * @return null
     * @throws Exception
     */
    public function printAction() {
        $jobId = $this->getRequest()->getParam('id');
        if(empty($jobId)) {
            Mage::getSingleton('adminhtml/session')->addError('Mass job could not be loaded');
            $this->_redirectReferer();
            return null;
        }
        $job = Mage::getModel('synergeticagency_gls/shipment_job')->load($jobId);
        if(!$job || !$job->getId() || $job->getPrinted() || !$job->getCompleted() || !$job->getPdfFile()) {
            Mage::getSingleton('adminhtml/session')->addError('Mass job could not be loaded');
            $this->_redirectReferer();
            return null;
        }

        $pdfFile = Mage::getBaseDir().DS.$job->getPdfFile();
        if(!is_file($pdfFile) || !is_readable($pdfFile)) {
            Mage::getSingleton('adminhtml/session')->addError('PDF file invalid');
            $this->_redirectReferer();
            return null;
        }

        $job->setPrinted(1);
        $job->save();

        $name = pathinfo($pdfFile, PATHINFO_BASENAME);
        $this->_prepareDownloadResponse($name, array(
            'type'  => 'filename',
            'value' => $pdfFile,
        ),
        'application/pdf'
        );
    }
}
