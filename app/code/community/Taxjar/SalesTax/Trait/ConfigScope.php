<?php

trait Taxjar_SalesTax_Trait_ConfigScope
{
    protected static $_scope;
    protected static $_scopeCode;
    protected static $_scopeId;

    /**
     * Scope in core_config_data table
     *
     * @return string
     */
    public function getScope()
    {
        if (!static::$_scope) {
            static::$_scope = Mage::getSingleton('adminhtml/config_data')->getScope();
        }

        return static::$_scope;
    }

    /**
     * Scope_id in core_config_data table
     *
     * @return int
     */
    public function getScopeId()
    {
        if (!static::$_scopeId) {
            static::$_scopeId = Mage::getSingleton('adminhtml/config_data')->getScopeId();
        }

        return static::$_scopeId;
    }

    /**
     * Store or website code, admin if both are empty
     *
     * @return string
     */
    public function getScopeCode()
    {
        if (!static::$_scopeCode) {
            static::$_scopeCode = Mage::getSingleton('adminhtml/config_data')->getScopeCode() ?: 'admin';
        }

        return static::$_scopeCode;
    }

    /**
     * @param string $configPath
     *
     * @return mixed
     * @throws Mage_Core_Exception
     */
    protected function getConfigValue($configPath)
    {
        $storeScope = Mage::getSingleton('adminhtml/config_data')->getStore();
        $websiteScope = Mage::getSingleton('adminhtml/config_data')->getWebsite();

        // fallback to default if more specific scopes are not defined
        if ($storeScope) {
            $enabled = Mage::getStoreConfig($configPath, $storeScope);
        } elseif ($websiteScope) {
            $enabled = Mage::app()->getWebsite($websiteScope)->getConfig($configPath);
        } else {
            $enabled = Mage::getStoreConfig($configPath);
        }

        return $enabled;
    }
}