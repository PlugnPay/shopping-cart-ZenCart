# PlugnPay Smart Screens v2 Module for Zen Cart 2.2.x

Hosted payments via PlugnPay Smart Screens v2 (`https://pay1.plugnpay.com/pay/`).

This is an **encapsulated Zen Cart plugin** (`zc_plugins`). It does not modify Zen Cart core files.

## Features

- Offsite / hosted checkout (card data collected on PlugnPay)
- Authorize-only (`authonly`) or Sale (`authpostauth`)
- Admin Capture (mark), Void, and Refund (return) from order edit
- Dedicated `plugnpay_ss2` database table for gateway communications
- Debug logging with PAN / CVV / password redaction
- PHP 8.2+ / Zen Cart 2.2.x compatible

## PCI notice

This module does **not** collect cardholder data on your server. Customers are redirected to PlugnPay’s hosted Smart Screens pages. For onsite card collection, use the separate PlugnPay Remote API module.

## Requirements

- Zen Cart **2.2.0+** (tested target: 2.2.2)
- PHP **8.2+**
- PlugnPay gateway account username (merchant-supplied)
- **Remote Client Password** (from PlugnPay Security Administration) for Capture / Void / Refund
- PHP **cURL + OpenSSL** for admin capture/void/refund only
- For Capture / Void / Refund: whitelist your store server IP in PlugnPay Security Administration

There is **no** public demo / test account mode. Use your merchant credentials.

## Installation

1. Unzip `zencart_2.2.2_ss2_module.zip` into your Zen Cart **root** so you have:

   ```
   zc_plugins/PlugnPaySs2/v1.0.0/...
   ```

2. Admin → **Plugins** → Plugin Manager → install / enable **PlugnPay Smart Screens v2**.

3. Admin → **Modules** → **Payment** → install **PlugnPay Smart Screens v2**.

4. Configure:
   - Gateway Account
   - Remote Client Password (admin ops)
   - Authorization Type (`authonly` or `authpostauth`)
   - Currency, order statuses, zone, database storage, debug logging

5. Place a test order with your merchant account (per your PlugnPay test procedures).

## Checkout flow

1. Customer selects Credit Card on the payment page (no card fields on your store).
2. On Confirm Order, Zen Cart POSTs hidden order fields to `https://pay1.plugnpay.com/pay/`.
3. Customer completes payment on PlugnPay Smart Screens.
4. PlugnPay POSTs back to your store’s `checkout_process`.
5. Module `before_process()` checks `pi_response_status` plus basic amount/account match.
6. On success, the order is created; AUTH and orderID are written to order history.
7. On decline / error, the customer is returned to payment with the gateway message.

Response-link cryptographic verification is **not** included in v1.0.0 (planned as a follow-up).

## Admin operations

On Admin → Customers → Orders → edit an order paid with this module:

| Action  | PlugnPay mode | Notes                                      |
|---------|---------------|--------------------------------------------|
| Capture | `mark`        | Shown when Authorization Type is authonly  |
| Void    | `void`        | Requires amount + orderID                  |
| Refund  | `return`      | One return per orderID; amount ≤ original  |

Enter the PlugnPay **orderID** from the order history comment (`TransID/orderID: …`).

## Logging

### File logs

Set **Debug Logging** to `Log File`. Sanitized logs are written under the Zen Cart logs directory as:

```
plugnpay_ss2_YYYYMMDD.log
```

### Database table

When **Enable Database Storage** is True, submit/response snapshots are stored in `{prefix}plugnpay_ss2`.

Never logged: full card number, CVV, or publisher-password.

## Troubleshooting

- Confirm return URL reaches `checkout_process` over HTTPS and session cookies survive the redirect.
- If admin mark/void/return fail with auth errors: confirm Remote Client Password and IP whitelist.
- Amount mismatch errors mean the return POST amount did not match the amount sent to Smart Screens.

## Manual test checklist

- [ ] Plugin installs in Plugin Manager
- [ ] Payment module installs and shows configuration keys
- [ ] Approved payment (merchant credentials) creates order
- [ ] Declined card shows gateway message and restores checkout
- [ ] Auth-only leaves order Pending; Capture updates status
- [ ] Void succeeds before settlement
- [ ] Refund (return) posts and updates history
- [ ] `plugnpay_ss2` table stores rows when Store Data is enabled
- [ ] Debug log redacts PAN/CVV/password
- [ ] Payment Zone restriction works
- [ ] Currency conversion path works when store currency ≠ gateway currency

## File map

```
zc_plugins/PlugnPaySs2/v1.0.0/
  manifest.php
  changelog.txt
  Installer/ScriptedInstaller.php
  catalog/includes/modules/payment/plugnpay_ss2.php
  catalog/includes/modules/payment/plugnpay_ss2/
    PnPSs2Api.php
    PnPSs2Logger.php
    admin_notification.php
  catalog/includes/languages/english/modules/payment/lang.plugnpay_ss2.php
```
