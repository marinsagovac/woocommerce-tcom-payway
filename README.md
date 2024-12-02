# PayWay Hrvatski Telekom payment gateway for WooCommerce

[![build-test](https://github.com/marinsagovac/woocommerce-tcom-payway/actions/workflows/wordpress-plugin-check.yml/badge.svg?branch=master)](https://github.com/marinsagovac/woocommerce-tcom-payway/actions/workflows/wordpress-plugin-check.yml)

![Woo](assets/images/banner-1544x500.png)

---
**Wordpress Woocommerce plugin** (payment gateway for Croatia)

Support payment gateway for PayWay HT payment gateway.

![PayWay](https://raw.githubusercontent.com/marinsagovac/woocommerce-tcom-payway/master/assets/images/payway-logo.png)

## API

API 2.x.x support updated to latest IntlCurrency and IntAmount 

Official Croatia Hrvatski Telekom [PayWay](https://www.hrvatskitelekom.hr/poslovni/ict/poslovna-rjesenja/web-shop#payway).

PayWayForm Documentation revision supported: 7.1.10

## FEATURES

Features
* EUR mandatory currency, HRK set as IntAmount from 1.1.2023.
* Added Currency code 978 and sent mandatory as `CurrencyCode` property [ISO 4217 HR](https://www.six-group.com/en/products-services/financial-information/data-standards.html)
* IntlCurrency and IntAmount added
* API 2.0 Support
* Production and testing API
* Added other languages with automation language switching on PayWay form
* PayWay transaction admin page lists
* WPML support conversion rate
* Locale support based on WP locale settings (EN/HR)
* Multilanguage support using po/mo translation
* Replace banner image of PayWay on checkout with smaller 250px width
* Not needle IntAmount and IntCurrency from 1.1.2024.
* Support PHP 8.x

Removed
* Removed woo-multi-currency support and integration
* Removed T-Com names
* Removed HRK to EUR conversion

## RELEASES

### OFFICIAL RELEASES

Latest version:

* Version [1.8.6](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.8.6) December/2024

API 2.x.x:

* Version [1.8.6](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.8.6) December/2024
* Version [1.8.5](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.8.5) November/2024
* Version [1.8.3](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.8.3) November/2024
* Version [1.8.1](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.8.1) February/2024
* Version [1.7.3](https://github.com/marinsagovac/woocommerce-tcom-payway/archive/refs/tags/1.7.3.zip) January/2023

Old releases (deprecated):

* Version [1.7.1](https://github.com/marinsagovac/woocommerce-tcom-payway/archive/refs/tags/1.7.1.zip) January/2023
* Version [1.7](https://github.com/marinsagovac/woocommerce-tcom-payway/archive/refs/tags/1.7.zip) December/2022
* Version [1.6.1](https://github.com/marinsagovac/woocommerce-tcom-payway/archive/refs/tags/1.6.1.zip) September/2022
* Version [1.5](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tags/1.5.zip) November/2021
* Version [1.4](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tags/1.4.zip) June/2021
* Version [1.3.3](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.3.3) January/2021
* Version [1.3](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.3) December/2019
* Release [1.2](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.2)
* Release [1.1](https://github.com/marinsagovac/woocommerce-tcom-payway/releases/tag/1.1)

## INSTALLATION

Download ZIP archive, upload via plugin page and apply package. Activate plugin. Change settings under Woocomerce settings page on the checkout page.

## CHANGELOG

Changelog is now moved [here](https://github.com/marinsagovac/woocommerce-tcom-payway/blob/master/CHANGELOG.md).

## HELP

Click here to go to [WIKI Help page](https://github.com/marinsagovac/woocommerce-tcom-payway/wiki/Common-issues-and-helps) for more common mistakes and helps.

## CONTRIBUTING

Thank you for considering contributing and developing features on our repository.
We're continuing to improving, upgrading and fixing our repository using open source directive so specially thanks to contributors.

## LICENSE

A source code is an open-sourced and is under [MIT license](http://opensource.org/licenses/MIT) and is possible to change or improve code.

![Security](assets/images/payway.png)

## TIP

You can buy me a coffe https://ko-fi.com/msagovac.
