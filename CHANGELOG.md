# CHANGELOG

## Info

Most recent latest changes is defined on every major, minor and bug fixes.

## Versions

### 1.7.1

* Change HNB URL tecajna to v3

### 1.7

Use since 01.01.2023 as EUR mandatory currency:

* EUR as main currency set 1.1.2022., removed unnecessary codes for conversion from HRK to EUR
* Added Currency code 978 and sent mandatory as `CurrencyCode` property [ISO 4217 HR](https://www.six-group.com/en/products-services/financial-information/data-standards.html)
* IntCurrency/IntAmount set as HRK currency for informative calculation

> Na svim Vašim shopID-evima će se sa 01.01.2023 od strane PayWay-a automatski postaviti EUR valuta, a na platnoj formi će i dalje biti dvojni prikaz cijena, no sa EUR valutom kao osnovnom i informativnim iznosom u HRK.

### 1.6.1

* Curl module load checks

### 1.6

* IntlCurrency and IntAmount added
* API 2.0 Support
* Production and testing API 
* Removed T-Com brand names
* Added other languages support
* Removed Woo Multi Currency Support
* HNB auto conversion support
* added gitignore file
* removed old readme file
* Replace banner image of PayWay on checkout with smaller 250px width

### 1.3.3

* fixing minor bugs
* changelog added and linked

### 1.3.2

* Signatures typo issues fixed
* Resolved description settings that is not shown

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
