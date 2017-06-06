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
 * TaxJar Extension UI
 * Returns transaction sync content
 */
class Taxjar_SalesTax_Model_Transaction_Comment
{
    /**
     * Display transaction sync content
     *
     * @param void
     * @return string
     */
    public function getCommentText()
    {
        $isAuthorized = Mage::getStoreConfig('tax/taxjar/transaction_auth');
        $isConnected = Mage::getStoreConfig('tax/taxjar/connected');
        $isEnabled = Mage::getStoreConfig('tax/taxjar/transactions');

        $htmlString = "<p class='note'><span>Sync orders and refunds with TaxJar for automated sales tax reporting and filing. Complete and closed transactions sync automatically on update.</span></p><br/>";

        if (!$isAuthorized && $isConnected) {
            $htmlString .= $this->_buildUnauthorizedHtml();
        }

        if ($isEnabled) {
            $htmlString .= $this->_buildEnabledHtml();
        }

        return $htmlString;
    }

    /**
     * Build HTML for transaction sync enabled
     *
     * @return string
     */
    private function _buildEnabledHtml()
    {
        $syncUrl = Mage::helper('adminhtml')->getUrl('adminhtml/tax_transaction');
        $htmlString = "<p><button type='button' class='scalable' onclick='window.location=\"$syncUrl\"'><span>Sync Transactions</span></button></p><br>";
        return $htmlString;
    }

    /**
     * Build HTML for transaction sync upgrade
     *
     * @return string
     */
    private function _buildUnauthorizedHtml()
    {
        $authUrl = 'https://app.taxjar.com';
        $popupUrl = $this->_getPopupUrl($authUrl);
        $upgradeUrl = Mage::helper('adminhtml')->getUrl('adminhtml/taxjar/upgrade');
        $upgradeUrl .= (parse_url($upgradeUrl, PHP_URL_QUERY) ? '&' : '?');
        $htmlString = "<p><button type='button' class='scalable' onclick=\"openPopup('$popupUrl', 'Upgrade Your TaxJar Account', 400, 500)\"><span>Start Your Free 30 Day Trial</span></button></p>";
        $htmlString .= "<p class='note'><span>Transaction sync requires a <a href='https://www.taxjar.com/pricing/' target='_blank'>paid subscription</a>. Start your free reporting trial today to enable transaction sync. Already a subscriber? <a href='javascript:void(0)' onclick=\"openPopup('$popupUrl', 'Upgrade Your TaxJar Account', 400, 500)\">Click here.</a></span></p><br/>";
        $htmlString .= <<<EOT
        <script>
            function openPopup(url, title, w, h) {
                var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
                var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
                var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
                var left = ((width / 2) - (w / 2)) + dualScreenLeft;
                var top = ((height / 2) - (h / 2)) + dualScreenTop;

                window.popup = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

                if (window.focus) window.popup.focus();
            }

            window.addEventListener('message', function(e) {
                if (e.origin !== '{$authUrl}')
                    return;

                try {
                    var data = JSON.parse(e.data);
                    if (data.reporting) {
                        window.popup.postMessage('Data received', '{$authUrl}');
                        window.location = encodeURI('{$upgradeUrl}');
                    } else {
                        throw 'Invalid data';
                    }
                } catch(e) {
                    alert('Invalid reporting auth provided. Please try again or contact support@taxjar.com.');
                }
            }, false);
        </script>
EOT;
        return $htmlString;
    }

    private function _getPopupUrl($authUrl)
    {
        $popupUrl = $authUrl . '/smartcalcs/connect/magento/upgrade_account/?store=' . urlencode($this->_getStoreOrigin());
        $pluginVersion = Mage::getConfig()->getModuleConfig('Taxjar_SalesTax')->version;

        if ($this->_getStoreGeneralEmail()) {
            $popupUrl .= '&email=' . urlencode($this->_getStoreGeneralEmail());
        }

        $popupUrl .= '&plugin=magento&version=' . $pluginVersion;

        return $popupUrl;
    }

    /**
     * Get current store origin
     *
     * @param void
     * @return string
     */
    private function _getStoreOrigin()
    {
        $protocol = Mage::app()->getRequest()->isSecure() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'];
    }

    /**
     * Get store general contact email if non-default
     *
     * @param void
     * @return string
     */
    private function _getStoreGeneralEmail()
    {
        $email = Mage::getStoreConfig('trans_email/ident_general/email');
        if ($email != 'owner@example.com') {
            return $email;
        } else {
            return '';
        }
    }
}
