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
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND_BUTTON_TEXT' => 'Do Refund',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_REFUND_CONFIRM_ERROR' => 'Error: You requested to do a refund but did not check the Confirmation box.',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_INVALID_REFUND_AMOUNT' => 'Error: You requested a refund but entered an invalid amount.',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_TRANS_ID_REQUIRED_ERROR' => 'Error: You need to specify a PlugnPay orderID (Transaction ID).',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_REFUND_INITIATED' => 'Refund Initiated. OrderID: %1$s',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CAPTURE_CONFIRM_ERROR' => 'Error: You requested to do a capture but did not check the Confirmation box.',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_CAPTURE_BUTTON_TEXT' => 'Do Capture',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_INVALID_CAPTURE_AMOUNT' => 'Error: You requested a capture but need to enter an amount.',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CAPT_INITIATED' => 'Funds Capture initiated. Amount: %1$s. OrderID: %2$s',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_VOID_BUTTON_TEXT' => 'Do Void',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_VOID_CONFIRM_ERROR' => 'Error: You requested a Void but did not check the Confirmation box.',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_VOID_INITIATED' => 'Void Initiated. OrderID: %1$s',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND_TITLE' => '<strong>Refund Transactions</strong>',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND' => 'You may refund money to the customer\'s credit card here:',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_REFUND_CONFIRM_CHECK' => 'Check this box to confirm your intent: ',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND_AMOUNT_TEXT' => 'Enter the amount you wish to refund',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND_TRANS_ID' => 'Enter the original PlugnPay orderID:',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND_TEXT_COMMENTS' => 'Notes (will show on Order History):',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND_DEFAULT_MESSAGE' => 'Refund Issued',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND_SUFFIX' => 'Returns use PlugnPay mode=return against a prior authorization. Only one return is allowed per orderID. Amount cannot exceed the original auth. Your server IP must be whitelisted in PlugnPay Security Administration.',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_CAPTURE_TITLE' => '<strong>Capture Transactions</strong>',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_CAPTURE' => 'You may capture previously-authorized funds here (PlugnPay mark):',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_CAPTURE_AMOUNT_TEXT' => 'Enter the amount to Capture: ',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_CAPTURE_CONFIRM_CHECK' => 'Check this box to confirm your intent: ',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_CAPTURE_TRANS_ID' => 'Enter the original PlugnPay orderID: ',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_CAPTURE_TEXT_COMMENTS' => 'Notes (will show on Order History):',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_CAPTURE_DEFAULT_MESSAGE' => 'Settled previously-authorized funds.',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_CAPTURE_SUFFIX' => 'Captures use PlugnPay mode=mark. Amount cannot exceed the original authorization. Your server IP must be whitelisted in PlugnPay Security Administration.',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_VOID_TITLE' => '<strong>Voiding Transactions</strong>',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_VOID' => 'You may void an unsettled transaction or uncaptured authorization.<br>Enter the PlugnPay orderID:',
    'MODULE_PAYMENT_PLUGNPAY_API_TEXT_VOID_CONFIRM_CHECK' => 'Check this box to confirm your intent:',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_VOID_AMOUNT_TEXT' => 'Enter the original transaction amount: ',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_VOID_TEXT_COMMENTS' => 'Notes (will show on Order History):',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_VOID_DEFAULT_MESSAGE' => 'Transaction Cancelled',
    'MODULE_PAYMENT_PLUGNPAY_API_ENTRY_VOID_SUFFIX' => 'Voids must be completed before the transaction is swept to the bank. Your server IP must be whitelisted in PlugnPay Security Administration.',
];

if (defined('MODULE_PAYMENT_PLUGNPAY_API_STATUS') && MODULE_PAYMENT_PLUGNPAY_API_STATUS == 'True') {
    $define['MODULE_PAYMENT_PLUGNPAY_API_TEXT_DESCRIPTION'] =
        '<a rel="noreferrer noopener" target="_blank" href="https://pay1.plugnpay.com/admin/">PlugnPay Merchant Admin</a>' .
        '<br><br><strong>Requirements:</strong><br>' .
        '* PlugnPay account username (publisher-name)<br>' .
        '* Remote Client Password (publisher-password) from Security Administration<br>' .
        '* PHP cURL with SSL<br>' .
        '* Store HTTPS enabled for Production mode<br>' .
        '* Server IP whitelisted in PlugnPay for mark/void/return<br>' .
        '<br>PCI note: This module collects card data on your site (higher PCI scope). ' .
        'For lower PCI scope, consider PlugnPay Smart Screens (hosted).' .
        (MODULE_PAYMENT_PLUGNPAY_API_TESTMODE != 'Production'
            ? '<br><br><b>Test tip:</b> Use publisher-name <code>pnpdemo</code> with sample cards per PlugnPay docs.'
            : '');
} else {
    $define['MODULE_PAYMENT_PLUGNPAY_API_TEXT_DESCRIPTION'] =
        'Accept credit cards via <strong>PlugnPay Remote API</strong>.<br><br>' .
        '<a rel="noreferrer noopener" target="_blank" href="https://www.plugnpay.com/">PlugnPay</a> · ' .
        '<a rel="noreferrer noopener" target="_blank" href="https://docs.plugnpay.com/">API Documentation</a><br><br>' .
        '<strong>Requirements:</strong><br><hr>' .
        '* PlugnPay merchant account<br>' .
        '* Remote Client Password<br>' .
        '* PHP cURL compiled with SSL<br>' .
        '* HTTPS on your storefront for Production mode';
}

return $define;
