<?php
/**
 * Taxjar_SalesTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Taxjar
 * @package    Taxjar_SalesTax
 * @copyright  Copyright (c) 2017 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

use Taxjar_SalesTax_Block_Adminhtml_Enabled as Enabled;
use Taxjar_SalesTax_Block_Adminhtml_Backup as Backup;

class Taxjar_SalesTax_Model_Observer_ConfigChanged
{
    use Taxjar_SalesTax_Trait_ConfigScope;

    const CONFIG_ENABLED = 'tax/taxjar/enabled';
    const CONFIG_BACKUP  = 'tax/taxjar/backup';

    /**
     * @param Varien_Event_Observer $observer
     *
     * @throws Mage_Core_Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $this->_updateSmartcalcs();
        $this->_updateBackupRates();
    }

    /**
     * @throws Mage_Core_Exception
     */
    private function _updateSmartcalcs()
    {
        // UPDATE NOTE: check config for store or website if selected,
        // fallback to admin scope
        $enabled = $this->getConfigValue(self::CONFIG_ENABLED);
        // load cache for currently selected scope
        $prevEnabled = Mage::app()->getCache()->load(Enabled::CACHE_KEY . '_' . $this->getScopeCode());

        if (isset($prevEnabled)) {
            if ($prevEnabled !== $enabled && (int) $enabled === 1) {
                Mage::dispatchEvent('taxjar_salestax_import_categories');
                Mage::dispatchEvent('taxjar_salestax_import_data');
            }
        }
        // END UPDATE
    }

    /**
     * @throws Mage_Core_Exception
     */
    private function _updateBackupRates()
    {
        // UPDATE NOTE: check config for store or website if selected,
        // fallback to admin scope
        $enabled = $this->getConfigValue(self::CONFIG_BACKUP);
        // load cache for currently selected scope
        $prevEnabled = Mage::app()->getCache()->load(Backup::CACHE_KEY . '_' . $this->getScopeCode());

        if (isset($prevEnabled)) {
            if ($prevEnabled !== $enabled) {
                Mage::dispatchEvent('taxjar_salestax_import_categories');
                Mage::dispatchEvent('taxjar_salestax_import_data');
                Mage::dispatchEvent('taxjar_salestax_import_rates');
            }
        }
        // END UPDATE
    }
}
