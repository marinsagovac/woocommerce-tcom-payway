# Woocommerce T-Com PayWay (Croatia)
Woocommerce plugin for payment service using T-Com payment gateway service in Croatia

T-Com PayWay is payment gateway service for Croatia only.

## API

Latest API used for version 1.0.7 from official T-Com PayWay on July 2015. Official T-Com Croatia [PayWay](https://www.hrvatskitelekom.hr/poslovni/ict/poslovna-rjesenja/web-shop#payway).

## FEATURES

* It's improvement change from old T-Com PayWay API to new API 1.0.7 using SHA512 key.
* Added autoform submit function to send customer data.
* Added T-Com PayWay cards types
* Example [Form](https://github.com/marinsagovac/woocommerce-tcom-payway/blob/master/docs/primjer_obrasca.png) configuration
* List table of PayWay transactions on administration page
* Locale support on PayWay based on language on Wordpress page
* WPML and Woo Multi Currency support currency rate

## RELEASES

* Version 1.3 not yet released
* Release [1.2](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.2)
* Release [1.1](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.1)
* Release [1.0](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.0)
* Release [0.9b](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/0.9b)
* Release [0.9a](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/0.9a)

## INSTALLATION

Download ZIP archive, upload via plugin page and apply package. Activate plugin. Change settings under Woocomerce settings page on the checkout page.

## CHANGELOG

### 1.3

* Complete code review and files reorganisation
* Added textdomain and translations
* Applied WP Coding Standards
* Fixed deprecated WC Order methods

### 1.2

* Added WPML support to supply the currency rate
* Keep support for Woo Multi Currency

### 1.1

* Fix receipment class
* Added wiki documenatation

### 1.0

* Fix pgw result code
* Fix response code, parsing $id variable
* Clean unneeded codes
* Timeout 3 seconds so that user can read notice on redirecting page
* Pending status after successful payment
* Order note with code status when is status code 3, returning to cart and empty cart (cancelled status)
* User already receive email notifications for successful transactions, translated to Croatian

### 0.9b

* Removed unused code, added include plugin, fix name class

### 0.9a

* Fix 3D Secure with response code 4 as success with pending status

### 0.9

* Added data list table to show PayWay transaction status on administration page [Example](https://github.com/marinsagovac/woocommerce-tcom-payway/blob/master/docs/DataList.jpg)

### 0.8a

* Fixed Woocommerce declined, successfull or failed status to Order status
* Support with Woo Multi Currency plugin to handle multi currency with PayWay (default is deactivated)
* Added reason code and reason response text received from PayWay, showing to client status message
* Fix unused codes, rebuild database table to persist response code
* Remove and cleanup code

### 0.7

* Added locale language on PayWay based on language in Wordpress locale

### 0.6

* Fix currency by billing Country (not language localization URI)
* Remove unused code
* Cleaning up

## HELP

Click here to go to [WIKI Help page](https://github.com/marinsagovac/woocommerce-tcom-payway/wiki/Common-issues-and-helps) for more common mistakes and helps.

## CONTRIBUTING

Thank you for considering contributing and developing features on our repository.
We're continuing to improving, upgrading and fixing our repository using open source directive so specially thanks to contributors.

## LICENSE

A source code is an open-sourced and is under [MIT license](http://opensource.org/licenses/MIT) and is possible to change or improve code.
