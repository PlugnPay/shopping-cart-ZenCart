# Shopping Cart - ZenCart Payment Modules

Easy to install payment modules for the ZenCart shopping cart.
Multiple payment styles are supported, each covering a different checkout need.

## Downloads by Zen Cart version

### Zen Cart v2.2.x (encapsulated `zc_plugins`)

* **API (Remote Auth)** — onsite card collection
  - [Download - Credit Card](./ZenCart_v2.2.x/zencart_2.2.2_api_module.zip)
  - Source: [./ZenCart_v2.2.x/src/zc_plugins/PlugnPayApi/](./ZenCart_v2.2.x/src/zc_plugins/PlugnPayApi/)
  - Docs: [API README](./ZenCart_v2.2.x/src/zc_plugins/PlugnPayApi/v1.0.1/README.md)
* **Smart Screens v2** — gateway hosted checkout
  - [Download](./ZenCart_v2.2.x/zencart_2.2.2_ss2_module.zip)
  - Source: [./ZenCart_v2.2.x/src/zc_plugins/PlugnPaySs2/](./ZenCart_v2.2.x/src/zc_plugins/PlugnPaySs2/)
  - Docs: [Smart Screens v2 README](./ZenCart_v2.2.x/src/zc_plugins/PlugnPaySs2/v1.0.1/README.md)

Package overview: [./ZenCart_v2.2.x/README.md](./ZenCart_v2.2.x/README.md)

### Zen Cart v1.5.x (legacy copy-into-core)

* Smart Screens v2 (Gateway Hosted Solution)
  - [Download](./ZenCart_v1.5.x/zencart_1.5.7_ss2_module.zip)
  - [Download - CardX](./ZenCart_v1.5.x/zencart_1.5.7_ss_cardx_module.zip) — legacy CardX build; **not** offered for 2.2.x

### Zen Cart v1.3.x

* API (Remote Auth)
  - [Download - Credit Card](./ZenCart_v1.3.x/zencart_1.3.8_api_module.zip)
  - [Download - Auto Rec Bill](./ZenCart_v1.3.x/zencart_1.3.8_arb_module.zip)
  - [Download - Bill Member](./ZenCart_v1.3.x/zencart_1.3.8_bm_module.zip)

### Zen Cart v1.2.x

* API (Remote Auth)
  - [Download - Credit Card](./ZenCart_v1.2.x/zencart_1.2.4.1_api_module.zip)

## Installation

For complete instructions, open the README inside the zip (or the linked docs above).

### Zen Cart 2.2.x

1. Download the zip for the module you want (API or Smart Screens v2).
2. Unzip into the Zen Cart **root** so `zc_plugins/` merges in place (`…/v1.0.1/…`).
3. Admin → **Plugins** → Plugin Manager → install / enable the plugin.
4. Admin → **Modules** → **Payment** → install and configure the payment method.

### Older Zen Cart versions (1.x)

1. Download the zip for your cart version.
2. Unzip and copy files into the matching paths under the cart root (see the zip README).
3. Activate and configure under Admin → Modules → Payment.

## Usage

### API (Remote Auth)

* Zen Cart handles the entire checkout process on your site.
* Your store collects payment information (increases PCI scope).
* Customer never leaves your site and does not see the PlugnPay billing pages.
* Storefront HTTPS is required (production only; no Test/Production toggle).
* Authorization Type: `authonly` or `authpostauth`.
* Capture / void / refund are done in PlugnPay Merchant Admin (not from Zen Cart).
* For Zen Cart 2.2.x: encapsulated `zc_plugins` package **v1.0.1**.

### Smart Screens v2

* Hosted checkout at `https://pay1.plugnpay.com/pay/`.
* Supports Credit Card and other payment options configured on your PlugnPay account.
* Zen Cart does **not** collect sensitive payment data at checkout.
* Customer is redirected to PlugnPay, then returned to Zen Cart after approval or decline.
* HTTPS on your store is strongly recommended (return URL / session).
* For Zen Cart 2.2.x: encapsulated `zc_plugins` package **v1.0.1**; authorization-only hosted checkout (settle in PlugnPay Admin).
* No public demo / test publisher for the 2.2.x module — use merchant-supplied credentials.

### Smart Screens v2 (CardX Build)

* Legacy CardX-specific build for Zen Cart **1.5.x only**.
* Not offered for Zen Cart 2.2.x.
