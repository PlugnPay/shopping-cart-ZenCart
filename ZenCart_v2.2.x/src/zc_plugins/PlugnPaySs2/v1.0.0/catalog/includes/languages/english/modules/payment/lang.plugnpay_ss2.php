<?php
$define = [
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ADMIN_TITLE' => 'PlugnPay Smart Screens v2',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CATALOG_TITLE' => 'Credit Card',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ERROR_CURL_NOT_FOUND' => 'CURL functions not found - required for PlugnPay Smart Screens admin capture/void/refund',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CREDIT_CARD_OWNER' => 'Card Owner:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CREDIT_CARD_NUMBER' => 'Card Number:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CREDIT_CARD_EXPIRES' => 'Expiry Date:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_DECLINED_MESSAGE' => 'Your credit card was declined. Please try another card or contact your bank for more info.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ERROR_MESSAGE' => 'There has been an error processing your credit card. Please try again.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ERROR' => 'Credit Card Error!',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_AMOUNT_MISMATCH' => 'Payment amount did not match the order total. Please try again or contact us for assistance.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ACCOUNT_MISMATCH' => 'Payment response did not match this store\'s gateway account. Please contact us for assistance.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_COMM_ERROR' => 'Unable to process payment due to a communications error. You may try again or contact us for assistance.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_BUTTON_TEXT' => 'Do Refund',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_REFUND_CONFIRM_ERROR' => 'Error: You requested to do a refund but did not check the Confirmation box.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_INVALID_REFUND_AMOUNT' => 'Error: You requested a refund but entered an invalid amount.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_TRANS_ID_REQUIRED_ERROR' => 'Error: You need to specify a PlugnPay orderID (Transaction ID).',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_REFUND_INITIATED' => 'Refund Initiated. OrderID: %1$s',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CAPTURE_CONFIRM_ERROR' => 'Error: You requested to do a capture but did not check the Confirmation box.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_BUTTON_TEXT' => 'Do Capture',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_INVALID_CAPTURE_AMOUNT' => 'Error: You requested a capture but need to enter an amount.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CAPT_INITIATED' => 'Funds Capture initiated. Amount: %1$s. OrderID: %2$s',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_BUTTON_TEXT' => 'Do Void',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_VOID_CONFIRM_ERROR' => 'Error: You requested a Void but did not check the Confirmation box.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_VOID_INITIATED' => 'Void Initiated. OrderID: %1$s',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_TITLE' => '<strong>Refund Transactions</strong>',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND' => 'You may refund money to the customer\'s credit card here:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_REFUND_CONFIRM_CHECK' => 'Check this box to confirm your intent: ',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_AMOUNT_TEXT' => 'Enter the amount you wish to refund',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_TRANS_ID' => 'Enter the original PlugnPay orderID:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_TEXT_COMMENTS' => 'Notes (will show on Order History):',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_DEFAULT_MESSAGE' => 'Refund Issued',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_SUFFIX' => 'Returns use PlugnPay mode=return against a prior authorization. Only one return is allowed per orderID. Amount cannot exceed the original auth. Your server IP must be whitelisted in PlugnPay Security Administration.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_TITLE' => '<strong>Capture Transactions</strong>',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE' => 'You may capture previously-authorized funds here (PlugnPay mark):',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_AMOUNT_TEXT' => 'Enter the amount to Capture: ',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CAPTURE_CONFIRM_CHECK' => 'Check this box to confirm your intent: ',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_TRANS_ID' => 'Enter the original PlugnPay orderID: ',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_TEXT_COMMENTS' => 'Notes (will show on Order History):',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_DEFAULT_MESSAGE' => 'Settled previously-authorized funds.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_SUFFIX' => 'Captures use PlugnPay mode=mark. Amount cannot exceed the original authorization. Your server IP must be whitelisted in PlugnPay Security Administration.',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_TITLE' => '<strong>Voiding Transactions</strong>',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID' => 'You may void an unsettled transaction or uncaptured authorization.<br>Enter the PlugnPay orderID:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_VOID_CONFIRM_CHECK' => 'Check this box to confirm your intent:',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_AMOUNT_TEXT' => 'Enter the original transaction amount: ',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_TEXT_COMMENTS' => 'Notes (will show on Order History):',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_DEFAULT_MESSAGE' => 'Transaction Cancelled',
    'MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_SUFFIX' => 'Voids must be completed before the transaction is swept to the bank. Your server IP must be whitelisted in PlugnPay Security Administration.',
];

if (defined('MODULE_PAYMENT_PLUGNPAY_SS2_STATUS') && MODULE_PAYMENT_PLUGNPAY_SS2_STATUS == 'True') {
    $define['MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_DESCRIPTION'] =
        '<a rel="noreferrer noopener" target="_blank" href="https://pay1.plugnpay.com/admin/">PlugnPay Merchant Admin</a>' .
        '<br><br><strong>Requirements:</strong><br>' .
        '* PlugnPay gateway account username<br>' .
        '* Remote Client Password (for admin capture/void/refund only)<br>' .
        '* PHP cURL with SSL (admin ops)<br>' .
        '* Server IP whitelisted in PlugnPay for mark/void/return<br>' .
        '<br>PCI note: Card data is collected on PlugnPay Smart Screens (hosted). ' .
        'Your store does not collect PAN/CVV onsite.';
} else {
    $define['MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_DESCRIPTION'] =
        'Accept payments via <strong>PlugnPay Smart Screens v2</strong> (hosted checkout).<br><br>' .
        '<a rel="noreferrer noopener" target="_blank" href="https://www.plugnpay.com/">PlugnPay</a> · ' .
        '<a rel="noreferrer noopener" target="_blank" href="https://docs.plugnpay.com/">Documentation</a><br><br>' .
        '<strong>Requirements:</strong><br><hr>' .
        '* PlugnPay merchant account<br>' .
        '* Gateway account username<br>' .
        '* Remote Client Password (for Capture / Void / Refund from Admin)<br>' .
        '* PHP cURL compiled with SSL (admin ops)';
}

return $define;
