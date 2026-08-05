<?php
/**
 * PlugnPay Smart Screens v2 payment method for Zen Cart 2.2.x
 *
 * Redirects the customer to:
 * https://pay1.plugnpay.com/pay/
 *
 * Card data is collected on PlugnPay. Transactions are authorization-only
 * (pb_post_auth=no). Capture / void / refund are handled in PlugnPay admin,
 * not from Zen Cart.
 *
 * @copyright Copyright (c) PlugnPay Technologies
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('TABLE_PLUGNPAY_SS2')) {
    define('TABLE_PLUGNPAY_SS2', DB_PREFIX . 'plugnpay_ss2');
}

class plugnpay_ss2 extends base
{
    public $code;
    public $title;
    public $description;
    public $enabled;
    public $sort_order;
    public $order_status;
    public $form_action_url;
    public $collectsCardDataOnsite = false;
    public $transaction_id = null;
    public $auth_code = null;

    private $_check;
    private string $_logDir = '';
    private string $gateway_currency = 'USD';
    /** @var array<string, mixed> */
    private array $reportable_submit_data = [];
    /** @var array<string, mixed> */
    private array $authorize = [];
    protected string $cc_card_number = '';
    protected string $cc_card_type = '';
    protected string $cc_expiry_month = '';
    protected string $cc_expiry_year = '';

    public function __construct()
    {
        global $order;

        $this->code = 'plugnpay_ss2';
        $this->enabled = (defined('MODULE_PAYMENT_PLUGNPAY_SS2_STATUS') && MODULE_PAYMENT_PLUGNPAY_SS2_STATUS === 'True');

        if (IS_ADMIN_FLAG === true) {
            $this->title = MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ADMIN_TITLE;
            if ($this->enabled) {
                if (!defined('MODULE_PAYMENT_PLUGNPAY_SS2_LOGIN')
                    || trim((string)MODULE_PAYMENT_PLUGNPAY_SS2_LOGIN) === ''
                ) {
                    $this->title .= '<span class="alert"> (Not Configured)</span>';
                }
                $this->tableCheckup();
            }
        } else {
            $this->title = MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CATALOG_TITLE;
        }

        $this->description = MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_PLUGNPAY_SS2_SORT_ORDER')
            ? MODULE_PAYMENT_PLUGNPAY_SS2_SORT_ORDER
            : null;

        if (null === $this->sort_order) {
            return;
        }

        $this->form_action_url = 'https://pay1.plugnpay.com/pay/';
        // Authorization-only: keep order Pending until settled outside Zen Cart
        $this->order_status = 1;

        $this->_logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;
        $this->gateway_currency = defined('MODULE_PAYMENT_PLUGNPAY_SS2_CURRENCY')
            ? (string)MODULE_PAYMENT_PLUGNPAY_SS2_CURRENCY
            : 'USD';

        if (is_object($order)) {
            $this->update_status();
        }
    }

    public function update_status(): void
    {
        global $order, $db;

        if ($this->enabled
            && defined('MODULE_PAYMENT_PLUGNPAY_SS2_ZONE')
            && (int)MODULE_PAYMENT_PLUGNPAY_SS2_ZONE > 0
            && isset($order->billing['country']['id'])
        ) {
            $check_flag = false;
            $check = $db->Execute(
                "SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES .
                " WHERE geo_zone_id = '" . (int)MODULE_PAYMENT_PLUGNPAY_SS2_ZONE .
                "' AND zone_country_id = '" . (int)$order->billing['country']['id'] .
                "' ORDER BY zone_id"
            );
            while (!$check->EOF) {
                if ($check->fields['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                }
                if ($check->fields['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
                $check->MoveNext();
            }
            if ($check_flag === false) {
                $this->enabled = false;
            }
        }
    }

    public function javascript_validation(): string
    {
        return '';
    }

    public function selection(): array
    {
        return [
            'id' => $this->code,
            'module' => MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_CATALOG_TITLE,
        ];
    }

    public function pre_confirmation_check(): void
    {
        // Offsite: no card fields on the store. Return-path validation is in before_process().
    }

    public function confirmation(): array
    {
        return [];
    }

    public function process_button(): string
    {
        global $order, $currencies;

        $amount = round((float)$order->info['total'], 2);
        $currency = (string)($_SESSION['currency'] ?? $this->gateway_currency);

        // Convert to gateway-supported currency when needed
        if (isset($order->info['currency']) && $order->info['currency'] !== $this->gateway_currency) {
            $amount = round((float)$order->info['total'] * (float)$currencies->get_value($this->gateway_currency), 2);
            $currency = $this->gateway_currency;
        }

        // Basket / cart ID for PlugnPay account_code_1
        $basketId = '';
        if (isset($_SESSION['cart']) && is_object($_SESSION['cart']) && !empty($_SESSION['cart']->cartID)) {
            $basketId = (string)$_SESSION['cart']->cartID;
        }

        // Zen Cart resumes the checkout session from zenid on the return URL (GET).
        // Custom-field zenid alone is not visible to init_sessions on a cross-site POST
        // (cookies are often withheld by SameSite), which caused "session not found"
        // / session-mismatch failures on callback. Mirror the 1.5 SS2 success-URL pattern.
        $sessionId = zen_session_id();
        $successUrlParams = zen_session_name() . '=' . $sessionId;

        $submit_data = [
            'pt_gateway_account' => defined('MODULE_PAYMENT_PLUGNPAY_SS2_LOGIN')
                ? (string)MODULE_PAYMENT_PLUGNPAY_SS2_LOGIN
                : '',
            'pt_transaction_amount' => number_format($amount, 2, '.', ''),
            'pt_currency' => $currency,
            'pt_currency_code' => $currency,
            'pb_post_auth' => 'no',
            'pt_account_code_1' => $basketId,
            'pt_billing_company' => (string)($order->billing['company'] ?? ''),
            'pt_payment_name' => trim(($order->billing['firstname'] ?? '') . ' ' . ($order->billing['lastname'] ?? '')),
            'pt_billing_address_1' => (string)($order->billing['street_address'] ?? ''),
            'pt_billing_city' => (string)($order->billing['city'] ?? ''),
            'pt_billing_state' => (string)($order->billing['state'] ?? ''),
            'pt_billing_postal_code' => (string)($order->billing['postcode'] ?? ''),
            'pt_billing_country' => (string)($order->billing['country']['iso_code_2'] ?? ''),
            'pt_billing_phone_number' => (string)($order->customer['telephone'] ?? ''),
            'pt_billing_email_address' => (string)($order->customer['email_address'] ?? ''),
            'pt_client_identifier' => 'ZenCart_SS2',
            'pt_ip_address' => zen_get_ip_address(),
            'pb_transition_type' => 'post',
            // Force zenid into the return URL so Zen Cart can restore the session.
            // add_session_id=false: we already include it explicitly (avoid duplicates).
            'pb_success_url' => zen_href_link(FILENAME_CHECKOUT_PROCESS, $successUrlParams, 'SSL', false, false),
            'pd_collect_shipping_information' => 'no',
            'pd_display_items' => 'no',
            // Custom fields must use pt_custom_name_N / pt_custom_value_N (not arbitrary keys).
            // Echoed back on callback and verified in before_process().
            'pt_custom_name_1' => 'zenid',
            'pt_custom_value_1' => $sessionId,
        ];

        $this->notify('NOTIFY_PAYMENT_PLUGNPAY_SS2_PRESUBMIT_HOOK');

        // Remember amount/account/session for basic return checks (and DB "sent" snapshot)
        $_SESSION['plugnpay_ss2_expected_amount'] = number_format($amount, 2, '.', '');
        $_SESSION['plugnpay_ss2_expected_account'] = $submit_data['pt_gateway_account'];
        $_SESSION['plugnpay_ss2_expected_session'] = $submit_data['pt_custom_value_1'];
        $_SESSION['plugnpay_ss2_submit_data'] = $submit_data;

        $this->reportable_submit_data = $submit_data;
        $this->debugLog('Submit-Data', $submit_data);

        $process_button_string = "\n";
        foreach ($submit_data as $key => $value) {
            $process_button_string .= zen_draw_hidden_field($key, $value) . "\n";
        }

        return $process_button_string;
    }

    public function before_process(): void
    {
        global $messageStack, $order;

        $this->authorize = $_POST;
        unset($this->authorize['btn_submit_x'], $this->authorize['btn_submit_y']);

        $this->notify('NOTIFY_PAYMENT_PLUGNPAY_SS2_POSTSUBMIT_HOOK', $this->authorize);
        $this->debugLog('Response-Data', $this->authorize);

        $status = strtolower(trim((string)($this->authorize['pi_response_status'] ?? '')));
        if ($status === '') {
            $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ERROR_MESSAGE, 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        if ($status === 'success') {
            // Basic return checks (hash/link verification can be added later)
            $returnedAmount = number_format((float)($this->authorize['pt_transaction_amount'] ?? 0), 2, '.', '');
            $expectedAmount = (string)($_SESSION['plugnpay_ss2_expected_amount'] ?? '');
            if ($expectedAmount !== '' && $returnedAmount !== $expectedAmount) {
                $this->debugLog('Amount mismatch', [
                    'expected' => $expectedAmount,
                    'returned' => $returnedAmount,
                ]);
                $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_AMOUNT_MISMATCH, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            }

            $returnedAccount = trim((string)($this->authorize['pt_gateway_account'] ?? ''));
            $expectedAccount = (string)($_SESSION['plugnpay_ss2_expected_account'] ?? '');
            if ($expectedAccount !== '' && $returnedAccount !== '' && strcasecmp($returnedAccount, $expectedAccount) !== 0) {
                $this->debugLog('Account mismatch', [
                    'expected' => $expectedAccount,
                    'returned' => $returnedAccount,
                ]);
                $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ACCOUNT_MISMATCH, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            }

            $returnedSession = $this->extractReturnedSessionId($this->authorize);
            $expectedSession = (string)($_SESSION['plugnpay_ss2_expected_session'] ?? zen_session_id());
            $currentSession = zen_session_id();
            if ($returnedSession === ''
                || ($expectedSession !== '' && !hash_equals($expectedSession, $returnedSession))
                || ($currentSession !== '' && !hash_equals($currentSession, $returnedSession))
            ) {
                $this->debugLog('Session mismatch', [
                    'expected' => $expectedSession,
                    'current' => $currentSession,
                    'returned' => $returnedSession,
                ]);
                $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_SESSION_MISMATCH, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            }

            $order->info['cc_type'] = (string)($this->authorize['pt_card_type'] ?? '');
            $cardNumber = (string)($this->authorize['pt_card_number'] ?? '');
            $order->info['cc_number'] = $cardNumber !== ''
                ? str_pad(substr($cardNumber, -4), strlen($cardNumber), 'X', STR_PAD_LEFT)
                : '';
            $order->info['cc_owner'] = (string)($this->authorize['pt_payment_name'] ?? '');
            $this->auth_code = (string)($this->authorize['pt_authorization_code'] ?? '');
            $this->transaction_id = (string)($this->authorize['pt_order_id'] ?? '');
            $this->cc_card_type = $order->info['cc_type'];
            $this->cc_card_number = $order->info['cc_number'];

            $this->storeTransactionRow('success');
            return;
        }

        $gatewayMsg = trim((string)($this->authorize['pi_error_message'] ?? ''));
        if ($status === 'badcard' || $status === 'fraud') {
            $customerMsg = MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_DECLINED_MESSAGE;
            if ($gatewayMsg !== '') {
                $customerMsg = $gatewayMsg . ' ' . $customerMsg;
            }
            $this->storeTransactionRow($status);
            $messageStack->add_session('checkout_payment', $customerMsg, 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $this->storeTransactionRow($status !== '' ? $status : 'error');
        $messageStack->add_session(
            'checkout_payment',
            $gatewayMsg !== '' ? $gatewayMsg : MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ERROR_MESSAGE,
            'error'
        );
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }

    public function after_process(): bool
    {
        global $insert_id, $order, $currencies;

        $this->notify('NOTIFY_PAYMENT_PLUGNPAY_SS2_POSTPROCESS_HOOK');

        $comments = 'Credit Card authorization via PlugnPay Smart Screens v2. AUTH: ' . $this->auth_code .
            ' TransID/orderID: ' . $this->transaction_id .
            ' (auth-only; settle in PlugnPay Admin)';
        if (isset($order->info['currency']) && $order->info['currency'] !== $this->gateway_currency) {
            $comments .= ' (' .
                number_format((float)$order->info['total'] * (float)$currencies->get_value($this->gateway_currency), 2) .
                ' ' . $this->gateway_currency . ')';
        }
        zen_update_orders_history($insert_id, $comments, null, $this->order_status, -1);

        // Update DB row with Zen Cart order id when available
        $this->updateStoredOrderId((int)$insert_id);

        unset(
            $_SESSION['plugnpay_ss2_expected_amount'],
            $_SESSION['plugnpay_ss2_expected_account'],
            $_SESSION['plugnpay_ss2_expected_session'],
            $_SESSION['plugnpay_ss2_submit_data']
        );

        return false;
    }

    public function get_error(): array
    {
        return [
            'title' => MODULE_PAYMENT_PLUGNPAY_SS2_TEXT_ERROR,
            'error' => stripslashes(urldecode($_GET['error'] ?? '')),
        ];
    }

    public function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute(
                "SELECT configuration_value FROM " . TABLE_CONFIGURATION .
                " WHERE configuration_key = 'MODULE_PAYMENT_PLUGNPAY_SS2_STATUS'"
            );
            $this->_check = $check_query->RecordCount();
        }
        if ($this->_check > 0) {
            $this->keys();
        }
        return $this->_check;
    }

    public function install(): void
    {
        global $db, $messageStack, $sniffer;

        if (defined('MODULE_PAYMENT_PLUGNPAY_SS2_STATUS')) {
            $messageStack->add_session(sprintf(TEXT_ERROR_MODULE_ALREADY_INSTALLED, $this->title), 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=plugnpay_ss2', 'NONSSL'));
            return;
        }

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable PlugnPay Smart Screens v2 Module', 'MODULE_PAYMENT_PLUGNPAY_SS2_STATUS', 'True', 'Do you want to accept payments via PlugnPay Smart Screens v2 (hosted, authorization-only)?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Gateway Account', 'MODULE_PAYMENT_PLUGNPAY_SS2_LOGIN', '', 'Your PlugnPay gateway account username (pt_gateway_account / publisher-name)', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Supported', 'MODULE_PAYMENT_PLUGNPAY_SS2_CURRENCY', 'USD', 'Which currency is your PlugnPay Gateway Account configured to accept?<br>(Purchases in any other currency will be pre-converted to this currency before submission using the exchange rates in your store admin.)', '6', '0', 'zen_cfg_select_option(array(\'USD\', \'CAD\', \'GBP\', \'EUR\', \'AUD\', \'NZD\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_PLUGNPAY_SS2_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_PLUGNPAY_SS2_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Database Storage', 'MODULE_PAYMENT_PLUGNPAY_SS2_STORE_DATA', 'True', 'Save gateway submit/response data to the plugnpay_ss2 database table?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Logging', 'MODULE_PAYMENT_PLUGNPAY_SS2_DEBUGGING', 'Off', 'Write sanitized gateway request/response logs under the store logs directory (never logs full PAN/CVV/password)', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Log File\'), ', now())");

        if ($sniffer->table_exists(TABLE_PLUGNPAY_SS2)) {
            $db->Execute("DROP TABLE " . TABLE_PLUGNPAY_SS2);
        }
        $sql = "CREATE TABLE " . TABLE_PLUGNPAY_SS2 . " (
          id int(25) unsigned NOT NULL auto_increment,
          customer_id varchar(30) NOT NULL default '',
          order_id varchar(30) NOT NULL default '',
          response_code varchar(32) NOT NULL default '',
          response_text varchar(255) NOT NULL default '',
          authorization_type varchar(25) NOT NULL default '',
          transaction_id varchar(255) NOT NULL default '',
          sent text,
          received text,
          time varchar(255) NOT NULL default '',
          session_id varchar(255) NOT NULL default '',
          PRIMARY KEY (id),
          KEY idx_customer_id_zen (customer_id)
        )";
        $db->Execute($sql);
    }

    public function remove(): void
    {
        global $db, $sniffer;
        $db->Execute(
            "DELETE FROM " . TABLE_CONFIGURATION .
            " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PLUGNPAY\_SS2\_%'"
        );
        if ($sniffer->table_exists(TABLE_PLUGNPAY_SS2)) {
            $db->Execute("DROP TABLE " . TABLE_PLUGNPAY_SS2);
        }
    }

    public function keys(): array
    {
        // Drop obsolete keys from earlier builds (admin API ops / auth type / statuses)
        if (defined('MODULE_PAYMENT_PLUGNPAY_SS2_STATUS')) {
            global $db;
            $db->Execute(
                "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN (" .
                "'MODULE_PAYMENT_PLUGNPAY_SS2_PASSWORD'," .
                "'MODULE_PAYMENT_PLUGNPAY_SS2_AUTHTYPE'," .
                "'MODULE_PAYMENT_PLUGNPAY_SS2_ORDER_STATUS_ID'," .
                "'MODULE_PAYMENT_PLUGNPAY_SS2_REFUNDED_ORDER_STATUS_ID'" .
                ")"
            );
        }

        return [
            'MODULE_PAYMENT_PLUGNPAY_SS2_STATUS',
            'MODULE_PAYMENT_PLUGNPAY_SS2_LOGIN',
            'MODULE_PAYMENT_PLUGNPAY_SS2_CURRENCY',
            'MODULE_PAYMENT_PLUGNPAY_SS2_SORT_ORDER',
            'MODULE_PAYMENT_PLUGNPAY_SS2_ZONE',
            'MODULE_PAYMENT_PLUGNPAY_SS2_STORE_DATA',
            'MODULE_PAYMENT_PLUGNPAY_SS2_DEBUGGING',
        ];
    }

    /**
     * Pull the returned zenid / session id from a Smart Screens callback POST.
     *
     * Prefers matching pt_custom_name_N=zenid → pt_custom_value_N, then a flat zenid field.
     *
     * @param array<string, mixed> $response
     */
    private function extractReturnedSessionId(array $response): string
    {
        for ($i = 1; $i <= 10; $i++) {
            $nameKey = 'pt_custom_name_' . $i;
            $valueKey = 'pt_custom_value_' . $i;
            if (!isset($response[$nameKey])) {
                continue;
            }
            if (strtolower(trim((string)$response[$nameKey])) === 'zenid') {
                return trim((string)($response[$valueKey] ?? ''));
            }
        }

        if (isset($response['zenid'])) {
            return trim((string)$response['zenid']);
        }

        $sessionName = zen_session_name();
        if ($sessionName !== '' && isset($response[$sessionName])) {
            return trim((string)$response[$sessionName]);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $context
     */
    private function debugLog(string $message, array $context = []): void
    {
        require_once __DIR__ . '/plugnpay_ss2/PnPSs2Logger.php';
        $debug = defined('MODULE_PAYMENT_PLUGNPAY_SS2_DEBUGGING')
            && MODULE_PAYMENT_PLUGNPAY_SS2_DEBUGGING === 'Log File';
        $logger = new PnPSs2Logger(
            $this->_logDir !== '' ? $this->_logDir : (defined('DIR_FS_LOGS') ? DIR_FS_LOGS : sys_get_temp_dir()),
            $debug
        );
        $logger->log($message, $context);
    }

    private function storeTransactionRow(string $responseCode): void
    {
        global $db, $sniffer;

        if (!defined('MODULE_PAYMENT_PLUGNPAY_SS2_STORE_DATA')
            || MODULE_PAYMENT_PLUGNPAY_SS2_STORE_DATA !== 'True'
        ) {
            return;
        }
        if (!$sniffer->table_exists(TABLE_PLUGNPAY_SS2)) {
            return;
        }

        require_once __DIR__ . '/plugnpay_ss2/PnPSs2Logger.php';
        $logger = new PnPSs2Logger('', false);
        $sentRaw = $this->reportable_submit_data !== []
            ? $this->reportable_submit_data
            : (array)($_SESSION['plugnpay_ss2_submit_data'] ?? []);
        $sent = $logger->sanitize($sentRaw);
        $received = $logger->sanitize($this->authorize);

        $sql = "INSERT INTO " . TABLE_PLUGNPAY_SS2 .
            " (customer_id, order_id, response_code, response_text, authorization_type, transaction_id, sent, received, time, session_id)" .
            " VALUES (:custID, '', :respCode, :respText, :authType, :transID, :sentData, :recvData, :orderTime, :sessID)";
        $sql = $db->bindVars($sql, ':custID', (string)($_SESSION['customer_id'] ?? ''), 'string');
        $sql = $db->bindVars($sql, ':respCode', $responseCode, 'string');
        $sql = $db->bindVars($sql, ':respText', (string)($this->authorize['pi_error_message'] ?? ''), 'string');
        $sql = $db->bindVars($sql, ':authType', (string)($this->authorize['pt_card_type'] ?? ''), 'string');
        $sql = $db->bindVars($sql, ':transID', (string)($this->authorize['pt_order_id'] ?? ''), 'string');
        $sql = $db->bindVars($sql, ':sentData', print_r($sent, true), 'string');
        $sql = $db->bindVars($sql, ':recvData', print_r($received, true), 'string');
        $sql = $db->bindVars($sql, ':orderTime', date('F j, Y, g:i a'), 'string');
        $sql = $db->bindVars($sql, ':sessID', zen_session_id(), 'string');
        $db->Execute($sql);

        $_SESSION['plugnpay_ss2_last_row_id'] = (int)$db->Insert_ID();
    }

    private function updateStoredOrderId(int $orderId): void
    {
        global $db, $sniffer;

        if ($orderId < 1) {
            return;
        }
        if (!defined('MODULE_PAYMENT_PLUGNPAY_SS2_STORE_DATA')
            || MODULE_PAYMENT_PLUGNPAY_SS2_STORE_DATA !== 'True'
        ) {
            return;
        }
        if (!$sniffer->table_exists(TABLE_PLUGNPAY_SS2)) {
            return;
        }

        $rowId = (int)($_SESSION['plugnpay_ss2_last_row_id'] ?? 0);
        if ($rowId > 0) {
            $db->Execute(
                "UPDATE " . TABLE_PLUGNPAY_SS2 .
                " SET order_id = '" . (int)$orderId . "' WHERE id = " . (int)$rowId
            );
            unset($_SESSION['plugnpay_ss2_last_row_id']);
            return;
        }

        if ($this->transaction_id !== null && $this->transaction_id !== '') {
            $sql = "UPDATE " . TABLE_PLUGNPAY_SS2 .
                " SET order_id = :orderID WHERE transaction_id = :transID AND (order_id = '' OR order_id = '0') LIMIT 1";
            $sql = $db->bindVars($sql, ':orderID', (string)$orderId, 'string');
            $sql = $db->bindVars($sql, ':transID', (string)$this->transaction_id, 'string');
            $db->Execute($sql);
        }
    }

    private function tableCheckup(): void
    {
        // Reserved for future schema migrations of TABLE_PLUGNPAY_SS2.
    }
}
