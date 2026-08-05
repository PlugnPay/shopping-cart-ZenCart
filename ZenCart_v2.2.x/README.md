# Zen Cart 2.2.x — PlugnPay Payment Modules

Encapsulated `zc_plugins` packages for Zen Cart 2.2.x.

## Modules

### Remote API (onsite)

- Download: [zencart_2.2.2_api_module.zip](./zencart_2.2.2_api_module.zip)
- Source: [src/zc_plugins/PlugnPayApi/v1.0.0/](./src/zc_plugins/PlugnPayApi/v1.0.0/)
- Docs: [src/zc_plugins/PlugnPayApi/v1.0.0/README.md](./src/zc_plugins/PlugnPayApi/v1.0.0/README.md)
- Install notes: [INSTALL.txt](./INSTALL.txt)

Collects card data on your storefront and posts to `pnpremote.cgi`.

### Smart Screens v2 (hosted)

- Download: [zencart_2.2.2_ss2_module.zip](./zencart_2.2.2_ss2_module.zip)
- Source: [src/zc_plugins/PlugnPaySs2/v1.0.0/](./src/zc_plugins/PlugnPaySs2/v1.0.0/)
- Docs: [src/zc_plugins/PlugnPaySs2/v1.0.0/README.md](./src/zc_plugins/PlugnPaySs2/v1.0.0/README.md)
- Install notes: [INSTALL_SS2.txt](./INSTALL_SS2.txt)

Redirects customers to `https://pay1.plugnpay.com/pay/`. Card data is collected on PlugnPay.

Both modules support Admin Capture / Void / Refund via Remote API when Remote Client Password and IP whitelist are configured.
