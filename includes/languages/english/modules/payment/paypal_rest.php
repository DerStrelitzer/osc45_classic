<?php

  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_TITLE', 'PayPal Checkout');
  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_PUBLIC_TITLE', 'PayPal (including Credit and Debit Cards)');
  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_DESCRIPTION', '<!-- <img src="images/icon_info.gif" border="0" />&nbsp;<a href="http://library.oscommerce.com/Package&en&paypal&oscom23&express_checkout" target="_blank" style="text-decoration: underline; font-weight: bold;">View Online Documentation</a><br /><br />--><img src="images/icon_popup.gif" border="0" />&nbsp;<a href="https://www.paypal.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit PayPal Website</a>');

  define('MODULE_PAYMENT_PAYPAL_REST_ERROR_ADMIN_CURL', 'This module requires cURL to be enabled in PHP and will not load until it has been enabled on this webserver.');
  define('MODULE_PAYMENT_PAYPAL_REST_ERROR_ADMIN_CONFIGURATION', 'This module will not load until the Seller Account or API Credential parameters have been configured. Please edit and configure the settings of this module.');

  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_BUTTON', 'Check Out with PayPal');
  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_COMMENTS', 'Comments:');

  define('MODULE_PAYMENT_PAYPAL_REST_BUTTON', 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png');
  define('MODULE_PAYMENT_PAYPAL_REST_LANGUAGE_LOCALE', 'en_US');

  define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_LINK_TITLE', 'Test API Server Connection');
  define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_TITLE', 'API Server Connection Test');
  define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_GENERAL_TEXT', 'Testing connection to server..');
  define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_BUTTON_CLOSE', 'Close');
  define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_TIME', 'Connection Time:');
  define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_SUCCESS', 'Success!');
  define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_FAILED', 'Failed! Please review your details and settings and try again.');
  define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_ERROR', 'An error occurred. Please refresh the page, review your settings, and try again.');

  define('MODULE_PAYMENT_PAYPAL_REST_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS', 'Shipping is currently not available for the selected shipping address. Please select or create a new shipping address to use with your purchase.');
  define('MODULE_PAYMENT_PAYPAL_REST_WARNING_LOCAL_LOGIN_REQUIRED', 'Please log into your account to verify the order.');
  define('MODULE_PAYMENT_PAYPAL_REST_NOTICE_CHECKOUT_CONFIRMATION', 'Please review and confirm your order below. Your order will not be processed until it has been confirmed.');

  define('MODULE_PAYMENT_PAYPAL_REST_API_DETAILS', 'PayPal API details');
  define('MODULE_PAYMENT_PAYPAL_REST_API_OK', 'All details correct');
  define('MODULE_PAYMENT_PAYPAL_REST_API_FAIL_DATA', '');
  define('MODULE_PAYMENT_PAYPAL_REST_ORDER_ID_ERROR', 'Invalid Order Id');
  define('MODULE_PAYMENT_PAYPAL_REST_ORDER_DETAILS_ERROR', 'Order is not completed');
  define('MODULE_PAYMENT_PAYPAL_REST_RESTART', 'Error during communication with PayPal (incorrect total). Please login to PayPal once again');
  define('MODULE_PAYMENT_PAYPAL_REST_RESTART_AUTHORIZE', 'Error during communication with PayPal (payment not authorized). Please login to PayPal once again');
  define('MODULE_PAYMENT_PAYPAL_REST_RESTART_CAPTURE', 'Error during communication with PayPal (payment not captured). Please login to PayPal once again');
  define('TEXT_PAY_UPON_INVOICE', 'Kauf auf Rechnung');
  define('TEXT_CANCELLED_BY_CUSTOMER', 'Cancelled By Customer');
  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_ERROR_CAPTURE', 'Payment could not be captured');
  define('MODULE_PAYMENT_PAYPAL_REST_GENERAL_ERROR', "Can't create order");
  define('MODULE_PAYMENT_PAYPAL_REST_PROCESSING_ERROR', "Payment was not processed. Review your details and try again or select different payment option");

  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_CC_NUMBER', "Card Number");
  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_EXP', "Expiration Date");
  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_EXP_PLACEHOLDER', "MM/YY");
  define('MODULE_PAYMENT_PAYPAL_REST_TEXT_CVV', "CVV");
  define('SESSION_EXPIRED_LOGIN_OR_CHECK_EMAIL', "Your session expired. Please check your email for order updates or login to your account");
  define('SESSION_EXPIRED_LOGIN_OR_CHECK_EMAIL', "Check your email for status update Session expired ");

  ///////////////////

define('ADD_PAYPAL', 'PayPal Quick Setup');
define('ADD_PAYPAL_TITLE', 'PayPal Setup');
define('PAYPAL_EXISTING_ACCOUNT', 'Do you have PayPal account?');
define('PAYPAL_ACCOUNT_OPTIONS_YES', 'Yes, connect it');
define('PAYPAL_ACCOUNT_OPTIONS_NO', 'No, create new one');
define('TEXT_ADVANCED', 'Advanced...');
define('PAYPAL_SANDBOX_TRY', 'Do you want to try sandbox first? Remember - you can switch to production at any time');
define('PAYPAL_ACCOUNT_OPTIONS_OWN_API_ACCESS', 'I have my own REST API keys on hand, and I know how to set them up');
define('PAYPAL_ACCOUNT_PRESS_BUTTON', 'Press the "PayPal" button');
define('MODULE_PAYMENT_PAYPAL_REST_CONTINUE_PAYPAL', 'Continue to PayPal');
define('MODULE_PAYMENT_PAYPAL_REST_GET_DATA_PAYPAL', 'to get data from PayPal');
define('PAYPAL_SANDBOX_MODE', 'Use PayPal sandbox account (you could switch to live account later)');
define('TEXT_ENTER_API_DETAILS', 'Enter api details');

define('MODULE_PAYMENT_PAYPAL_REST_SELLER_BOARDED_ERROR_EMAIL', 'Attention: Please confirm your email address on <a target="blank" href="https://www.paypal.com/businessprofile/settings">https://www.paypal.com/businessprofile/settings</a> in order to receive payments! You currently cannot receive payments.');

define('MODULE_PAYMENT_PAYPAL_REST_PAYLATER_TITLE', 'Pay Later (usually PayPal Credit should be also selected)');


define('MODULE_PAYMENT_PAYPAL_REST_SELLER_NOT_BOARDED', 'Your PayPal /account is not linked to osCommerce.');
define('MODULE_PAYMENT_PAYPAL_REST_SELLER_MERCHANT_ID', 'Seller Merchant Id');
define('MODULE_PAYMENT_PAYPAL_REST_OWN_CLIENT_ID', 'PayPal API Client ID');
define('MODULE_PAYMENT_PAYPAL_REST_OWN_CLIENT_SECRET', 'PayPal API Client secret');
define('MODULE_PAYMENT_PAYPAL_REST_SELLER_EMAIL', 'Seller E-Mail Address');

define('MODULE_PAYMENT_PAYPAL_REST_API_TEST', 'Test API connection');

define('MODULE_PAYMENT_PAYPAL_REST_TEXT_ADVANCED_SETTINGS', 'Account details and advanced settings');
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_ACCOUNT_DETAILS', 'PayPal Account Details');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS', 'PayPal Webhooks');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_REQUIRED_NOTE', '* for alternative payment methods (APMs)');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_REQUIRED', 'Required Webhooks');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_SUBSCRIBED', 'Subscribed Webhooks');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_SUBSCRIBE', 'Subscribe');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_SUBSCRIBE_CONFIRM', 'Please Confirm you want to subscribe to required Webhooks. You can unsubscribe at any time in you PayPal account');
define('MODULE_PAYMENT_PAYPAL_REST_DELETE_SELLER', 'Connect different PayPal account');

define('MODULE_PAYMENT_PAYPAL_REST_SELLER_BOARDED_ERROR', 'Can\'t get PayPal details');
define('MODULE_PAYMENT_PAYPAL_REST_SAVE_TO_CONTINUE', 'PayPal transaction server has been changed. Save changes to finish API configuration');
define('MODULE_PAYMENT_PAYPAL_REST_UNLINK_PROMPT', 'Are you sure you want to connect different PayPal account?');

define('MODULE_PAYMENT_PAYPAL_REST_CARD_PROCESSING_VIRTUAL_TERMINAL_TEXT', 'Virtual terminal');
define('MODULE_PAYMENT_PAYPAL_REST_COMMERCIAL_ENTITY_TEXT', 'Commercial entity');
define('MODULE_PAYMENT_PAYPAL_REST_CUSTOM_CARD_PROCESSING_TEXT', 'Custom card processing');
define('MODULE_PAYMENT_PAYPAL_REST_DEBIT_CARD_SWITCH_TEXT', 'Debit card switch');
define('MODULE_PAYMENT_PAYPAL_REST_FRAUD_TOOL_ACCESS_TEXT', 'Fraud tool');
define('MODULE_PAYMENT_PAYPAL_REST_ALT_PAY_PROCESSING_TEXT', 'Alternative payment methods');
define('MODULE_PAYMENT_PAYPAL_REST_RECEIVE_MONEY_TEXT', 'Receive money');
define('MODULE_PAYMENT_PAYPAL_REST_SEND_MONEY_TEXT', 'Send money');
define('MODULE_PAYMENT_PAYPAL_REST_STANDARD_CARD_PROCESSING_TEXT', 'Standard card processing');
define('MODULE_PAYMENT_PAYPAL_REST_WITHDRAW_MONEY_TEXT', 'Withdraw money');

define('MODULE_PAYMENT_PAYPAL_REST_CUSTOM_CARD_FIELDS', 'Custom Card Fields');
define('MODULE_PAYMENT_PAYPAL_REST_CUSTOM_CARD_3DS', '3D Secure');
define('MODULE_PAYMENT_PAYPAL_REST_CUSTOM_CARD_3DS_DESCRIPTION', '3D Secure enables you to authenticate card holders through card issuers. It reduces the likelihood of fraud when you use supported cards and improves transaction performance. A successful 3D Secure authentication can shift liability for chargebacks due to fraud from you to the card issuer.');
define('MODULE_PAYMENT_PAYPAL_REST_CONTINGENCIES', 'Сontingencies');

define('ENTRY_STATUS', 'Status');
define('TEXT_YES', 'Yes');
define('TEXT_NO', 'No');
define('TEXT_NEXT', 'Next');

define('TEXT_ALTERNATIVE_CHECKOUT_METHODS', 'Or use');
