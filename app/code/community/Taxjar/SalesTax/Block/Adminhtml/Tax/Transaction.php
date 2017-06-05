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

class Taxjar_SalesTax_Block_Adminhtml_Tax_Transaction extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'id';
        $this->_blockGroup  = 'taxjar';
        $this->_controller  = 'adminhtml_tax_transaction';
        $this->_mode        = 'backfill';

        parent::__construct();

        $this->_removeButton('reset');
        $this->_updateButton('save', 'label', Mage::helper('taxjar')->__('Sync to TaxJar'));
        $this->_updateButton('save', 'onclick', 'syncTransactions()');
        $this->_updateButton('save', 'id', 'transaction-sync-button');
        $this->_updateButton('save', 'class', '');
    }

    public function getHeaderText()
    {
        return Mage::helper('taxjar')->__('Sync Transactions');
    }

    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit/section/tax');
    }
}
