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
 * Refund Transaction Model
 * Sync refund transactions with TaxJar
 */
class Taxjar_SalesTax_Model_Transaction_Refund extends Taxjar_SalesTax_Model_Transaction
{
   /**
     * Build a refund transaction
     *
     * @param $order
     * @param $creditmemo
     * @return array
     */
    public function build($order, $creditmemo) {
        $subtotal = (float) $creditmemo->getSubtotal();
        $shipping = (float) $creditmemo->getShippingAmount();
        $discount = (float) $creditmemo->getDiscountAmount();
        $salesTax = (float) $creditmemo->getTaxAmount();

        $this->originalOrder = $order;
        $this->originalRefund = $creditmemo;

        $refund = array(
            'plugin' => 'magento',
            'transaction_id' => $creditmemo->getIncrementId() . '-refund',
            'transaction_reference_id' => $order->getIncrementId(),
            'transaction_date' => $creditmemo->getCreatedAt(),
            'amount' => $subtotal + $shipping - abs($discount),
            'shipping' => $shipping,
            'sales_tax' => $salesTax
        );

        $this->request = array_merge(
            $refund,
            $this->buildFromAddress($order->getStoreId()),
            $this->buildToAddress($order),
            $this->buildLineItems($order, $creditmemo->getAllItems(), 'refund')
        );

        return $this->request;
    }

    /**
     * Push refund transaction to SmartCalcs
     *
     * @param string|null $forceMethod
     * @return void
     */
    public function push($forceMethod = null) {
        $refundUpdatedAt = $this->originalRefund->getUpdatedAt();
        $refundSyncedAt = $this->originalRefund->getTjSalestaxSyncDate();

        if (!$this->isSynced($refundSyncedAt)) {
            $method = 'POST';
        } else {
            if ($refundSyncedAt < $refundUpdatedAt) {
                $method = 'PUT';
            } else {
                $this->logger->log('Refund #' . $this->request['transaction_id']
                                        . ' for order #' . $this->request['transaction_reference_id']
                                        . ' not updated since last sync', 'skip');
                return;
            }
        }

        if ($forceMethod) {
            $method = $forceMethod;
        }

        try {
            $this->logger->log('Pushing refund / credit memo #' . $this->request['transaction_id']
                                    . ' for order #' . $this->request['transaction_reference_id']
                                    . ': ' . json_encode($this->request), $method);

            if ($method == 'POST') {
                $response = $this->client->postResource('refunds', $this->request, $this->transactionErrors());
                $this->logger->log('Refund #' . $this->request['transaction_id'] . ' created: ' . json_encode($response), 'api');
                $this->originalRefund->addComment('Refund / credit memo created in TaxJar.')->save();
                $this->originalOrder->addStatusHistoryComment('Refund / credit memo created in TaxJar.')->save();
            } else {
                $response = $this->client->putResource('refunds', $this->request['transaction_id'], $this->request, $this->transactionErrors());
                $this->logger->log('Refund #' . $this->request['transaction_id'] . ' updated: ' . json_encode($response), 'api');
            }

            $this->originalRefund->setTjSalestaxSyncDate(gmdate('Y-m-d H:i:s'))->save();
        } catch (Exception $e) {
            $this->logger->log('Error: ' . $e->getMessage(), 'error');

            $errorStatusCode = array_search($e->getMessage(), $this->transactionErrors());

            // Retry push for not found records using POST
            if (!$forceMethod && $method == 'PUT' && $errorStatusCode == 404) {
                $this->logger->log('Attempting to create refund / credit memo #' . $this->request['transaction_id'], 'retry');
                return $this->push('POST');
            }

            // Retry push for existing records using PUT
            if (!$forceMethod && $method == 'POST' && $errorStatusCode == 422) {
                $this->logger->log('Attempting to update refund / credit memo #' . $this->request['transaction_id'], 'retry');
                return $this->push('PUT');
            }
        }
    }
}
