<?php
$define = [
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_ADMIN_TITLE' => 'PlugnPay Remote API',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CATALOG_TITLE' => 'Credit Card',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_ERROR_CURL_NOT_FOUND' => 'CURL functions not found - required for PlugnPay Remote API payment module',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_TYPE' => 'Card Type:',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_OWNER' => 'Card Owner:',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_NUMBER' => 'Card Number:',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_EXPIRES' => 'Expiry Date:',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CVV' => 'CVV Number:',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_POPUP_CVV_LINK' => 'What\'s this?',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_JS_CC_OWNER' => '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_JS_CC_NUMBER' => '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_JS_CC_CVV' => '* The 3 or 4 digit CVV number must be entered from the back of the credit card.\n',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_DECLINED_MESSAGE' => 'Your credit card could not be authorized for this reason. Please correct the information and try again or contact us for further assistance.',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_ERROR' => 'Credit Card Error!',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_COMM_ERROR' => 'Unable to process payment due to a communications error. You may try again or contact us for assistance.',
];

if (defined('MODULE_PAYMENT_PLUGNPAY_API_STATUS') && MODULE_PAYMENT_PLUGNPAY_API_STATUS == 'True') {
    $define['MODULE_PAYMENT_PLUGNPAY_API_TEXT_DESCRIPTION'] =
        '<a rel="noreferrer noopener" target="_blank" href="https://pay1.plugnpay.com/admin/">PlugnPay Merchant Admin</a>' .
        '<br><br><strong>Requirements:</strong><br>' .
        '* PlugnPay account username (publisher-name)<br>' .
        '* Remote Client Password (publisher-password) from Security Administration<br>' .
        '* PHP cURL with SSL<br>' .
        '* Store HTTPS enabled (production only)<br>' .
        '<br>Capture, void, and refund are done in PlugnPay Admin — not from Zen Cart.' .
        '<br><br>PCI note: This module collects card data on your site (higher PCI scope). ' .
        'For lower PCI scope, consider PlugnPay Smart Screens (hosted).';
} else {
    $define['MODULE_PAYMENT_PLUGNPAY_API_TEXT_DESCRIPTION'] =
        'Accept credit cards via <strong>PlugnPay Remote API</strong>.<br><br>' .
        '<a rel="noreferrer noopener" target="_blank" href="https://www.plugnpay.com/">PlugnPay</a> · ' .
        '<a rel="noreferrer noopener" target="_blank" href="https://docs.plugnpay.com/">API Documentation</a><br><br>' .
        '<strong>Requirements:</strong><br><hr>' .
        '* PlugnPay merchant account<br>' .
        '* Remote Client Password<br>' .
        '* PHP cURL compiled with SSL<br>' .
        '* HTTPS on your storefront<br>' .
        '* Capture / void / refund in PlugnPay Admin (not Zen Cart)';
}

return $define;
