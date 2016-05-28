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
* Support for Woo Multi Currency
* Locale support on PayWay based on language on Wordpress page

## RELEASES

* Latest Release (May 2016): [Download](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/latest)
* Release [0.9a](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/0.9a)
* Release [0.9](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/0.9)
* Release [0.8a](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/0.8a)
* Release [0.7](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/0.7)

## CHANGELOG

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

## CONTRIBUTING

Thank you for considering contributing and developing features on our repository.

## LICENSE

A source code is an open-sourced and is under [MIT license](http://opensource.org/licenses/MIT) and is possible to change or improve code.
