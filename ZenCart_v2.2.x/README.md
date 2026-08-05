# Zen Cart 2.2.x — PlugnPay Payment Modules

Encapsulated `zc_plugins` packages for Zen Cart **2.2.0+** (target **2.2.2**). Current plugin version: **v1.0.1**. Both modules install through Admin → Plugins (Plugin Manager), then Admin → Modules → Payment.

## Choose a module

| | Remote API | Smart Screens v2 |
|---|---|---|
| Package | `PlugnPayApi` | `PlugnPaySs2` |
| Download | [zencart_2.2.2_api_module.zip](./zencart_2.2.2_api_module.zip) | [zencart_2.2.2_ss2_module.zip](./zencart_2.2.2_ss2_module.zip) |
| Checkout | Onsite card fields → `pnpremote.cgi` | Redirect → `https://pay1.plugnpay.com/pay/` |
| Card data on your server | Yes | No |
| PCI scope | Higher | Lower |
| Transaction mode | Auth-only or sale (`authonly` / `authpostauth`) | Authorization-only |
| Admin Capture / Void / Refund | No (use PlugnPay Admin) | No (use PlugnPay Admin) |
| Public demo account | No — merchant credentials only | No — merchant credentials only |

You may install both plugins; enable only the payment method(s) you need under Modules → Payment.

## Remote API (onsite)

- Source: [src/zc_plugins/PlugnPayApi/v1.0.1/](./src/zc_plugins/PlugnPayApi/v1.0.1/)
- Full docs: [src/zc_plugins/PlugnPayApi/v1.0.1/README.md](./src/zc_plugins/PlugnPayApi/v1.0.1/README.md)
- Quick install: [INSTALL.txt](./INSTALL.txt)

Collects card data on your storefront and posts from the server to PlugnPay Remote API (`authonly` or `authpostauth`). Capture / void / refund are done in PlugnPay Merchant Admin.

## Smart Screens v2 (hosted)

- Source: [src/zc_plugins/PlugnPaySs2/v1.0.1/](./src/zc_plugins/PlugnPaySs2/v1.0.1/)
- Full docs: [src/zc_plugins/PlugnPaySs2/v1.0.1/README.md](./src/zc_plugins/PlugnPaySs2/v1.0.1/README.md)
- Quick install: [INSTALL_SS2.txt](./INSTALL_SS2.txt)

Redirects customers to PlugnPay hosted Smart Screens. Return POST completes the Zen Cart order as **Pending** (authorization-only). Capture / void / refund are done in PlugnPay Merchant Admin, not from Zen Cart.

## Common install steps (both)

1. Unzip the module zip into the Zen Cart **root** so `zc_plugins/…` merges in place.
2. Admin → **Plugins** → Plugin Manager → install / enable the plugin.
3. Admin → **Modules** → **Payment** → install the payment method and enter credentials.

## Development layout

```
ZenCart_v2.2.x/
  INSTALL.txt                 # API quick install
  INSTALL_SS2.txt             # Smart Screens v2 quick install
  zencart_2.2.2_api_module.zip
  zencart_2.2.2_ss2_module.zip
  src/zc_plugins/
    PlugnPayApi/v1.0.1/
    PlugnPaySs2/v1.0.1/
```
