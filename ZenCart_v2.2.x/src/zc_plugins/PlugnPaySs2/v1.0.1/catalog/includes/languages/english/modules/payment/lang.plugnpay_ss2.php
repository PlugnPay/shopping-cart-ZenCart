<?php
$define = [
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ADMIN_TITLE' => 'PlugnPay Smart Screens v2',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CATALOG_TITLE' => 'Credit Card',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CREDIT_CARD_OWNER' => 'Card Owner:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CREDIT_CARD_NUMBER' => 'Card Number:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CREDIT_CARD_EXPIRES' => 'Expiry Date:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_DECLINED_MESSAGE' => 'Your credit card was declined. Please try another card or contact your bank for more info.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ERROR_MESSAGE' => 'There has been an error processing your credit card. Please try again.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ERROR' => 'Credit Card Error!',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_AMOUNT_MISMATCH' => 'Payment amount did not match the order total. Please try again or contact us for assistance.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ACCOUNT_MISMATCH' => 'Payment response did not match this store\'s gateway account. Please contact us for assistance.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_SESSION_MISMATCH' => 'Your payment session could not be verified. Please try again or contact us for assistance.',
];

if (defined('MODULE_PAYMENT_PLUGNPAY_SS2_STATUS') && MODULE_PAYMENT_PLUGNPAY_SS2_STATUS == 'True') {
    $define['MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_DESCRIPTION'] =
        '<a rel="noreferrer noopener" target="_blank" href="https://pay1.plugnpay.com/admin/">PlugnPay Merchant Admin</a>' .
        '<br><br><strong>Hosted checkout (authorization-only):</strong> customers pay on Smart Screens v2 ' .
        '(<code>https://pay1.plugnpay.com/pay/</code>). Your store does not collect PAN/CVV.' .
        '<br><br>Transactions are authorized only (<code>pb_post_auth=no</code>). ' .
        'Capture, void, and refund are done in PlugnPay Admin — not from Zen Cart.' .
        '<br><br><strong>Requirements:</strong><br>' .
        '* PlugnPay gateway account username (merchant-supplied; no public demo account)<br>' .
        '<br>See the plugin README for configuration, return validation, and troubleshooting.';
} else {
    $define['MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_DESCRIPTION'] =
        'Accept payments via <strong>PlugnPay Smart Screens v2</strong> (hosted checkout at ' .
        '<code>https://pay1.plugnpay.com/pay/</code>).<br><br>' .
        '<a rel="noreferrer noopener" target="_blank" href="https://www.plugnpay.com/">PlugnPay</a> · ' .
        '<a rel="noreferrer noopener" target="_blank" href="https://docs.plugnpay.com/">Documentation</a><br><br>' .
        '<strong>Requirements:</strong><br><hr>' .
        '* PlugnPay merchant account<br>' .
        '* Gateway account username<br>' .
        '* Authorization-only (settle in PlugnPay Admin)<br>' .
        '* No public demo account — use your merchant credentials';
}

return $define;
