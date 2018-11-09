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

    /** @var Varien_Db_Adapter_Interface */
    protected $readConnection;
    /** @var Varien_Db_Adapter_Interface */
    protected $writeConnection;

    public function __construct()
    {
        $this->client = Mage::getSingleton('taxjar/client');
        $this->logger = Mage::getSingleton('taxjar/logger')->setFilename('transactions.log');
        $this->logger = Mage::getSingleton('taxjar/logger');
        $this->readConnection = Mage::getSingleton('core/resource')->getConnection(
            Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE
        );
        $this->writeConnection = Mage::getSingleton('core/resource')->getConnection(
            Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE
        );
    }

    /**
     * @param string $entityId
     * @param string $tableName
     *
     * @return PDO_Statement|Zend_Db_Statement
     * @throws Zend_Db_Statement_Exception
     */
    public function getSyncedAt($entityId, $tableName)
    {
        $select = $this->readConnection
            ->select()
            ->from($tableName, array('synced_at'))
            ->where('id = ?', $entityId);

        return $this->readConnection
            ->query($select)
            ->fetchColumn();
    }

    /**
     * @param string $entityId
     * @param string $tableName
     */
    public function setSyncedAt($entityId, $tableName)
    {
        $this->writeConnection->insertOnDuplicate(
            $tableName,
            array(
                'id'        => $entityId,
                'synced_at' => date('Y-m-d H:i:s', time())
            ),
            array('synced_at')
        );
    }

    /**
     * @deprecated
     *
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
    protected function buildToAddress($order)
    {
        /**
         * don't load address object as it's data is already present in the order
         * @see Taxjar_SalesTax_Model_Export_Abstract::execute
         */
        $address = Mage::getModel(
            'sales/order_address',
            array(
                'country_id' => $order->getCountryId(),
                'region'     => $order->getRegion(),
                'region_id'  => $order->getRegionId()
            )
        );

        $toAddress = array(
            'to_country' => $order->getCountryId(),
            'to_zip'     => $order->getPostcode(),
            'to_state'   => $address->getRegionCode(),
            'to_city'    => $order->getCity(),
            'to_street'  => $order->getStreet()
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
    protected function buildLineItems($order, $items, $type = 'order')
    {
        $lineItems = array();
        $parentDiscounts = $this->getParentAmounts('discount', $items, $type);
        $parentTaxes = $this->getParentAmounts('tax', $items, $type);

        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if (method_exists($item, 'getOrderItem') && $item->getOrderItem()->getParentItemId()) {
                continue;
            }

            // don't load whole product object just to select one tax class attribute,
            // using single select instead
            $productTaxClassId = Mage::getResourceSingleton('catalog/product')->getAttributeRawValue(
                $item->getProductId(),
                'tax_class_id',
                $order->getStoreId()
            );

            $itemId = $item->getOrderItemId() ? $item->getOrderItemId() : $item->getItemId();
            $discount = (float) $item->getDiscountAmount();
            $tax = (float) $item->getTaxAmount();

            if (isset($parentDiscounts[$itemId]) && $parentDiscounts[$itemId] > 0) {
                $discount = $parentDiscounts[$itemId] ?: $discount;
            }

            if (isset($parentTaxes[$itemId]) && $parentTaxes[$itemId] > 0) {
                $tax = $parentTaxes[$itemId] ?: $tax;
            }

            if ($productTaxClassId) {
                $taxClass = Mage::getModel('tax/class')->load($productTaxClassId);
                $taxCode = $taxClass->getTjSalestaxCode();
            } else {
                $taxCode = '99999';
            }

            $lineItem = array(
                'id' => $itemId,
                'quantity' => (int) $item->getQtyOrdered(),
                'product_identifier' => $item->getSku(),
                'description' => $item->getName(),
                'product_tax_code' => $taxCode,
                'unit_price' => (float) $item->getPrice(),
                'discount' => $discount,
                'sales_tax' => $tax
            );

            if ($type == 'refund' && method_exists($item, 'getOrderItem')) {
                $orderItem = $item->getOrderItem();
                $lineItem['quantity'] = (int) $orderItem->getQtyRefunded();
                $lineItem['unit_price'] = $orderItem->getAmountRefunded() / $lineItem['quantity'];
            }

            $lineItems['line_items'][] = $lineItem;
        }

        return $lineItems;
    }

    /**
     * Get parent amounts (discounts, tax, etc) for bundle products
     *
     * @param string $attr
     * @param array $items
     * @param string $type
     * @return array
     */
    protected function getParentAmounts($attr, $items, $type = 'order')
    {
        $parentAmounts = array();

        foreach ($items as $item) {
            $parentItemId = null;

            if ($item->getParentItemId()) {
                $parentItemId = $item->getParentItemId();
            }

            if (method_exists($item, 'getOrderItem') && $item->getOrderItem()->getParentItemId()) {
                $parentItemId = $item->getOrderItem()->getParentItemId();
            }

            if ($parentItemId) {
                switch ($attr) {
                    case 'discount':
                        $amount = (float) (($type == 'order') ? $item->getDiscountAmount() : $item->getDiscountRefunded());
                        break;
                    case 'tax':
                        $amount = (float) (($type == 'order') ? $item->getTaxAmount() : $item->getTaxRefunded());
                        break;
                }

                if (isset($parentAmounts[$parentItemId])) {
                    $parentAmounts[$parentItemId] += $amount;
                } else {
                    $parentAmounts[$parentItemId] = $amount;
                }
            }
        }

        return $parentAmounts;
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
