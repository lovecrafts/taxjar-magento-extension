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
 * TaxJar HTTP Client
 *
 * @author Taxjar (support@taxjar.com)
 */
class Taxjar_SalesTax_Model_Client
{
    protected $_version = 'v2';
    protected $_apiKey;
    protected $_storeZip;
    protected $_storeRegionCode;
    protected $_storeRegionId;

    /**
     * Use website specific shipping origin address if possible,
     * fallback to global value otherwise.
     */
    public function __construct()
    {
        $this->_apiKey = trim(Mage::getStoreConfig('tax/taxjar/apikey'));
        $websiteForShippingOrigin = Mage::getStoreConfig('tax/taxjar/shipping_origin_website');
        if ($websiteForShippingOrigin) {
            $this->_storeZip = trim(
                Mage::app()
                    ->getWebsite($websiteForShippingOrigin)
                    ->getConfig('shipping/origin/postcode')
            );
            $this->_storeRegionId = Mage::app()
                ->getWebsite($websiteForShippingOrigin)
                ->getConfig('shipping/origin/region_id');

        } else {
            $this->_storeZip = trim(Mage::getStoreConfig('shipping/origin/postcode'));
            $this->_storeRegionId = Mage::getStoreConfig('shipping/origin/region_id');
        }

        $this->_storeRegionCode = Mage::getModel('directory/region')->load($this->_storeRegionId)->getCode();
    }

    /**
     * @return integer
     */
    public function getStoreRegionId()
    {
        return $this->_storeRegionId;
    }

    /**
     * @return string
     */
    public function getStoreRegionCode()
    {
        return $this->_storeRegionCode;
    }

    /**
     * @return integer
     */
    public function getStoreZip()
    {
        return $this->_storeZip;
    }

    /**
     * Perform a GET request
     *
     * @param string $url
     * @param array $errors
     * @return array
     */
    public function getResource($resource, $errors = array())
    {
        $client = $this->_getClient($this->_getApiUrl($resource));
        return $this->_getRequest($client, $errors);
    }

    /**
     * Perform a POST request
     *
     * @param string $resource
     * @param array $data
     * @param array $errors
     * @return array
     */
    public function postResource($resource, $data, $errors = array())
    {
        $client = $this->_getClient($this->_getApiUrl($resource), Zend_Http_Client::POST);
        $client->setRawData(json_encode($data), 'application/json');
        return $this->_getRequest($client, $errors);
    }

    /**
     * Perform a PUT request
     *
     * @param string $resource
     * @param array $data
     * @param array $errors
     * @return array
     */
    public function putResource($resource, $resourceId, $data, $errors = array())
    {
        $resourceUrl = $this->_getApiUrl($resource) . '/' . $resourceId;
        $client = $this->_getClient($resourceUrl, Zend_Http_Client::PUT);
        $client->setRawData(json_encode($data), 'application/json');
        return $this->_getRequest($client, $errors);
    }

    /**
     * Perform a DELETE request
     *
     * @param string $resource
     * @param array $errors
     * @return array
     */
    public function deleteResource($resource, $resourceId, $errors = array())
    {
        $resourceUrl = $this->_getApiUrl($resource) . '/' . $resourceId;
        $client = $this->_getClient($resourceUrl, Zend_Http_Client::DELETE);
        return $this->_getRequest($client, $errors);
    }

    /**
     * Get HTTP Client
     *
     * @param string $url
     * @return Zend_Http_Client $response
     */
    private function _getClient($url, $method = Zend_Http_Client::GET)
    {
        $client = new Zend_Http_Client($url, array('timeout' => 30));
        $client->setMethod($method);
        $client->setHeaders('Authorization', 'Bearer ' . $this->_apiKey);

        return $client;
    }

    /**
     * Get HTTP request
     *
     * @param Zend_Http_Client $client
     * @param array $errors
     * @return array
     */
    private function _getRequest($client, $errors = array())
    {
        try {
            $response = $client->request();

            if ($response->isSuccessful()) {
                $json = $response->getBody();
                return json_decode($json, true);
            } else {
                $detail = null;
                if ($response->getBody()) {
                    $detail = json_decode($response->getBody(), true);
                    if (isset($detail['detail'])) {
                        $detail = $detail['detail'];
                    }
                }

                $this->_handleError($errors, $response->getStatus(), $detail);
            }
        } catch (Zend_Http_Client_Exception $e) {
            Mage::throwException(Mage::helper('taxjar')->__('Could not connect to TaxJar.'));
        }
    }

    /**
     * Get SmartCalcs API URL
     *
     * @param string $type
     * @return string
     */
    private function _getApiUrl($resource)
    {
        $apiUrl = 'https://api.taxjar.com/' . $this->_version;

        switch($resource) {
            case 'config':
                $apiUrl .= '/plugins/magento/configuration/' . $this->_storeRegionCode;
                break;
            case 'rates':
                $apiUrl .= '/plugins/magento/rates/' . $this->_storeRegionCode . '/' . $this->_storeZip;
                break;
            case 'categories':
                $apiUrl .= '/categories';
                break;
            case 'nexus':
                $apiUrl .= '/nexus/addresses';
                break;
            case 'orders':
                $apiUrl .= '/transactions/orders';
                break;
            case 'refunds':
                $apiUrl .= '/transactions/refunds';
                break;
        }

        return $apiUrl;
    }

    /**
     * Handle API errors and throw exception
     *
     * @param array $customErrors
     * @param string $statusCode
     * @param string $detail
     * @return string
     */
    private function _handleError($customErrors, $statusCode, $detail = null)
    {
        $errors = $this->_defaultErrors() + $customErrors;

        if (isset($errors[$statusCode])) {
            Mage::throwException($errors[$statusCode] . ' ' . $detail);
        } else {
            Mage::throwException($errors['default'] . ' ' . $detail);
        }
    }

    /**
     * Return default API errors
     *
     * @return array
     */
    private function _defaultErrors()
    {
        return array(
            '401' => Mage::helper('taxjar')->__('Your TaxJar API token is invalid. Please review your TaxJar account at https://app.taxjar.com.'),
            'default' => Mage::helper('taxjar')->__('Could not connect to TaxJar.')
        );
    }
}
