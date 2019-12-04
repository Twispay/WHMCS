# Payment Module for WHMCS

=== Twispay Credit Card Payments ===
Contributors: twispay
Tags: payment, gateway, module
Requires at least: WHMCS 7.8
Tested up to: WHMCS 7.8

Twispay enables new and existing WHMCS instances owners to quickly and effortlessly accept online credit card payments.

Description
===========

**Credit Card Payments by Twispay** is the official [payment module for WHMCS](https://www.twispay.com/whmcs "WHMCS Twispay Payment Module")
which allows for a quick and easy integration to Twispay’s **Payment Gateway** for accepting online **credit card payments** through a secure environment and a fully customizable checkout process. Give your customers the shopping experience they expect, and boost your online sales with our simple and elegant payment plugin.

Twispay is a European certified **acquiring bank** with a sleek payment gateway optimized for online service providers. We process payments from worldwide customers using Mastercard or Visa debit and **credit cards**. Increase your purchases by using our conversion rate optimized checkout flow and manage your transactions with our dashboard created specifically for online merchants like you.

Twispay provides merchants with a lean way of accessing a complete portfolio of online payment services at the most competitive rates. For more details concerning our pricing in your area, please check out our [pricing page](https://www.twispay.com/prices "Twispay Prices in your area"). To use our payment module and start processing you will need a [Twispay merchant account](https://www.twispay.com/signup "Get started with Twispay"). For any assistance during the on-boarding process, our [sales and compliance](https://www.twispay.com/contact-twispay "Contact sales") team are happy to assist you with any enquiries you may have.

We take pride in offering world class, free customer support to all our merchants during the integration phase, and at any time thereafter. Our [support team](https://www.twispay.com/contact-support "Contact support") is available non-stop during regular business hours EET.

Our WHMCS payment extension allows for fast and easy integration with the Twispay Payment Gateway. Quickly start accepting online credit card payments through a secure environment and a fully customizable checkout process. Give your customers the shopping experience they expect, and boost your online sales with our simple and elegant payment plugin.

At the time of purchase, after checkout confirmation, the customer will be redirected to the secure Twispay Payment Gateway.

All payments will be processed in a secure PCI DSS compliant environment so you don't have to think about any such compliance requirements in your web shop.


## Merchant Benefits:
* Quick and easy installation process
* Seamless integration to new and existing platforms
* Fully customizable checkout (logo, colors or full HTML/CSS template)
* Secure credit card processing in a PCI-DSS compliant environment
* More business through continuously optimized payment flows


## Customer benefits:
* Safe payments – peace of mind while paying online
* Instant billing and receipts – faster shipments and delivery
* Smooth purchase flow – straightforward shopping experience

Install
=======

### Automatic
1. Download the Twispay payment module from WHMCS Marketplace, where you can find [The Official Twispay Payment Gateway Extension](https://marketplace.whmcs.com/product3699) and unzip the downloaded archive into a folder or download the Twispay payment module from our [Github repository](https://github.com/Twispay/WHMCS) and unzip the downloaded archive into a folder;
2. Upload/Copy the files as follows:
  * <EXTRACTION_FOLDER>/modules/gateways/twispay.php (file) -> <WHMCS_ROOT_FOLDER>/modules/gateways/ (here)
  * <EXTRACTION_FOLDER>/modules/gateways/twispay (folder) -> <WHMCS_ROOT_FOLDER>/modules/gateways/ (here)
  * <EXTRACTION_FOLDER>/modules/gateways/twispay/callback/twispay.php (file) -> <WHMCS_ROOT_FOLDER>/modules/gateways/callback/ (here)
  * <EXTRACTION_FOLDER>/modules/gateways/twispay/callback/twispay_ipn.php (file) -> <WHMCS_ROOT_FOLDER>/modules/gateways/callback/ (here)
  * <EXTRACTION_FOLDER>/includes/hooks/twispay.php (file) -> <WHMCS_ROOT_FOLDER>/includes/hooks/twispay.php (here)
3. Sign in to your WHMCS admin;
4. Go to : WHMCS Admin Area / Setup / Payments / Payment Gateways;
5. Click 'All Payment Gateways' tab;
6. Click on the "Twispay" button to activate the module (to turn it green);
  * If the button is already green NO CLICK in requiered;
5. Click 'Manage Existing Gateways' tab;
6. Go to the "Twispay" section
7. **Check** the "Show on Order Form" option;
8. Set "Display Name" to **Twispay**;
9. Select **No** under **Test Mode**. _(Unless you are testing)_
10. Enter your **Site ID**. _(Twispay Staging Account ID: You can get one from [here for live](https://merchant.twispay.com/login) or from [here for stage](https://merchant-stage.twispay.com/login))_
11. Enter your **Secret Key**. _(Twispay Secret Key: You can get one from [here for live](https://merchant.twispay.com/login) or from [here for stage](https://merchant-stage.twispay.com/login))_
12. Enter your **Redirect to custom page**. _(Leave empty to redirect to order confirmation default page (Ex: /clientarea.php?action=services).)_
13. Enter your tehnical **Contact Email**. _(This will be displayed to customers in case of a payment error)_
14. Save your changes.

Changelog
=========

= 1.0.1 =
* Updated the way requests are sent to the Twispay server.
* Updated the server response handling to process all the possible server response statuses.
* Added support for refunds and partial refunds.
* Added support for recurring orders.
* Added a "DailyCronJob" hook used to sync the status of the subscriptions between the Twispay plaform and the WHMCS instance.


= 1.0.0 =
* Initial Plugin version
