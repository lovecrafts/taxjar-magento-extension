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
 * Transaction Model
 * Sync transactions with TaxJar
 */
class Taxjar_SalesTax_Model_Transaction
{
    protected $client;
    protected $logger;

    public function __construct()
    {
        $this->client = Mage::getSingleton('taxjar/client');
        $this->logger = Mage::getSingleton('taxjar/logger');
    }

    /**
     * Check if a transaction is synced
     *
     * @param string $syncDate
     * @return array
     */
    protected function isSynced($syncDate)
    {
        if (empty($syncDate) || $syncDate == '0000-00-00 00:00:00') {
            return false;
        }
        return true;
    }

    /**
     * Build `from` address for SmartCalcs request
     *
     * @param int $storeId
     * @return array
     */
    protected function buildFromAddress($storeId)
    {
        $fromCountry = Mage::getStoreConfig('shipping/origin/country_id', $storeId);
        $fromPostcode = Mage::getStoreConfig('shipping/origin/postcode', $storeId);
        $fromState = Mage::getModel('directory/region')->load(Mage::getStoreConfig('shipping/origin/region_id', $storeId))->getCode();
        $fromCity = Mage::getStoreConfig('shipping/origin/city', $storeId);
        $fromStreet = Mage::getStoreConfig('shipping/origin/street_line1', $storeId) . Mage::getStoreConfig('shipping/origin/street_line2', $storeId);

        return array(
            'from_country' => $fromCountry,
            'from_zip' => $fromPostcode,
            'from_state' => $fromState,
            'from_city' => $fromCity,
            'from_street' => $fromStreet
        );
    }

    /**
     * Build `to` address for SmartCalcs request
     *
     * @param $order
     * @return array
     */
    protected function buildToAddress($order) {
        if ($order->getIsVirtual()) {
            $address = $order->getBillingAddress();
        } else {
            $address = $order->getShippingAddress();
        }

        $toAddress = array(
            'to_country' => $address->getCountryId(),
            'to_zip' => $address->getPostcode(),
            'to_state' => $address->getRegionCode(),
            'to_city' => $address->getCity(),
            'to_street' => $address->getData('street')
        );

        return $toAddress;
    }

    /**
     * Build line items for SmartCalcs request
     *
     * @param $order
     * @param array $items
     * @param string $type
     * @return array
     */
    protected function buildLineItems($order, $items, $type = 'order') {
        $lineItems = array();
        $parentDiscounts = $this->getParentDiscounts($items);

        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $taxClass = Mage::getModel('tax/class')->load($product->getTaxClassId());
            $discount = (float) $item->getDiscountAmount();

            if (isset($parentDiscounts[$item->getId()])) {
                $discount = $parentDiscounts[$item->getId()] ?: $discount;
            }

            $lineItem = array(
                'id' => $item->getItemId(),
                'quantity' => (int) $item->getQtyOrdered(),
                'product_identifier' => $item->getSku(),
                'description' => $item->getName(),
                'product_tax_code' => $taxClass->getTjSalestaxCode(),
                'unit_price' => (float) $item->getPrice(),
                'discount' => $discount,
                'sales_tax' => (float) $item->getTaxAmount()
            );

            if ($type == 'refund') {
                $lineItem['quantity'] = (int) $item->getQty();
            }

            $lineItems['line_items'][] = $lineItem;
        }

        if ($order->getShippingDiscountAmount() > 0) {
            $shippingDiscount = (float) $order->getShippingDiscountAmount();

            $lineItems['line_items'][] = array(
                'description' => 'Shipping Discount',
                'discount' => $shippingDiscount
            );
        }

        return $lineItems;
    }

    /**
     * Get discounts for bundle products
     *
     * @param array $items
     * @return array
     */
    protected function getParentDiscounts($items) {
        $parentDiscounts = array();

        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                $discount = (float) $item->getDiscountAmount();

                if (isset($parentDiscounts[$item->getParentItemId()])) {
                    $parentDiscounts[$item->getParentItemId()] += $discount;
                } else {
                    $parentDiscounts[$item->getParentItemId()] = $discount;
                }
            }
        }

        return $parentDiscounts;
    }

    /**
     * Return custom errors for transaction endpoints
     *
     * @return array
     */
    protected function transactionErrors()
    {
        return array(
            '400' => Mage::helper('taxjar')->__('Bad Request – Your request format is bad.'),
            '403' => Mage::helper('taxjar')->__('Forbidden – The resource requested is not authorized for use.'),
            '404' => Mage::helper('taxjar')->__('Not Found – The specified resource could not be found.'),
            '405' => Mage::helper('taxjar')->__('Method Not Allowed – You tried to access a resource with an invalid method.'),
            '406' => Mage::helper('taxjar')->__('Not Acceptable – Your request is not acceptable.'),
            '410' => Mage::helper('taxjar')->__('Gone – The resource requested has been removed from our servers.'),
            '422' => Mage::helper('taxjar')->__('Unprocessable Entity – Your request could not be processed.'),
            '429' => Mage::helper('taxjar')->__('Too Many Requests – You’re requesting too many resources! Slow down!'),
            '500' => Mage::helper('taxjar')->__('Internal Server Error – We had a problem with our server. Try again later.'),
            '503' => Mage::helper('taxjar')->__('Service Unavailable – We’re temporarily offline for maintenance. Try again later.')
        );
    }
}
