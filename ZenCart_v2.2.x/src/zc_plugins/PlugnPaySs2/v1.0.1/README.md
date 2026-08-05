# PlugnPay Smart Screens v2 Module for Zen Cart 2.2.x

**Version:** v1.0.1

Hosted **authorization-only** payments via PlugnPay Smart Screens v2 (`https://pay1.plugnpay.com/pay/`).

This is an **encapsulated Zen Cart plugin** (`zc_plugins`). It does not modify Zen Cart core files.

## Features

- Offsite / hosted checkout (card data collected on PlugnPay)
- Authorization-only (`pb_post_auth=no`) — orders stay Pending
- Dedicated `plugnpay_ss2` database table for gateway communications
- Debug logging with PAN / CVV / password redaction
- Basic return checks (amount, gateway account, session/`zenid`)
- PHP 8.2+ / Zen Cart 2.2.x compatible

This module does **not** include Zen Cart admin Capture / Void / Refund. Settle or reverse transactions in [PlugnPay Merchant Admin](https://pay1.plugnpay.com/admin/). For onsite checkout (`authonly` / `authpostauth`), use the Remote API module (`PlugnPayApi`).

## When to use this vs Remote API

| | Smart Screens v2 (this module) | Remote API (`PlugnPayApi`) |
|---|---|---|
| Card data | Collected on PlugnPay | Collected on your store |
| Customer experience | Redirect to hosted billing page | Stays on your checkout |
| PCI scope | Lower | Higher |
| Checkout endpoint | `https://pay1.plugnpay.com/pay/` | `pnpremote.cgi` (server-to-server) |
| Transaction mode | Authorization-only | Auth-only or sale (`authonly` / `authpostauth`) |
| Admin Capture / Void / Refund in Zen Cart | No | No |
| Public demo account | None — merchant credentials only | None — merchant credentials only |

## PCI notice

This module does **not** collect cardholder data on your server. Customers are redirected to PlugnPay’s hosted Smart Screens pages.

## Requirements

- Zen Cart **2.2.0+** (tested target: 2.2.2)
- PHP **8.2+**
- PlugnPay **gateway account username** (merchant-supplied; there is no public demo account)
- HTTPS on the storefront is **strongly recommended** so the return POST to `checkout_process` stays secure and session cookies work reliably

No Remote Client Password, cURL, or IP whitelist is required for this module (those apply to the Remote API module’s admin ops).

## Installation

1. Unzip `zencart_2.2.2_ss2_module.zip` into your Zen Cart **root** so you have:

   ```
   zc_plugins/PlugnPaySs2/v1.0.1/...
   ```

2. Admin → **Plugins** → Plugin Manager → install / enable **PlugnPay Smart Screens v2**.

3. Admin → **Modules** → **Payment** → install **PlugnPay Smart Screens v2**.

4. Configure the keys below, then place a test order with your merchant account (per your PlugnPay test procedures).

### Configuration reference

| Setting | Key | Notes |
|---|---|---|
| Enable | `MODULE_PAYMENT_PLUGNPAY_SS2_STATUS` | `True` / `False` |
| Gateway Account | `MODULE_PAYMENT_PLUGNPAY_SS2_LOGIN` | PlugnPay username (`pt_gateway_account`) |
| Currency Supported | `MODULE_PAYMENT_PLUGNPAY_SS2_CURRENCY` | USD, CAD, GBP, EUR, AUD, NZD |
| Sort order | `MODULE_PAYMENT_PLUGNPAY_SS2_SORT_ORDER` | Display order among payment methods |
| Payment Zone | `MODULE_PAYMENT_PLUGNPAY_SS2_ZONE` | Optional geo-zone restriction |
| Enable Database Storage | `MODULE_PAYMENT_PLUGNPAY_SS2_STORE_DATA` | Writes `{prefix}plugnpay_ss2` |
| Debug Logging | `MODULE_PAYMENT_PLUGNPAY_SS2_DEBUGGING` | `Off` or `Log File` |

Checkout always sends `pb_post_auth=no`. Successful orders are set to **Pending** (status `1`). Capture/settle in PlugnPay Admin when ready.

There is **no** Test/Production toggle and **no** public demo publisher.

## Checkout flow

1. Customer selects Credit Card on the payment page (no card fields on your store).
2. On Confirm Order, Zen Cart POSTs hidden order fields to `https://pay1.plugnpay.com/pay/`.
3. Customer completes payment on PlugnPay Smart Screens (authorization only).
4. PlugnPay POSTs back to your store’s `checkout_process` (`pb_success_url`, `pb_transition_type=post`).
5. Module `before_process()` validates the return (see below).
6. On success, the order is created as Pending; AUTH and orderID are written to order history.
7. On decline / error, the customer is returned to payment with the gateway message.

### Return validation (v1.0.1)

Accepted return POST must include `pi_response_status`. On `success`, the module also checks:

- Returned `pt_transaction_amount` matches the amount stored in session at submit time
- Returned `pt_gateway_account` matches the configured Gateway Account (when present)
- Returned `zenid` (from `pt_custom_name_N` / `pt_custom_value_N`, or a flat `zenid` field) matches the session ID sent at submit and the current Zen Cart session

Cryptographic response-link / hash verification is **not** included in v1.0.1 (planned follow-up).

**Session restore:** Smart Screens cannot carry a top-level `zenid=` POST key, so the module puts `zenid=<session>` on `pb_success_url` (query string). Zen Cart’s session bootstrap reads that GET param on the return POST and resumes the checkout session. The same session ID is also sent as `pt_custom_name_1` / `pt_custom_value_1` and verified in `before_process()`.

### Key fields submitted to `/pay/`

| Field | Purpose |
|---|---|
| `pt_gateway_account` | Merchant gateway account |
| `pt_transaction_amount` | Order total (converted to gateway currency if needed) |
| `pt_currency` / `pt_currency_code` | Currency sent to Smart Screens |
| `pb_post_auth` | Always `no` (authorization-only) |
| `pt_account_code_1` | Basket / cart ID |
| `pt_payment_name` + billing fields | Prefill billing on hosted page |
| `pb_success_url` | Return URL → store `checkout_process` **with `zenid=<session>` in the query string** (required for Zen Cart session resume) |
| `pb_transition_type` | `post` |
| `pd_display_items` | `no` (this module does not send line-itemization) |
| `pd_collect_shipping_information` | `no` |
| `pt_client_identifier` | `ZenCart_SS2` |
| `pt_custom_name_1` / `pt_custom_value_1` | Custom pair: name `zenid`, value = session ID (verified on return) |

Arbitrary custom keys (for example posting `zenid` directly as a Smart Screens field) are not part of the Smart Screens v2 spec; use `pt_custom_name_N` / `pt_custom_value_N` instead. Session resume for Zen Cart uses the `zenid` query param on `pb_success_url`, not a top-level gateway POST field.

## Logging

### File logs

Set **Debug Logging** to `Log File`. Sanitized logs are written under the Zen Cart logs directory as:

```
plugnpay_ss2_YYYYMMDD.log
```

### Database table

When **Enable Database Storage** is True, install creates `{prefix}plugnpay_ss2` and stores sanitized submit/response snapshots. Uninstall drops the table and removes module configuration keys.

Never logged: full card number, CVV, or publisher-password.

## Troubleshooting

| Symptom | What to check |
|---|---|
| Customer returns but order is not created / “session not found” | `pb_success_url` must include `zenid=<session>` and hit `checkout_process` on HTTPS; if cookies still drop, set `COOKIE_SAMESITE` to `none` via `includes/extra_configures/samesite_cookie.php` |
| “Amount did not match” | Cart total changed between confirm and return, or currency conversion mismatch |
| “Gateway account” mismatch | `pt_gateway_account` on return ≠ configured Gateway Account |
| “Payment session could not be verified” | Returned custom-field `zenid` missing or does not match the session sent at submit / current session |
| Decline / fraud message | Expected; customer is sent back to checkout payment |
| Module shows “(Not Configured)” | Gateway Account is empty |
| Need to capture / void / refund | Use PlugnPay Merchant Admin (not this Zen Cart module) |

## Manual test checklist

- [ ] Plugin installs in Plugin Manager
- [ ] Payment module installs and shows configuration keys (no password / auth-type / refund status)
- [ ] Empty Gateway Account shows “(Not Configured)” in Admin
- [ ] Approved payment creates a **Pending** order with AUTH + orderID history
- [ ] Declined card shows gateway message and restores checkout
- [ ] Submit uses `pb_post_auth=no` (visible in debug log / DB storage)
- [ ] `plugnpay_ss2` table stores rows when Store Data is enabled
- [ ] Debug log redacts PAN/CVV/password
- [ ] Payment Zone restriction works
- [ ] Currency conversion path works when store currency ≠ gateway currency
- [ ] Order edit screen has **no** Capture / Void / Refund tools for this module

## File map

```
zc_plugins/PlugnPaySs2/v1.0.1/
  manifest.php
  changelog.txt
  README.md
  Installer/ScriptedInstaller.php
  catalog/includes/modules/payment/plugnpay_ss2.php
  catalog/includes/modules/payment/plugnpay_ss2/
    PnPSs2Logger.php
  catalog/includes/languages/english/modules/payment/lang.plugnpay_ss2.php
```

## Uninstall

1. Admin → Modules → Payment → remove **PlugnPay Smart Screens v2** (drops config keys and `{prefix}plugnpay_ss2` table).
2. Admin → Plugins → Plugin Manager → uninstall **PlugnPay Smart Screens v2** (also cleans leftover `MODULE_PAYMENT_PLUGNPAY_SS2_%` keys).

## Support

Provided AS IS. See [PlugnPay docs](https://docs.plugnpay.com/) and the Zen Cart community forums for integration questions.
