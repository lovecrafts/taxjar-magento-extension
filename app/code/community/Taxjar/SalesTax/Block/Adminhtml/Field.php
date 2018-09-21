<?php

abstract class Taxjar_SalesTax_Block_Adminhtml_Field extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    use Taxjar_SalesTax_Trait_ConfigScope;

    /**
     * @return string
     */
    public abstract function getFieldCacheKey();

    /**
     * @return array
     */
    public abstract function getTagKey();

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @throws Zend_Cache_Exception
     */
    protected function _cacheElementValue(Varien_Data_Form_Element_Abstract $element)
    {
        $elementValue = (string) $element->getValue();

        // UPDATE NOTE: include scope code into cache key
        Mage::app()->getCache()->save(
            $elementValue,
            $this->getFieldCacheKey() . '_' . $this->getScopeCode(),
            $this->getTagKey(),
            null
        );
        // END UPDATE
    }
}