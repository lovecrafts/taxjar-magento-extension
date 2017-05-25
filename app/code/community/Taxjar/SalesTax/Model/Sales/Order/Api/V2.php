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

/**
 * Extend Order SOAP API V2
 * http://devdocs.magento.com/guides/m1x/api/soap/sales/salesOrder/sales_order.info.html
 */
class Taxjar_SalesTax_Model_Sales_Order_Api_V2 extends Mage_Sales_Model_Order_Api_V2
{
    /**
     * Retrieve full order information
     *
     * @param string $orderIncrementId
     * @return array
     */
    public function info($orderIncrementId)
    {
        $result = parent::info($orderIncrementId);
        $order = parent::_initOrder($orderIncrementId);

        foreach ($order->getAllItems() as $itemIndex => $item) {
            $taxClass = Mage::getModel('tax/class')->load($item->getProduct()->getTaxClassId());
            $result['items'][$itemIndex]['product_tax_code'] = $taxClass->getTjSalestaxCode();
        }

        return $result;
    }
}
