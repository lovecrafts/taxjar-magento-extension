<?php

/**
 * Create and parse rates from JSON obj
 *
 * @author Taxjar (support@taxjar.com)
 */
class Taxjar_SalesTax_Model_Rate {
  private $cache;
  
  public function __construct() {
    $this->cache = Mage::getSingleton('core/cache');
  }

  /**
   * Try to create the rate
   *
   * @param JSON $string
   * @return array
   */
  public function create( $rateJson ) {
    try {
      $zip        = $rateJson['zip'];
      $regionCode = $rateJson['state'];
      $rate       = $rateJson['rate'];
      
      if ( isset( $rateJson['country'] ) ) {
        $countryCode = $rateJson['country'];
      }
      else {
        $countryCode = 'US';
      }
      
      if ($this->cache->load('regionId') && $regionCode == $this->cache->load('regionCode') && $countryCode == $this->cache->load('countryCode')) {
        $regionId = $this->cache->load('regionId');
      } else {
        $regionId = Mage::getModel('directory/region')->loadByCode($regionCode, $countryCode)->getId();
        $this->cache->save($regionId, 'regionId');
        $this->cache->save($regionCode, 'regionCode');
        $this->cache->save($countryCode, 'countryCode');
      }
      
      $rateModel = Mage::getModel('tax/calculation_rate');
      $rateModel->setTaxCountryId($countryCode);
      $rateModel->setTaxRegionId($regionId);
      $rateModel->setTaxPostcode($zip);
      $rateModel->setCode($countryCode . '-' . $regionCode . '-' . $zip);
      $rateModel->setRate($rate);
      $rateModel->save();

      if ( $rateJson['freight_taxable'] ) {
        $shippingRateId = $rateModel->getId();
      }
      else {
        $shippingRateId = 0;
      }

      return array( $rateModel->getId(), $shippingRateId );
    }
    catch ( Exception $e ) {
      Mage::getSingleton('core/session')->addNotice( "There was an error encountered while loading rate with code " . $rateModel->getCode() . ". This is most likely due to duplicate codes and can be safely ignored if lots of other rates were loaded. If the error persists, email support@taxjar.com with a screenshot of any Magento errors displayed." );
      unset( $rateModel );
      return;
    }
  }
}
?>