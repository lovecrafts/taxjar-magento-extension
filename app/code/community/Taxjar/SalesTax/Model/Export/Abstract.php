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

abstract class Taxjar_SalesTax_Model_Export_Abstract
{
    const DEFAULT_CURRENCY_CODE = 'USD';
    const DEFAULT_COUNTRY = 'US';

    private $format = 'Y-m-d H:i:s';

    protected $logger;
    protected $from;
    protected $to;

    public function __construct()
    {
        $this->logger = Mage::getSingleton('taxjar/logger')->setFilename('transactions.log');
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $apiKey = trim(Mage::getStoreConfig('tax/taxjar/apikey'));

        if (!$apiKey) {
            $this->logger->log(
                'Error: ' . Mage::helper('taxjar')->__(
                    'Could not sync transactions with TaxJar. Please make sure you have an API key.'
                ),
                'error'
            );

            return;
        }

        $statesToMatch = array('complete', 'closed');
        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();

        $this->logger->log('Initializing TaxJar transaction sync');

        if (!empty($fromDate)) {
            $fromDate = (new DateTime($fromDate));
        } else {
            $fromDate = (new DateTime());
            $fromDate = $fromDate->sub(new DateInterval('P1D'));
        }

        if (!empty($toDate)) {
            $toDate = (new DateTime($toDate));
        } else {
            $toDate = (new DateTime());
        }

        if ($fromDate > $toDate) {
            $this->logger->log(
                'Error: ' . Mage::helper('taxjar')->__("To date can't be earlier than from date."),
                'error'
            );

            return;
        }

        $this->logger->log(
            'Finding ' . implode(', ', $statesToMatch) . ' transactions from ' . $fromDate->format('m/d/Y') . ' - ' .
            $toDate->format('m/d/Y')
        );

        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('state', array('in' => $statesToMatch))
            ->addAttributeToFilter('order_currency_code', array('eq' => self::DEFAULT_CURRENCY_CODE))
            ->addAttributeToFilter(
                $this->getFilterBy(),
                array('from' => $fromDate->format($this->getFormat()), 'to' => $toDate->format($this->getFormat()))
            );
        $orders->getSelect()
            ->joinLeft(
                ['address' => 'sales_flat_order_address'],
                'main_table.entity_id = address.parent_id AND
                address.address_type = IF(main_table.is_virtual = 1, "billing", "shipping")',
                [
                    'country_id',
                    'postcode',
                    'region',
                    'region_id',
                    'city',
                    'street'
                ]
            )
            ->where('address.country_id = ?', self::DEFAULT_COUNTRY);

        $this->logger->log($orders->getSize() . ' transaction(s) found');
        if (!$orders->getSize()) {
            $this->logger->log('no orders found for import');

            return;
        }

        $orderTransaction = Mage::getSingleton('taxjar/transaction_order');
        $refundTransaction = Mage::getSingleton('taxjar/transaction_refund');

        foreach ($orders->getItems() as $order) {
            $orderTransaction->build($order);
            $orderTransaction->push();

            $creditMemos = $order->getCreditmemosCollection();

            foreach ($creditMemos as $creditMemo) {
                $refundTransaction->build($order, $creditMemo);
                $refundTransaction->push();
            }
        }
    }

    /**
     * @param string $fromDate
     */
    public function setFromDate(string $fromDate)
    {
        $this->from = $fromDate;
    }

    /**
     * @param string $toDate
     */
    public function setToDate(string $toDate)
    {
        $this->to = $toDate;
    }

    /**
     * @return string
     */
    abstract public function getFilterBy();

    /**
     * @return string
     */
    abstract public function getFromDate();

    /**
     * @return string
     */
    abstract public function getToDate();
}