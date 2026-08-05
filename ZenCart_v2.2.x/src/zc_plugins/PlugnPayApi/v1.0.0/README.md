# PlugnPay Remote API Module for Zen Cart 2.2.x

Credit card payments via PlugnPay’s Remote API (`https://pay1.plugnpay.com/payment/pnpremote.cgi`).

This is an **encapsulated Zen Cart plugin** (`zc_plugins`). It does not modify Zen Cart core files.

## Features

- Onsite credit card collection (checkout stays on your store)
- Authorize-only (`authonly`) or Sale (`authpostauth`)
- Admin Capture (mark), Void, and Refund (return) from order edit
- Debug logging with PAN / CVV / password redaction
- PHP 8.2+ / Zen Cart 2.2.x compatible
- Requires PHP cURL with SSL (no shell-exec curl path)

## PCI notice

This module collects cardholder data on your server, which increases PCI DSS scope. For a lower-scope hosted option, use the PlugnPay Smart Screens v2 module (`PlugnPaySs2`).

## Requirements

- Zen Cart **2.2.0+** (tested target: 2.2.2)
- PHP **8.2+** with **cURL + OpenSSL**
- Storefront **HTTPS** for Production mode
- PlugnPay publisher-name (username)
- **Remote Client Password** (from PlugnPay Security Administration — not your admin login password)
- For Capture / Void / Refund: whitelist your store server IP in PlugnPay Security Administration

## Installation

1. Unzip `zencart_2.2.2_api_module.zip` into your Zen Cart **root** so you have:

   ```
   zc_plugins/PlugnPayApi/v1.0.0/...
   ```

2. Admin → **Plugins** → Plugin Manager → install / enable **PlugnPay Remote API**.

3. Admin → **Modules** → **Payment** → install **PlugnPay Remote API**.

4. Configure:
   - Publisher Name
   - Remote Client Password
   - Publisher Email (optional notify address)
   - Test / Production
   - Authorization Type (`authonly` or `authpostauth`)
   - Order statuses, zone, CVV, debug logging

5. Place a test order (Test mode / `pnpdemo` as appropriate).

## Checkout flow

1. Customer enters card details on payment page.
2. On Confirm Order, Zen Cart calls `before_process()`.
3. Module POSTs to `pnpremote.cgi` with hyphenated Remote API fields.
4. On `FinalStatus=success`, order is created; `orderID` and auth code are written to order history.
5. On decline / error, customer is returned to payment with the gateway message.

## Admin operations

On Admin → Customers → Orders → edit an order paid with this module:

| Action  | PlugnPay mode | Notes                                      |
|---------|---------------|--------------------------------------------|
| Capture | `mark`        | Shown when Authorization Type is authonly  |
| Void    | `void`        | Requires amount + orderID                  |
| Refund  | `return`      | One return per orderID; amount ≤ original  |

Enter the PlugnPay **orderID** from the order history comment (`TransID/orderID: …`).

## Logging

Set **Debug Logging** to `Log File`. Sanitized logs are written under the Zen Cart logs directory as:

```
plugnpay_api_YYYYMMDD.log
```

Never logged: full card number, CVV, or publisher-password.

## Troubleshooting

Test connectivity from the server:

```bash
curl -d "publisher-name=pnpdemo&publisher-email=trash%40plugnpay.com&mode=auth&card-name=cardtest&card-number=4111111111111111&card-exp=01/30&card-cvv=123&card-amount=1.23" https://pay1.plugnpay.com/payment/pnpremote.cgi
```

You should receive a URL-encoded response containing `FinalStatus=…`.

If the response is blank: firewall / outbound HTTPS / DNS issue.

If admin mark/void/return fail with auth errors: confirm Remote Client Password and IP whitelist.

## Manual test checklist

- [ ] Plugin installs in Plugin Manager
- [ ] Payment module installs and shows configuration keys
- [ ] Approved auth (Test / pnpdemo)
- [ ] Declined card shows gateway message and restores checkout
- [ ] Auth-only leaves order Pending; Capture updates status
- [ ] Void succeeds before settlement
- [ ] Refund (return) posts and updates history
- [ ] Debug log redacts PAN/CVV/password
- [ ] Module disabled without HTTPS in Production
- [ ] Payment Zone restriction works

## File map

```
zc_plugins/PlugnPayApi/v1.0.0/
  manifest.php
  changelog.txt
  Installer/ScriptedInstaller.php
  catalog/includes/modules/payment/plugnpay_api.php
  catalog/includes/modules/payment/plugnpay_api/
    PnPApi.php
    PnPLogger.php
    admin_notification.php
  catalog/includes/languages/english/modules/payment/lang.plugnpay_api.php
```

## Support

Provided AS IS. See [PlugnPay docs](https://docs.plugnpay.com/) and the Zen Cart community forums for integration questions.
