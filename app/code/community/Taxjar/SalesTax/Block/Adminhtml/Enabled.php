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
 * Config Enabled Dropdown Renderer
 * Handle state based on presence of API token
 */
class Taxjar_SalesTax_Block_Adminhtml_Enabled extends Taxjar_SalesTax_Block_Adminhtml_Field
{
    const CACHE_KEY = 'taxjar_salestax_config_enabled';
    const CACHE_TAG = ['TAXJAR_SALESTAX_ENABLED'];

    /**
     * @return string
     */
    public function getFieldCacheKey()
    {
        return self::CACHE_KEY;
    }

    /**
     * @return array
     */
    public function getTagKey()
    {
        return self::CACHE_TAG;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     * @throws Zend_Cache_Exception
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $apiKey = trim(Mage::getStoreConfig('tax/taxjar/apikey'));

        if (!$apiKey) {
            $element->setDisabled('disabled');
        } else {
            $this->_cacheElementValue($element);
        }

        return parent::_getElementHtml($element);
    }
}
