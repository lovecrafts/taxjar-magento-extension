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
 * TaxJar Admin Router
 * Connect and disconnect TaxJar accounts
 */
class Taxjar_SalesTax_Adminhtml_Tax_TransactionController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Tax'))
             ->_title($this->__('Sync Transactions'));

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('taxjar/adminhtml_tax_transaction')
                    ->setData('action', $this->getUrl('*/tax_transaction/backfill'))
            )
            ->renderLayout();
    }

    public function backfillAction()
    {
        try {
            $logger = Mage::getSingleton('taxjar/logger');
            $logger->record();

            Mage::dispatchEvent('taxjar_salestax_backfill_transactions');

            $responseContent = array(
                'success' => true,
                'error_message' => '',
                'result' => $logger->playback(),
            );
        } catch (Exception $e) {
            $responseContent = array(
                'success' => false,
                'error_message' => $e->getMessage(),
            );
        }

        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($responseContent)
        );
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/tax/taxjar_salestax_transaction')
            ->_addBreadcrumb(Mage::helper('taxjar')->__('Tax'), Mage::helper('taxjar')->__('Tax'))
            ->_addBreadcrumb(Mage::helper('taxjar')->__('Sync Transactions'), Mage::helper('taxjar')->__('Sync Transactions'))
        ;
        return $this;
    }

    protected function _isAllowed()
    {
        $connected = Mage::getStoreConfig('tax/taxjar/connected');

        if (!$connected) {
            return false;
        }

        return Mage::getSingleton('admin/session')->isAllowed('sales/tax/taxjar_salestax_transaction');
    }
}
