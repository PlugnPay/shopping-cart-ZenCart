<?php
/**
 * PlugnPay Remote API payment method for Zen Cart 2.2.x
 *
 * Posts cardholder data from the merchant server to:
 * https://pay1.plugnpay.com/payment/pnpremote.cgi
 *
 * @copyright Copyright (c) PlugnPay Technologies
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

class plugnpay_api extends base
{
    public $code;
    public $title;
    public $description;
    public $enabled;
    public $sort_order;
    public $order_status;
    public $form_action_url;
    public $collectsCardDataOnsite = true;
    public $transaction_id = null;
    public $auth_code = null;

    private $_check;
    private string $_logDir = '';
    protected string $cc_card_number = '';
    protected string $cc_card_type = '';
    protected string $cc_expiry_month = '';
    protected string $cc_expiry_year = '';
    protected string $avs_response = '';
    protected string $ccv_response = '';

    public function __construct()
    {
        global $order, $messageStack;

        $this->code = 'plugnpay_api';
        $this->enabled = (defined('MODULE_PAYMENT_PLUGNPAY_API_STATUS') && MODULE_PAYMENT_PLUGNPAY_API_STATUS === 'True');

        if (IS_ADMIN_FLAG === true) {
            $this->title = MODULE_PAYMENT_PLUGNPAY_API_TEXT_ADMIN_TITLE;
            if ($this->enabled) {
                if (!defined('MODULE_PAYMENT_PLUGNPAY_API_LOGIN')
                    || MODULE_PAYMENT_PLUGNPAY_API_LOGIN === 'testing'
                    || !defined('MODULE_PAYMENT_PLUGNPAY_API_PASSWORD')
                    || MODULE_PAYMENT_PLUGNPAY_API_PASSWORD === ''
                ) {
                    $this->title .= '<span class="alert"> (Not Configured)</span>';
                } elseif (MODULE_PAYMENT_PLUGNPAY_API_TESTMODE === 'Test') {
                    $this->title .= '<span class="alert"> (in Testing mode)</span>';
                }
                if (!function_exists('curl_init')) {
                    $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_ERROR_CURL_NOT_FOUND, 'error');
                }
            }
        } else {
            $this->title = MODULE_PAYMENT_PLUGNPAY_API_TEXT_CATALOG_TITLE;
        }

        $this->description = MODULE_PAYMENT_PLUGNPAY_API_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_PLUGNPAY_API_SORT_ORDER')
            ? MODULE_PAYMENT_PLUGNPAY_API_SORT_ORDER
            : null;

        if (null === $this->sort_order) {
            return;
        }

        $this->form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false);
        $this->order_status = (int)DEFAULT_ORDERS_STATUS_ID;
        if (defined('MODULE_PAYMENT_PLUGNPAY_API_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_PLUGNPAY_API_ORDER_STATUS_ID > 0) {
            $this->order_status = (int)MODULE_PAYMENT_PLUGNPAY_API_ORDER_STATUS_ID;
        }
        // Auth-only: keep order pending until capture
        if (defined('MODULE_PAYMENT_PLUGNPAY_API_AUTHTYPE') && MODULE_PAYMENT_PLUGNPAY_API_AUTHTYPE === 'authonly') {
            $this->order_status = 1;
        }

        $this->_logDir = defined('DIR_FS_LOGS') ? DIR_FS_LOGS : DIR_FS_SQL_CACHE;

        if (is_object($order)) {
            $this->update_status();
        }
    }

    public function update_status(): void
    {
        global $order, $db;

        if (IS_ADMIN_FLAG === false) {
            if (defined('MODULE_PAYMENT_PLUGNPAY_API_TESTMODE')
                && MODULE_PAYMENT_PLUGNPAY_API_TESTMODE === 'Production'
                && substr(HTTP_SERVER, 0, 6) !== 'https:'
                && (!defined('ENABLE_SSL') || ENABLE_SSL != 'true')
            ) {
                $this->enabled = false;
            }
        }

        if ($this->enabled
            && defined('MODULE_PAYMENT_PLUGNPAY_API_ZONE')
            && (int)MODULE_PAYMENT_PLUGNPAY_API_ZONE > 0
            && isset($order->billing['country']['id'])
        ) {
            $check_flag = false;
            $check = $db->Execute(
                "SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES .
                " WHERE geo_zone_id = '" . (int)MODULE_PAYMENT_PLUGNPAY_API_ZONE .
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
        $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
            '    var cc_owner = document.checkout_payment.plugnpay_api_cc_owner.value;' . "\n" .
            '    var cc_number = document.checkout_payment.plugnpay_api_cc_number.value;' . "\n";
        if (defined('MODULE_PAYMENT_PLUGNPAY_API_USE_CVV') && MODULE_PAYMENT_PLUGNPAY_API_USE_CVV === 'True') {
            $js .= '    var cc_cvv = document.checkout_payment.plugnpay_api_cc_cvv.value;' . "\n";
        }
        $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_PLUGNPAY_API_TEXT_JS_CC_OWNER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_PLUGNPAY_API_TEXT_JS_CC_NUMBER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n";
        if (defined('MODULE_PAYMENT_PLUGNPAY_API_USE_CVV') && MODULE_PAYMENT_PLUGNPAY_API_USE_CVV === 'True') {
            $js .= '    if (cc_cvv == "" || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n" .
                '      error_message = error_message + "' . MODULE_PAYMENT_PLUGNPAY_API_TEXT_JS_CC_CVV . '";' . "\n" .
                '      error = 1;' . "\n" .
                '    }' . "\n";
        }
        $js .= '  }' . "\n";

        return $js;
    }

    public function selection(): array
    {
        global $order, $zcDate;

        $expires_month = [];
        for ($i = 1; $i < 13; $i++) {
            $expires_month[] = [
                'id' => sprintf('%02d', $i),
                'text' => $zcDate->output('%B - (%m)', mktime(0, 0, 0, $i, 1, 2000)),
            ];
        }

        $today = getdate();
        $expires_year = [];
        for ($i = $today['year']; $i < $today['year'] + 15; $i++) {
            $expires_year[] = [
                'id' => $zcDate->output('%y', mktime(0, 0, 0, 1, 1, $i)),
                'text' => $zcDate->output('%Y', mktime(0, 0, 0, 1, 1, $i)),
            ];
        }

        $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

        $selection = [
            'id' => $this->code,
            'module' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CATALOG_TITLE,
            'fields' => [
                [
                    'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_OWNER,
                    'field' => zen_draw_input_field(
                        'plugnpay_api_cc_owner',
                        $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                        'id="' . $this->code . '-cc-owner"' . $onFocus . ' autocomplete="cc-name"'
                    ),
                    'tag' => $this->code . '-cc-owner',
                ],
                [
                    'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_NUMBER,
                    'field' => zen_draw_input_field(
                        'plugnpay_api_cc_number',
                        '',
                        'id="' . $this->code . '-cc-number"' . $onFocus . ' autocomplete="cc-number"'
                    ),
                    'tag' => $this->code . '-cc-number',
                ],
                [
                    'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_EXPIRES,
                    'field' => zen_draw_pull_down_menu(
                        'plugnpay_api_cc_expires_month',
                        $expires_month,
                        $zcDate->output('%m'),
                        'id="' . $this->code . '-cc-expires-month"' . $onFocus
                    ) . '&nbsp;' . zen_draw_pull_down_menu(
                        'plugnpay_api_cc_expires_year',
                        $expires_year,
                        '',
                        'id="' . $this->code . '-cc-expires-year"' . $onFocus
                    ),
                    'tag' => $this->code . '-cc-expires-month',
                ],
            ],
        ];

        if (defined('MODULE_PAYMENT_PLUGNPAY_API_USE_CVV') && MODULE_PAYMENT_PLUGNPAY_API_USE_CVV === 'True') {
            $selection['fields'][] = [
                'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CVV,
                'field' => zen_draw_input_field(
                    'plugnpay_api_cc_cvv',
                    '',
                    'size="4" maxlength="4" id="' . $this->code . '-cc-cvv"' . $onFocus . ' autocomplete="cc-csc"'
                ) . ' <a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' .
                    MODULE_PAYMENT_PLUGNPAY_API_TEXT_POPUP_CVV_LINK . '</a>',
                'tag' => $this->code . '-cc-cvv',
            ];
        }

        return $selection;
    }

    public function pre_confirmation_check(): void
    {
        global $messageStack;

        include DIR_WS_CLASSES . 'cc_validation.php';

        $cc_validation = new cc_validation();
        $result = $cc_validation->validate(
            $_POST['plugnpay_api_cc_number'] ?? '',
            $_POST['plugnpay_api_cc_expires_month'] ?? '',
            $_POST['plugnpay_api_cc_expires_year'] ?? '',
            $_POST['plugnpay_api_cc_cvv'] ?? ''
        );

        $error = '';
        switch ($result) {
            case -1:
                $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
                break;
            case -2:
            case -3:
            case -4:
                $error = TEXT_CCVAL_ERROR_INVALID_DATE;
                break;
            case false:
                $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
                break;
        }

        if (($result == false) || ($result < 1)) {
            $messageStack->add_session('checkout_payment', $error . '<!-- [' . $this->code . '] -->', 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $this->cc_card_type = $cc_validation->cc_type;
        $this->cc_card_number = $cc_validation->cc_number;
        $this->cc_expiry_month = $cc_validation->cc_expiry_month;
        $this->cc_expiry_year = $cc_validation->cc_expiry_year;
    }

    public function confirmation(): array
    {
        global $zcDate;

        return [
            'fields' => [
                [
                    'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_TYPE,
                    'field' => $this->cc_card_type,
                ],
                [
                    'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_OWNER,
                    'field' => $_POST['plugnpay_api_cc_owner'] ?? '',
                ],
                [
                    'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_NUMBER,
                    'field' => substr($this->cc_card_number, 0, 4) .
                        str_repeat('X', max(0, strlen($this->cc_card_number) - 8)) .
                        substr($this->cc_card_number, -4),
                ],
                [
                    'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_CREDIT_CARD_EXPIRES,
                    'field' => $zcDate->output(
                        '%B, %Y',
                        mktime(0, 0, 0, (int)($_POST['plugnpay_api_cc_expires_month'] ?? 1), 1, (int)('20' . ($_POST['plugnpay_api_cc_expires_year'] ?? '00')))
                    ),
                ],
            ],
        ];
    }

    public function process_button(): string
    {
        $process_button_string = zen_draw_hidden_field('cc_owner', $_POST['plugnpay_api_cc_owner'] ?? '') .
            zen_draw_hidden_field('cc_expires', $this->cc_expiry_month . substr($this->cc_expiry_year, -2)) .
            zen_draw_hidden_field('cc_type', $this->cc_card_type) .
            zen_draw_hidden_field('cc_number', $this->cc_card_number);
        if (defined('MODULE_PAYMENT_PLUGNPAY_API_USE_CVV') && MODULE_PAYMENT_PLUGNPAY_API_USE_CVV === 'True') {
            $process_button_string .= zen_draw_hidden_field('cc_cvv', $_POST['plugnpay_api_cc_cvv'] ?? '');
        }
        $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

        return $process_button_string;
    }

    public function process_button_ajax(): array
    {
        return [
            'ccFields' => [
                'cc_number' => 'plugnpay_api_cc_number',
                'cc_owner' => 'plugnpay_api_cc_owner',
                'cc_cvv' => 'plugnpay_api_cc_cvv',
                'cc_expires' => [
                    'name' => 'concatExpiresFields',
                    'args' => "['plugnpay_api_cc_expires_month','plugnpay_api_cc_expires_year']",
                ],
                'cc_expires_month' => 'plugnpay_api_cc_expires_month',
                'cc_expires_year' => 'plugnpay_api_cc_expires_year',
                'cc_type' => $this->cc_card_type,
            ],
            'extraFields' => [zen_session_name() => zen_session_id()],
        ];
    }

    public function before_process(): void
    {
        global $order, $messageStack;

        $order->info['cc_owner'] = $_POST['cc_owner'] ?? '';
        $cc_number = (string)($_POST['cc_number'] ?? '');
        $order->info['cc_number'] = str_pad(substr($cc_number, -4), strlen($cc_number), 'X', STR_PAD_LEFT);
        $order->info['cc_expires'] = '';
        $order->info['cc_cvv'] = '***';
        $order->info['cc_type'] = $_POST['cc_type'] ?? '';

        $api = $this->getApiClient();
        $fields = $this->buildAuthorizeFields($order, $cc_number);
        $response = $api->authorize($fields);

        if ($api->getCommErrNo() !== 0 || $api->getLastRawResponse() === '') {
            $messageStack->add_session(
                'checkout_payment',
                MODULE_PAYMENT_PLUGNPAY_API_TEXT_COMM_ERROR . ' (' . $api->getCommErrNo() . ')',
                'caution'
            );
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        $this->auth_code = (string)($response['auth-code'] ?? $response['auth_code'] ?? '');
        $this->transaction_id = (string)($response['orderID'] ?? $response['orderid'] ?? '');
        $this->avs_response = (string)($response['avs-code'] ?? $response['avs_code'] ?? '');
        $this->ccv_response = (string)($response['cvvresp'] ?? '');

        if (!$api->isApproved($response)) {
            $final = (string)($response['FinalStatus'] ?? '');
            $gatewayMsg = trim((string)($response['MErrMsg'] ?? ''));
            $customerMsg = MODULE_PAYMENT_PLUGNPAY_API_TEXT_DECLINED_MESSAGE;
            if ($gatewayMsg !== '') {
                $customerMsg .= ' -- ' . $gatewayMsg;
            }
            if ($final === 'fraud') {
                $customerMsg = 'Your transaction was rejected. Please contact the merchant for ordering assistance.' .
                    ($gatewayMsg !== '' ? ' -- ' . $gatewayMsg : '');
            }
            $messageStack->add_session('checkout_payment', $customerMsg, 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }
    }

    public function after_process(): bool
    {
        global $insert_id;

        $comments = 'Credit Card payment via PlugnPay. AUTH: ' . $this->auth_code .
            ' TransID/orderID: ' . $this->transaction_id;
        if ($this->avs_response !== '') {
            $comments .= ' AVS: ' . $this->avs_response;
        }
        if ($this->ccv_response !== '') {
            $comments .= ' CVV: ' . $this->ccv_response;
        }
        zen_update_orders_history($insert_id, $comments, null, $this->order_status, -1);

        return false;
    }

    public function admin_notification($zf_order_id): string
    {
        $output = '';
        require __DIR__ . '/plugnpay_api/admin_notification.php';
        return $output;
    }

    public function get_error(): array
    {
        return [
            'title' => MODULE_PAYMENT_PLUGNPAY_API_TEXT_ERROR,
            'error' => stripslashes(urldecode($_GET['error'] ?? '')),
        ];
    }

    public function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute(
                "SELECT configuration_value FROM " . TABLE_CONFIGURATION .
                " WHERE configuration_key = 'MODULE_PAYMENT_PLUGNPAY_API_STATUS'"
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
        global $db, $messageStack;

        if (defined('MODULE_PAYMENT_PLUGNPAY_API_STATUS')) {
            $messageStack->add_session(sprintf(TEXT_ERROR_MODULE_ALREADY_INSTALLED, $this->title), 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=plugnpay_api', 'NONSSL'));
            return;
        }

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable PlugnPay Remote API Module', 'MODULE_PAYMENT_PLUGNPAY_API_STATUS', 'True', 'Do you want to accept credit card payments via PlugnPay Remote API?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Publisher Name', 'MODULE_PAYMENT_PLUGNPAY_API_LOGIN', 'testing', 'PlugnPay gateway account username (publisher-name)', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Remote Client Password', 'MODULE_PAYMENT_PLUGNPAY_API_PASSWORD', '', 'Remote Client Password from PlugnPay Security Administration (publisher-password). This is NOT your admin login password.', '6', '0', now(), 'zen_cfg_password_display')");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Publisher Email', 'MODULE_PAYMENT_PLUGNPAY_API_PUBEMAIL', '', 'Merchant confirmation / notify-email address used by PlugnPay', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Transaction Mode', 'MODULE_PAYMENT_PLUGNPAY_API_TESTMODE', 'Test', 'Test = development/testing; Production = live charges (requires HTTPS)', '6', '0', 'zen_cfg_select_option(array(\'Test\', \'Production\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Authorization Type', 'MODULE_PAYMENT_PLUGNPAY_API_AUTHTYPE', 'authonly', 'authonly = authorize only (capture later); authpostauth = authorize and settle (sale)', '6', '0', 'zen_cfg_select_option(array(\'authonly\', \'authpostauth\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Prevent Gateway Customer Email', 'MODULE_PAYMENT_PLUGNPAY_API_EMAILCUST', 'yes', 'If yes, sets dontsndmail so PlugnPay does not email the customer a gateway receipt', '6', '0', 'zen_cfg_select_option(array(\'yes\', \'no\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Request CVV Number', 'MODULE_PAYMENT_PLUGNPAY_API_USE_CVV', 'True', 'Ask the customer for the card CVV number?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_PLUGNPAY_API_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Payment Zone', 'MODULE_PAYMENT_PLUGNPAY_API_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Completed Order Status', 'MODULE_PAYMENT_PLUGNPAY_API_ORDER_STATUS_ID', '2', 'Status for completed (sale / captured) orders. Auth-only forces Pending (1) until capture.', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refunded Order Status', 'MODULE_PAYMENT_PLUGNPAY_API_REFUNDED_ORDER_STATUS_ID', '1', 'Status for refunded orders', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Logging', 'MODULE_PAYMENT_PLUGNPAY_API_DEBUGGING', 'Off', 'Write sanitized gateway request/response logs under the store logs directory (never logs full PAN/CVV/password)', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Log File\'), ', now())");
    }

    public function remove(): void
    {
        global $db;
        $db->Execute(
            "DELETE FROM " . TABLE_CONFIGURATION .
            " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PLUGNPAY\_API\_%'"
        );
    }

    public function keys(): array
    {
        if (defined('MODULE_PAYMENT_PLUGNPAY_API_STATUS')) {
            global $db;
            if (!defined('MODULE_PAYMENT_PLUGNPAY_API_PASSWORD')) {
                $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function) VALUES ('Remote Client Password', 'MODULE_PAYMENT_PLUGNPAY_API_PASSWORD', '', 'Remote Client Password from PlugnPay Security Administration (publisher-password). This is NOT your admin login password.', '6', '0', now(), 'zen_cfg_password_display')");
            }
            if (!defined('MODULE_PAYMENT_PLUGNPAY_API_REFUNDED_ORDER_STATUS_ID')) {
                $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Refunded Order Status', 'MODULE_PAYMENT_PLUGNPAY_API_REFUNDED_ORDER_STATUS_ID', '1', 'Status for refunded orders', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
            }
            if (!defined('MODULE_PAYMENT_PLUGNPAY_API_DEBUGGING')) {
                $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Debug Logging', 'MODULE_PAYMENT_PLUGNPAY_API_DEBUGGING', 'Off', 'Write sanitized gateway request/response logs under the store logs directory (never logs full PAN/CVV/password)', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Log File\'), ', now())");
            }
        }

        return [
            'MODULE_PAYMENT_PLUGNPAY_API_STATUS',
            'MODULE_PAYMENT_PLUGNPAY_API_LOGIN',
            'MODULE_PAYMENT_PLUGNPAY_API_PASSWORD',
            'MODULE_PAYMENT_PLUGNPAY_API_PUBEMAIL',
            'MODULE_PAYMENT_PLUGNPAY_API_TESTMODE',
            'MODULE_PAYMENT_PLUGNPAY_API_AUTHTYPE',
            'MODULE_PAYMENT_PLUGNPAY_API_EMAILCUST',
            'MODULE_PAYMENT_PLUGNPAY_API_USE_CVV',
            'MODULE_PAYMENT_PLUGNPAY_API_SORT_ORDER',
            'MODULE_PAYMENT_PLUGNPAY_API_ZONE',
            'MODULE_PAYMENT_PLUGNPAY_API_ORDER_STATUS_ID',
            'MODULE_PAYMENT_PLUGNPAY_API_REFUNDED_ORDER_STATUS_ID',
            'MODULE_PAYMENT_PLUGNPAY_API_DEBUGGING',
        ];
    }

    /**
     * Refund via PlugnPay mode=return
     */
    public function _doRefund($oID, $amount = 0): bool
    {
        global $messageStack;

        $new_order_status = (int)MODULE_PAYMENT_PLUGNPAY_API_REFUNDED_ORDER_STATUS_ID;
        if ($new_order_status === 0) {
            $new_order_status = (int)DEFAULT_ORDERS_STATUS_ID;
        }

        $proceed = true;
        $refundNote = strip_tags(zen_db_input($_POST['refnote'] ?? ''));
        if (!isset($_POST['refconfirm']) || $_POST['refconfirm'] !== 'on') {
            $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_REFUND_CONFIRM_ERROR, 'error');
            $proceed = false;
        }

        $refundAmt = 0.0;
        if (isset($_POST['buttonrefund']) && $_POST['buttonrefund'] === MODULE_PAYMENT_PLUGNPAY_API_ENTRY_REFUND_BUTTON_TEXT) {
            $refundAmt = (float)($_POST['refamt'] ?? 0);
            if ($refundAmt <= 0) {
                $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_INVALID_REFUND_AMOUNT, 'error');
                $proceed = false;
            }
        }

        $orderId = trim((string)($_POST['trans_id'] ?? ''));
        if ($orderId === '') {
            $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
            $proceed = false;
        }

        if ($proceed !== true) {
            return false;
        }

        $api = $this->getApiClient();
        $response = $api->returnFunds($orderId, number_format($refundAmt, 2, '.', ''));

        if (!$api->isApproved($response)) {
            $messageStack->add_session(
                trim((string)($response['MErrMsg'] ?? MODULE_PAYMENT_PLUGNPAY_API_TEXT_COMM_ERROR)),
                'error'
            );
            return false;
        }

        $comments = 'REFUND INITIATED. PlugnPay orderID: ' . $orderId .
            ' Amount: ' . number_format($refundAmt, 2, '.', '') . "\n" . $refundNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 1);
        $messageStack->add_session(sprintf(MODULE_PAYMENT_PLUGNPAY_API_TEXT_REFUND_INITIATED, $orderId), 'success');

        return true;
    }

    /**
     * Capture via PlugnPay mode=mark
     */
    public function _doCapt($oID, $amt = 0, $currency = 'USD'): bool
    {
        global $messageStack;

        $new_order_status = (int)MODULE_PAYMENT_PLUGNPAY_API_ORDER_STATUS_ID;
        if ($new_order_status === 0) {
            $new_order_status = (int)DEFAULT_ORDERS_STATUS_ID;
        }

        $proceed = true;
        $captureNote = strip_tags(zen_db_input($_POST['captnote'] ?? ''));
        if (!isset($_POST['captconfirm']) || $_POST['captconfirm'] !== 'on') {
            $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_CAPTURE_CONFIRM_ERROR, 'error');
            $proceed = false;
        }

        $captureAmt = (float)($_POST['captamt'] ?? 0);
        if ($captureAmt <= 0) {
            $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_INVALID_CAPTURE_AMOUNT, 'error');
            $proceed = false;
        }

        $orderId = trim((string)($_POST['captauthid'] ?? ''));
        if ($orderId === '') {
            $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
            $proceed = false;
        }

        if ($proceed !== true) {
            return false;
        }

        $api = $this->getApiClient();
        $formattedAmt = number_format($captureAmt, 2, '.', '');
        $response = $api->mark($orderId, $formattedAmt);

        if (!$api->isApproved($response)) {
            $messageStack->add_session(
                trim((string)($response['MErrMsg'] ?? MODULE_PAYMENT_PLUGNPAY_API_TEXT_COMM_ERROR)),
                'error'
            );
            return false;
        }

        $comments = 'CAPTURE INITIATED. PlugnPay orderID: ' . $orderId .
            ' Amount: ' . $formattedAmt . "\n" . $captureNote;
        zen_update_orders_history($oID, $comments, null, $new_order_status, 1);
        $messageStack->add_session(
            sprintf(MODULE_PAYMENT_PLUGNPAY_API_TEXT_CAPT_INITIATED, $formattedAmt, $orderId),
            'success'
        );

        return true;
    }

    /**
     * Void via PlugnPay mode=void
     */
    public function _doVoid($oID, $note = ''): bool
    {
        global $messageStack, $db;

        $proceed = true;
        $voidNote = strip_tags(zen_db_input($_POST['voidnote'] ?? $note));
        if (!isset($_POST['voidconfirm']) || $_POST['voidconfirm'] !== 'on') {
            $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_VOID_CONFIRM_ERROR, 'error');
            $proceed = false;
        }

        $orderId = trim((string)($_POST['voidauthid'] ?? ''));
        if ($orderId === '') {
            $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_TRANS_ID_REQUIRED_ERROR, 'error');
            $proceed = false;
        }

        $voidAmt = trim((string)($_POST['voidamt'] ?? ''));
        if ($voidAmt === '') {
            // Fall back to order total if amount not supplied
            $ot = $db->Execute(
                "SELECT value FROM " . TABLE_ORDERS_TOTAL .
                " WHERE orders_id = " . (int)$oID . " AND class = 'ot_total' LIMIT 1"
            );
            if (!$ot->EOF) {
                $voidAmt = number_format((float)$ot->fields['value'], 2, '.', '');
            }
        }

        if ($voidAmt === '' || (float)$voidAmt <= 0) {
            $messageStack->add_session(MODULE_PAYMENT_PLUGNPAY_API_TEXT_INVALID_CAPTURE_AMOUNT, 'error');
            $proceed = false;
        }

        if ($proceed !== true) {
            return false;
        }

        $api = $this->getApiClient();
        $formattedAmt = number_format((float)$voidAmt, 2, '.', '');
        $response = $api->void($orderId, $formattedAmt);

        if (!$api->isApproved($response)) {
            $messageStack->add_session(
                trim((string)($response['MErrMsg'] ?? MODULE_PAYMENT_PLUGNPAY_API_TEXT_COMM_ERROR)),
                'error'
            );
            return false;
        }

        $comments = 'VOID INITIATED. PlugnPay orderID: ' . $orderId . "\n" . $voidNote;
        zen_update_orders_history($oID, $comments, null, -1, 1);
        $messageStack->add_session(sprintf(MODULE_PAYMENT_PLUGNPAY_API_TEXT_VOID_INITIATED, $orderId), 'success');

        return true;
    }

    private function getApiClient(): PnPApi
    {
        require_once __DIR__ . '/plugnpay_api/PnPLogger.php';
        require_once __DIR__ . '/plugnpay_api/PnPApi.php';

        $debug = defined('MODULE_PAYMENT_PLUGNPAY_API_DEBUGGING')
            && MODULE_PAYMENT_PLUGNPAY_API_DEBUGGING === 'Log File';
        $logger = new PnPLogger($this->_logDir !== '' ? $this->_logDir : (defined('DIR_FS_LOGS') ? DIR_FS_LOGS : sys_get_temp_dir()), $debug);

        return new PnPApi(
            defined('MODULE_PAYMENT_PLUGNPAY_API_LOGIN') ? (string)MODULE_PAYMENT_PLUGNPAY_API_LOGIN : '',
            defined('MODULE_PAYMENT_PLUGNPAY_API_PASSWORD') ? (string)MODULE_PAYMENT_PLUGNPAY_API_PASSWORD : '',
            $logger
        );
    }

    /**
     * @return array<string, string>
     */
    private function buildAuthorizeFields(object $order, string $ccNumber): array
    {
        $amount = number_format((float)$order->info['total'], 2, '.', '');
        $expMonth = substr((string)($_POST['cc_expires'] ?? ''), 0, 2);
        $expYear = substr((string)($_POST['cc_expires'] ?? ''), 2, 2);

        $billingState = zen_get_zone_code(
            $order->billing['country']['id'],
            $order->billing['zone_id'],
            $order->billing['state'] ?? ''
        );
        $deliveryState = zen_get_zone_code(
            $order->delivery['country']['id'],
            $order->delivery['zone_id'],
            $order->delivery['state'] ?? ''
        );

        $billingCountry = $order->billing['country']['iso_code_2']
            ?? $order->billing['country']['iso_code_3']
            ?? '';
        $deliveryCountry = $order->delivery['country']['iso_code_2']
            ?? $order->delivery['country']['iso_code_3']
            ?? '';

        $authtype = (defined('MODULE_PAYMENT_PLUGNPAY_API_AUTHTYPE')
            && MODULE_PAYMENT_PLUGNPAY_API_AUTHTYPE === 'authpostauth')
            ? 'authpostauth'
            : 'authonly';

        $fields = [
            'mode' => 'auth',
            'paymethod' => 'credit',
            'authtype' => $authtype,
            'easycart' => '1',
            'shipinfo' => '1',
            'card-amount' => $amount,
            'card-number' => preg_replace('/\D/', '', $ccNumber),
            'card-exp' => $expMonth . '/' . $expYear,
            'card-name' => trim(($order->billing['firstname'] ?? '') . ' ' . ($order->billing['lastname'] ?? '')),
            'card-company' => (string)($order->billing['company'] ?? ''),
            'card-address1' => (string)($order->billing['street_address'] ?? ''),
            'card-address2' => (string)($order->billing['suburb'] ?? $order->billing['street_address2'] ?? ''),
            'card-city' => (string)($order->billing['city'] ?? ''),
            'card-state' => (string)$billingState,
            'card-zip' => (string)($order->billing['postcode'] ?? ''),
            'card-country' => (string)$billingCountry,
            'phone' => (string)($order->customer['telephone'] ?? ''),
            'email' => (string)($order->customer['email_address'] ?? ''),
            'shipname' => trim(($order->delivery['firstname'] ?? '') . ' ' . ($order->delivery['lastname'] ?? '')),
            'address1' => (string)($order->delivery['street_address'] ?? ''),
            'address2' => (string)($order->delivery['suburb'] ?? $order->delivery['street_address2'] ?? ''),
            'city' => (string)($order->delivery['city'] ?? ''),
            'state' => (string)$deliveryState,
            'zip' => (string)($order->delivery['postcode'] ?? ''),
            'country' => (string)$deliveryCountry,
            'shipping' => number_format((float)($order->info['shipping_cost'] ?? 0), 2, '.', ''),
            'tax' => number_format((float)($order->info['tax'] ?? 0), 2, '.', ''),
            'ipaddress' => zen_get_ip_address(),
            'dontsndmail' => (defined('MODULE_PAYMENT_PLUGNPAY_API_EMAILCUST') && MODULE_PAYMENT_PLUGNPAY_API_EMAILCUST === 'yes')
                ? 'yes'
                : 'no',
        ];

        if (defined('MODULE_PAYMENT_PLUGNPAY_API_PUBEMAIL') && MODULE_PAYMENT_PLUGNPAY_API_PUBEMAIL !== '') {
            $fields['publisher-email'] = MODULE_PAYMENT_PLUGNPAY_API_PUBEMAIL;
            $fields['notify-email'] = MODULE_PAYMENT_PLUGNPAY_API_PUBEMAIL;
        }

        if (defined('MODULE_PAYMENT_PLUGNPAY_API_USE_CVV')
            && MODULE_PAYMENT_PLUGNPAY_API_USE_CVV === 'True'
            && !empty($_POST['cc_cvv'])
        ) {
            $fields['card-cvv'] = (string)$_POST['cc_cvv'];
        }

        // $0: validity check only
        if ((float)$amount <= 0) {
            $fields['mode'] = 'checkcard';
            unset($fields['card-amount']);
        }

        // Line items
        if (!empty($order->products) && is_array($order->products)) {
            $j = 1;
            foreach ($order->products as $product) {
                $fields['item' . $j] = (string)($product['model'] ?? '');
                $fields['cost' . $j] = number_format((float)($product['final_price'] ?? 0), 2, '.', '');
                $fields['quantity' . $j] = (string)($product['qty'] ?? 1);
                $fields['description' . $j] = substr(strip_tags((string)($product['name'] ?? '')), 0, 255);
                $j++;
            }
        }

        if (defined('MODULE_PAYMENT_PLUGNPAY_API_TESTMODE') && MODULE_PAYMENT_PLUGNPAY_API_TESTMODE === 'Test') {
            $fields['zen-status'] = 'debug';
        }

        return $fields;
    }
}
