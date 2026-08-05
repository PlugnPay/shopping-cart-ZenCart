<?php
/**
 * PlugnPay Smart Screens v2 admin capture / void / refund UI for order edit screen.
 *
 * @copyright Copyright (c) PlugnPay Technologies
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

$outputStartBlock = '';
$outputMain = '';
$outputCapt = '';
$outputVoid = '';
$outputRefund = '';
$outputEndBlock = '';
$output = '';

$outputStartBlock .= '<table class="noprint">' . "\n";
$outputStartBlock .= '<tr style="background-color : #bbbbbb; border-style : dotted;">' . "\n";
$outputEndBlock .= '</tr>' . "\n";
$outputEndBlock .= '</table>' . "\n";

if (method_exists($this, '_doRefund')) {
    $outputRefund .= '<td><table class="noprint">' . "\n";
    $outputRefund .= '<tr style="background-color : #dddddd; border-style : dotted;">' . "\n";
    $outputRefund .= '<td class="main">' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_TITLE . '<br>' . "\n";
    $outputRefund .= zen_draw_form('pnprefund', FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=doRefund', 'post', '', true) . zen_hide_session_id();
    $outputRefund .= MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND . '<br>';
    $outputRefund .= MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_AMOUNT_TEXT . ' ' . zen_draw_input_field('refamt', '', 'length="8"') . '<br>';
    $outputRefund .= MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_TRANS_ID . ' ' . zen_draw_input_field('trans_id', '', 'length="32"') . '<br>';
    $outputRefund .= MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_REFUND_CONFIRM_CHECK . zen_draw_checkbox_field('refconfirm', '', false) . '<br>';
    $outputRefund .= '<br>' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_TEXT_COMMENTS . '<br>' . zen_draw_textarea_field('refnote', 'soft', '50', '3', MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_DEFAULT_MESSAGE);
    $outputRefund .= '<br>' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_SUFFIX;
    $outputRefund .= '<br><input type="submit" name="buttonrefund" value="' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_REFUND_BUTTON_TEXT . '">';
    $outputRefund .= '</form>';
    $outputRefund .= '</td></tr></table></td>' . "\n";
}

if (method_exists($this, '_doCapt')) {
    $outputCapt .= '<td valign="top"><table class="noprint">' . "\n";
    $outputCapt .= '<tr style="background-color : #dddddd; border-style : dotted;">' . "\n";
    $outputCapt .= '<td class="main">' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_TITLE . '<br>' . "\n";
    $outputCapt .= zen_draw_form('pnpcapture', FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=doCapture', 'post', '', true) . zen_hide_session_id();
    $outputCapt .= MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE . '<br>';
    $outputCapt .= '<br>' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_AMOUNT_TEXT . ' ' . zen_draw_input_field('captamt', '', 'length="8"') . '<br>';
    $outputCapt .= MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_TRANS_ID . '<br>' . zen_draw_input_field('captauthid', '', 'length="32"') . '<br>';
    $outputCapt .= MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CAPTURE_CONFIRM_CHECK . zen_draw_checkbox_field('captconfirm', '', false) . '<br>';
    $outputCapt .= '<br>' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_TEXT_COMMENTS . '<br>' . zen_draw_textarea_field('captnote', 'soft', '50', '2', MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_DEFAULT_MESSAGE);
    $outputCapt .= '<br>' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_SUFFIX;
    $outputCapt .= '<br><input type="submit" name="btndocapture" value="' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_CAPTURE_BUTTON_TEXT . '">';
    $outputCapt .= '</form>';
    $outputCapt .= '</td></tr></table></td>' . "\n";
}

if (method_exists($this, '_doVoid')) {
    $outputVoid .= '<td valign="top"><table class="noprint">' . "\n";
    $outputVoid .= '<tr style="background-color : #dddddd; border-style : dotted;">' . "\n";
    $outputVoid .= '<td class="main">' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_TITLE . '<br>' . "\n";
    $outputVoid .= zen_draw_form('pnpvoid', FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=doVoid', 'post', '', true) . zen_hide_session_id();
    $outputVoid .= MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID . '<br>' . zen_draw_input_field('voidauthid', '', 'length="32"');
    $outputVoid .= '<br>' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_AMOUNT_TEXT . ' ' . zen_draw_input_field('voidamt', '', 'length="8"');
    $outputVoid .= '<br>' . MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_VOID_CONFIRM_CHECK . zen_draw_checkbox_field('voidconfirm', '', false);
    $outputVoid .= '<br><br>' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_TEXT_COMMENTS . '<br>' . zen_draw_textarea_field('voidnote', 'soft', '50', '3', MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_DEFAULT_MESSAGE);
    $outputVoid .= '<br>' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_SUFFIX;
    $outputVoid .= '<br><input type="submit" name="ordervoid" value="' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_PLUGNPAY_SS2_ENTRY_VOID_BUTTON_TEXT . '">';
    $outputVoid .= '</form>';
    $outputVoid .= '</td></tr></table></td>' . "\n";
}

if (defined('MODULE_PAYMENT_PLUGNPAY_SS2_STATUS') && MODULE_PAYMENT_PLUGNPAY_SS2_STATUS != '') {
    $output = '<!-- BOF: plugnpay_ss2 admin transaction processing tools -->';
    $output .= $outputStartBlock;
    if (MODULE_PAYMENT_PLUGNPAY_SS2_AUTHTYPE === 'authonly' || (isset($_GET['authcapt']) && $_GET['authcapt'] === 'on')) {
        if (method_exists($this, '_doRefund')) {
            $output .= $outputRefund;
        }
        if (method_exists($this, '_doCapt')) {
            $output .= $outputCapt;
        }
        if (method_exists($this, '_doVoid')) {
            $output .= $outputVoid;
        }
    } else {
        if (method_exists($this, '_doRefund')) {
            $output .= $outputRefund;
        }
        if (method_exists($this, '_doVoid')) {
            $output .= $outputVoid;
        }
    }
    $output .= $outputEndBlock;
    $output .= '<!-- EOF: plugnpay_ss2 admin transaction processing tools -->';
}
