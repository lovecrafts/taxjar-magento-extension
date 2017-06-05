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

class Taxjar_SalesTax_Block_Adminhtml_Tax_Transaction_Backfill_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('transaction_form');
        $this->setTemplate('taxjar/transaction/form.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'backfill_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $this->setTitle('Date Range');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => 'Date Range'
        ));

        $fieldset->addField('from', 'date',
            array(
                'name'     => 'date_from',
                'label'    => Mage::helper('taxjar')->__('From'),
                'image'    => $this->getSkinUrl('images/grid-cal.gif'),
                'class'    => 'required-entry',
                'format'   => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                'required' => true,
                'tabindex' => 1
            )
        );

        $fieldset->addField('to', 'date',
            array(
                'name'     => 'date_to',
                'label'    => Mage::helper('taxjar')->__('To'),
                'image'    => $this->getSkinUrl('images/grid-cal.gif'),
                'class'    => 'required-entry',
                'format'   => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                'required' => true,
                'tabindex' => 2
            )
        );

        $form->setAction($this->getUrl('*/tax_transaction/backfill'));
        $form->setUseContainer(true);
        $form->setMethod('post');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
