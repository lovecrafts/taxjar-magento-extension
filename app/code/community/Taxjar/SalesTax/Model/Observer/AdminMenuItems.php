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

class TaxJar_SalesTax_Model_Observer_AdminMenuItems
{
    public function execute(Varien_Event_Observer $observer)
    {
        $connected = Mage::getStoreConfig('tax/taxjar/connected');

        if (!$connected) {
            $config = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode();
            unset($config->menu->sales->children->tax->children->taxjar_salestax_nexus);
            Mage::getSingleton('admin/config')->getAdminhtmlConfig()->setXml($config);
        }

        return $this;
    }
}
