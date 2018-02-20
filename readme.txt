=== T-Com payway payment gateway ===
Contributors: Marin Sagovac
Donate link: 
Tags: WooCommerce, Payment Gateway, Payway, T-Com Payway, Croatia
Requires at least: 3.9
Tested up to: 4.0
Stable tag: 1.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

T-Com payment gateway for Croatian users.

== Description ==

T-Com payment gateway is payment gateway for WooCommerce Wordpress plugin to make payment 

using T-Com payment provider and services. It's offer to insert your payment data and

redirect to T-Com PayWay. PayWay API 1.0.7 (June 2015)

Why pay 500 HRK if you can get for free? :)

Contact: marin@sagovac.com	

==Features==

Process and accepts payment type

Integration with WooCommerce payment service

Pictures of T-Com payway with credit cards supported 

Customize and easy to change secret key in admin panel

Checkout sums of data and order process

==Requirements==

Wordpress Site

WooCommerce shopping cart plugin

SSL certificate for the web hosting account (recommended but not a necessity)


== Installation ==

1.Install Wordpress

2.Ensure you have the latest version of WooCommerce plugin installed

3.Unzip and upload contents of the plugin to your /wp-content/plugins/ directory

4.Activate the plugin through the 'Plugins' menu available in WordPress


== Configuration ==

Provided below are the facets to be done for configuration of HNB Bank online payment gateway plugin

Visit WooCommerce > Settings > Checkout Tab

Click on "T-Com PayWay*" to edit the settings. If you do not see "T-Com PayWay" in the list at the top of the screen, check whether you have activated the plugin via the WordPress Plugin Manager

Enable the Payment Method; name it Credit Card / Debit Card / Internet Banking (this will be displayed on the payment webpage your users will be viewing)

Add in your PG Domain (often as https://pgw.t-com.hr/payment.aspx), PG Shop Id: (number ID of payment gateway), PG Secret Key (your secret key offered from T-Com PayWay)

On settings make sure that you add PG MerRespURL as http://example.com/checkout/, responce_url_sucess and responce_url_fail as http://example.com/checkout/.

On admin panel checkout_msg form is alert for customer information about forwarding to T-Com Payway.

Add Success redirect URL and Fail redirect URL (URL you want to redirect after payment)

Click Save


== Screenshots ==

1. All respective account info details are put in the above featured panel. Merchant ID, Instance ID & Hash Keys are offered by Hatton National Bank.
2. Shown on the above image are the "Checkout Page" payment options, inclusive of HNB IPG, given to users for selection.
3. Displayed above contains the plugin page view comprising the "HNB IPG" option. Make sure to turn on this option from the provided plugin page.

== Changelog ==
- Integration with PayWay
- Show credit cards
- 2015 payway implementation with SHA512 and new payway API 1.0.7
- euro currency based on en_US in URI

== Frequently Asked Questions ==
No recent asked questions 

== Upgrade Notice ==
No recent asked updates
