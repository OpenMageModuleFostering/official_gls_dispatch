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
 * Class SynergeticAgency_Gls_Exception
 */
class SynergeticAgency_Gls_Exception extends Exception
{

    /** Magento shipment to the GLS shipment is missing */
    const GLS_ERROR_CODE_MAGE_SHIPMENT_MISSING              = '00001';
    /** label is already printed and can not be printed again */
    const GLS_ERROR_CODE_LABEL_ALREADY_PRINTED              = '00002';
    /** The GLS basic configuration is incomplete */
    const GLS_ERROR_CODE_CONFIGURATION_INCOMPLETE           = '00003';
    /** The GLS-API-Connector returned an error */
    const GLS_ERROR_CODE_API_CONNECTOR_ERROR                = '00005';
    /** GLS API didin't return a correct consignment id */
    const GLS_ERROR_CODE_CONSIGNMENTID_MISSING              = '00006';
    /** edit of gls shipment not possible -- already printed or invalid data */
    const GLS_ERROR_CODE_SHIPMENT_EDIT_IMPOSSIBLE           = '00007';
    /** GLS shipment could not be saved */
    const GLS_ERROR_CODE_SHIPMENT_NOT_SAVED                 = '00009';
    /** GLS shipment could not be created */
    const GLS_ERROR_CODE_SHIPMENT_NOT_CREATED               = '00010';
    /** PDF label could not be written to filesystem */
    const GLS_ERROR_CODE_LABEL_IO_ERROR                     = '00011';
    /** The GLS label retuned is no valid PDF */
    const GLS_ERROR_CODE_LABEL_INVALID                      = '00013';
    /** in mass print no valid label was returned */
    const GLS_ERROR_CODE_MASS_LABEL_MISSING                 = '00080';
    /** unefined error */
    const GLS_ERROR_CODE_UNDEFINED                          = '99999';


    /** @var string */
    protected $code;

    /**
     * @param null $message
     * @param string $code
     */
    public function __construct($message = null, $code = '')
    {
        parent::__construct($message, 0);
        $this->code = $code;
    }
}
