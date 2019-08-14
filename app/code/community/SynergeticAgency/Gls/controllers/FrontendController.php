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
* @package    SynergeticAgency\Gls\controllers\Adminhtml
* @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Class SynergeticAgency_Gls_FrontendController
 */
class SynergeticAgency_Gls_FrontendController extends Mage_Core_Controller_Front_Action
{
    /**
     * Shows the map for the parcelshop search
     */
    public function parcelshopmapAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * providing billing country and postcode in checkout step shipping
     * ajax call is done to fetch this
     */
    public function parcelShopMapParamsAction()
    {
        $billingAddress = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getData();

        $parcelShopMapParams = array(
            'country_id' => $billingAddress['country_id'],
            'postcode' => $billingAddress['postcode']
        );
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($parcelShopMapParams));
        $this->getResponse()->setHeader('Content-type', 'application/json');
    }

}