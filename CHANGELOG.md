# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.4.0] - 2018-10-11
- Add calculation logging when debug mode is enabled.
- Improve backup rate sync performance up to 25%.
- Fix calculations for bundle product quantities caused by regression in 2.3.7.

## [2.3.7] - 2018-07-02
- Fix discounts applied to shipping amounts for calculations.
- Fix calculations on newly added cart items if redirect to cart after adding a product is turned off.
- Fix zip validation for backup rates.
- Fix PHP 7 type error when running scheduled backup rate sync.

## [2.3.6] - 2018-01-17
- Increase API request timeout to 30 seconds for merchants with large volume of backup rates.
- Fully exempt tax when product tax class is set to "None".
- Fix backup rates field comment typo.

## [2.3.5] - 2017-08-08
- Fix calculations for fixed price bundle products.
- Add note to "TaxJar Exempt" field for customer exemptions.

## [2.3.4] - 2017-05-23
- Fix child item quantity undefined offset error.
- Improve US region validation for backup rates and tax configuration.

## [2.3.3] - 2017-02-17
- Fix child item quantity calculation for bundle items when parent quantity is > 1.

## [2.3.2] - 2017-01-24
- Improve checkout calculation support for bundle products.

## [2.3.1] - 2016-08-29
- Add manage account and sales tax report buttons to configuration.
- Create reporting API user and role immediately after connecting to TaxJar.
- Import TaxJar product categories immediately after connecting to TaxJar.
- Fix multi-store connection issue with SID param.
- Fix non-SSL store connection issue.

## [2.3.0] - 2016-08-09
- Exempt specific customer tax classes from SmartCalcs requests.
- Support product tax codes in Core API salesOrderInfo response.

## [2.2.1] - 2016-07-17
- Support Magento EE gift card tax exemption.
- Tweak calculation requests to rely directly on line items for upcoming SmartCalcs accuracy check.

## [2.2.0] - 2016-06-30
- **Special promo sales tax calculations for Magento merchants.** You must upgrade to this version to receive special promo calculations at checkout using our new API endpoint.

## [2.1.2] - 2016-06-24
- Fix calculations with associated items in configurable/bundled products.
- Fix calculations fallback for earlier versions of Magento CE before 1.8.
- Add tax by line item rather than subtotal to prevent rounding issues.
- Fix nexus check for international locations.

## [2.1.1] - 2016-06-16
- Fix nexus upgrade script for v2.0.1 users.
- Purge nexus addresses on disconnect for switching accounts.
- Package nexus address template form.phtml.

## [2.1.0] - 2016-06-10
- **Nexus addresses can now be managed in Magento under Sales > Tax > Nexus Addresses.** If upgrading from a previous version and using checkout calculations, make sure you sync your existing addresses from TaxJar or set up a new address. Your addresses will automatically sync with TaxJar when added or changed.
- **International support for SmartCalcs checkout calculations.** One nexus address per country outside of US/CA is currently supported for [more than 30 countries](https://developers.taxjar.com/api/reference/#countries).
- Review nexus addresses for missing data and set up observer to report tax configuration issues.
- Report errors when using AJAX sync backup rate button in the TaxJar configuration.

## [2.0.1] - 2016-05-30
- Separate SmartCalcs shipping tax amount for orders and invoices.
- Fix display issue after connecting to TaxJar with caching enabled.
- Minor bug fixes for older versions of PHP and strict standards.

## [2.0.0] - 2016-05-22
- **Moved TaxJar configuration to System > Configuration > Tax.** This is a breaking change with new configuration fields. If upgrading from a previous version, you'll need to re-connect to TaxJar and re-enable your settings.
- Streamlined configuration with focus on SmartCalcs for upcoming free calculations and zip-based rates as a fallback.
- New "Connect to TaxJar" button for faster onboarding.
- New select fields for assigning backup tax rules to custom product and customer tax classes.
- New AJAX sync button for manually refreshing backup rates from TaxJar.
- Admin notifications tied to our RSS feed for extension updates and news.

[Unreleased]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.4.0...HEAD
[2.4.0]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.3.7...v2.4.0
[2.3.7]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.3.6...v2.3.7
[2.3.6]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.3.5...v2.3.6
[2.3.5]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.3.4...v2.3.5
[2.3.4]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.3.3...v2.3.4
[2.3.3]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.3.2...v2.3.3
[2.3.2]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.3.1...v2.3.2
[2.3.1]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.3.0...v2.3.1
[2.3.0]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.2.1...v2.3.0
[2.2.1]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.2.0...v2.2.1
[2.2.0]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.1.2...v2.2.0
[2.1.2]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.0.1...v2.1.0
[2.0.1]: https://github.com/taxjar/taxjar-magento-extension/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/taxjar/taxjar-magento-extension/compare/v1.6.1...v2.0.0
