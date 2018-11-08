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

class Taxjar_SalesTax_Model_Export_OnDemand  extends Taxjar_SalesTax_Model_Export_Abstract
{
    /**
     * @return string
     */
    public function getFilterBy()
    {
        return 'created_at';
    }

    /**
     * @return string
     */
    public function getFromDate()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getToDate()
    {
        return $this->to;
    }
}
