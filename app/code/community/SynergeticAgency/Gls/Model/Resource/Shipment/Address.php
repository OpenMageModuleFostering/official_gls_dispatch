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
 * @package    SynergeticAgency\Gls\Model\Resource\Shipment
 * @copyright  Copyright (c) 2016 synergetic agency AG (http://agency.synergetic.ag)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GLS shipment address resource model
 *
 * @category    SynergeticAgency
 * @package     SynergeticAgency_Gls
 * @author      PHP WebDevelopment <php.webdevelopment@synergetic.ag>
 */
class SynergeticAgency_Gls_Model_Resource_Shipment_Address extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * This Constructor initiates synergeticagency_gls/shipment_address using the magic _init function of Mage_Core_Model_Abstract
     */
    protected function _construct() {
        $this->_init('synergeticagency_gls/shipment_address', 'gls_shipment_address_id');
    }

    /**
     * Load data by specified glsShipmentId
     *
     * @param int $glsShipmentId
     * @return false|array
     */
    public function loadByGlsShipmentId($glsShipmentId)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('gls_shipment_id=:gls_shipment_id');

        $binds = array(
            'gls_shipment_id' => $glsShipmentId
        );

        return $adapter->fetchRow($select, $binds);
    }
}