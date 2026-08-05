# PlugnPay Remote API Module for Zen Cart 2.2.x

**Version:** v1.0.1

Credit card payments via PlugnPay’s Remote API (`https://pay1.plugnpay.com/payment/pnpremote.cgi`).

This is an **encapsulated Zen Cart plugin** (`zc_plugins`). It does not modify Zen Cart core files.

## Features

- Onsite credit card collection (checkout stays on your store)
- Authorize-only (`authonly`) or Sale (`authpostauth`)
- Production only (HTTPS required; no Test/Production toggle)
- Debug logging with PAN / CVV / password redaction
- PHP 8.2+ / Zen Cart 2.2.x compatible
- Requires PHP cURL with SSL (no shell-exec curl path)

This module does **not** include Zen Cart admin Capture / Void / Refund. Settle or reverse transactions in [PlugnPay Merchant Admin](https://pay1.plugnpay.com/admin/). For hosted (lower PCI) checkout, use Smart Screens v2 (`PlugnPaySs2`).

## PCI notice

This module collects cardholder data on your server, which increases PCI DSS scope. For a lower-scope hosted option, use the PlugnPay Smart Screens v2 module (`PlugnPaySs2`).

## Requirements

- Zen Cart **2.2.0+** (tested target: 2.2.2)
- PHP **8.2+** with **cURL + OpenSSL**
- Storefront **HTTPS** (module disables itself without SSL)
- PlugnPay publisher-name (username)
- **Remote Client Password** (from PlugnPay Security Administration — not your admin login password)

## Installation

1. Unzip `zencart_2.2.2_api_module.zip` into your Zen Cart **root** so you have:

   ```
   zc_plugins/PlugnPayApi/v1.0.1/...
   ```

2. Admin → **Plugins** → Plugin Manager → install / enable **PlugnPay Remote API**.

3. Admin → **Modules** → **Payment** → install **PlugnPay Remote API**.

4. Configure:
   - Publisher Name
   - Remote Client Password
   - Publisher Email (optional notify address)
   - Authorization Type (`authonly` or `authpostauth`)
   - Completed order status (used for `authpostauth`), zone, CVV, debug logging

5. Place a live/test order with your merchant credentials (per your PlugnPay procedures).

There is **no** Transaction Mode toggle — the module always runs in production (HTTPS required).

### Configuration reference

| Setting | Key | Notes |
|---|---|---|
| Enable | `MODULE_PAYMENT_PLUGNPAY_API_STATUS` | `True` / `False` |
| Publisher Name | `MODULE_PAYMENT_PLUGNPAY_API_LOGIN` | PlugnPay username |
| Remote Client Password | `MODULE_PAYMENT_PLUGNPAY_API_PASSWORD` | `publisher-password` |
| Publisher Email | `MODULE_PAYMENT_PLUGNPAY_API_PUBEMAIL` | Optional notify address |
| Authorization Type | `MODULE_PAYMENT_PLUGNPAY_API_AUTHTYPE` | `authonly` or `authpostauth` |
| Prevent Gateway Customer Email | `MODULE_PAYMENT_PLUGNPAY_API_EMAILCUST` | `yes` / `no` |
| Request CVV | `MODULE_PAYMENT_PLUGNPAY_API_USE_CVV` | `True` / `False` |
| Sort order | `MODULE_PAYMENT_PLUGNPAY_API_SORT_ORDER` | Display order |
| Payment Zone | `MODULE_PAYMENT_PLUGNPAY_API_ZONE` | Optional geo-zone |
| Set Completed Order Status | `MODULE_PAYMENT_PLUGNPAY_API_ORDER_STATUS_ID` | Used for `authpostauth`; `authonly` forces Pending (`1`) |
| Debug Logging | `MODULE_PAYMENT_PLUGNPAY_API_DEBUGGING` | `Off` or `Log File` |

**Authorization Type mapping**

- `authonly` → authorize only; order forced to Pending until settled in PlugnPay Admin
- `authpostauth` → authorize and settle (sale); uses Completed Order Status

## Checkout flow

1. Customer enters card details on payment page.
2. On Confirm Order, Zen Cart calls `before_process()`.
3. Module POSTs to `pnpremote.cgi` with hyphenated Remote API fields and configured `authtype`.
4. On `FinalStatus=success`, order is created; `orderID` and auth code are written to order history.
5. On decline / error, customer is returned to payment with the gateway message.

## Logging

Set **Debug Logging** to `Log File`. Sanitized logs are written under the Zen Cart logs directory as:

```
plugnpay_api_YYYYMMDD.log
```

Never logged: full card number, CVV, or publisher-password.

## Troubleshooting

Test connectivity from the server:

```bash
curl -d "publisher-name=YOUR_ACCOUNT&publisher-password=YOUR_REMOTE_PASSWORD&mode=auth&authtype=authonly&card-name=cardtest&card-number=4111111111111111&card-exp=01/30&card-cvv=123&card-amount=1.23" https://pay1.plugnpay.com/payment/pnpremote.cgi
```

You should receive a URL-encoded response containing `FinalStatus=…`.

If the response is blank: firewall / outbound HTTPS / DNS issue.

If the module does not appear at checkout: confirm storefront HTTPS is enabled.

## Manual test checklist

- [ ] Plugin installs in Plugin Manager
- [ ] Payment module installs and shows configuration keys (no Test Mode / Capture-Void-Refund UI)
- [ ] Approved authonly creates a **Pending** order with AUTH + orderID history
- [ ] Approved authpostauth uses Completed Order Status
- [ ] Declined card shows gateway message and restores checkout
- [ ] Debug log redacts PAN/CVV/password
- [ ] Module disabled without HTTPS
- [ ] Payment Zone restriction works
- [ ] Order edit screen has **no** Capture / Void / Refund tools for this module

## File map

```
zc_plugins/PlugnPayApi/v1.0.1/
  manifest.php
  changelog.txt
  README.md
  Installer/ScriptedInstaller.php
  catalog/includes/modules/payment/plugnpay_api.php
  catalog/includes/modules/payment/plugnpay_api/
    PnPApi.php
    PnPLogger.php
  catalog/includes/languages/english/modules/payment/lang.plugnpay_api.php
```

## Uninstall

1. Admin → Modules → Payment → remove **PlugnPay Remote API**.
2. Admin → Plugins → Plugin Manager → uninstall **PlugnPay Remote API**.

## Support

Provided AS IS. See [PlugnPay docs](https://docs.plugnpay.com/) and the Zen Cart community forums for integration questions.

For hosted (lower PCI) checkout on Zen Cart 2.2.x, see the [Smart Screens v2 module](../PlugnPaySs2/v1.0.1/README.md).
