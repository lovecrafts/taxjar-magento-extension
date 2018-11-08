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

class Taxjar_SalesTax_Model_Export_Cron extends Taxjar_SalesTax_Model_Export_Abstract
{
    /**
     * @return string
     */
    public function getFilterBy()
    {
        return 'updated_at';
    }

    /**
     * @return false|string
     */
    public function getFromDate()
    {
        $from = Mage::getStoreConfig('tax/taxjar/cron_orders_export_for_time') ?: 1440;
        return date($this->getFormat(), time() - $from * 60);
    }

    /**
     * @return false|string
     */
    public function getToDate()
    {
        return date($this->getFormat(), time());
    }
}