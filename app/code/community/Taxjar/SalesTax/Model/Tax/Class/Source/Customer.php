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
 * @copyright  Copyright (c) 2016 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Customer Tax Classes
 *
 * @author Taxjar (support@taxjar.com)
 */
class Taxjar_SalesTax_Model_Tax_Class_Source_Customer
{
    public function toOptionArray()
    {
        $output = array();
        $customerClasses = Mage::getModel('tax/class')
            ->getCollection()
            ->addFieldToFilter('class_type', 'CUSTOMER')
            ->load();

        foreach($customerClasses as $customerClass) {
            $output[] = array('value' => $customerClass->getClassId(), 'label' => $customerClass->getClassName());
        }

        return $output;
    }
}
