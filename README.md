# Shopping Cart - ZenCart Payment Modules

Easy to install payment modules for the ZenCart shopping cart.
Multiple payment styles are supported, each covering a different checkout need.

For ZenCart v1.5.x
* Smart Screens v2 (Gateway Hosted Solution)
  - [Download](./ZenCart_v1.5.x/zencart_1.5.7_ss2_module.zip)
  
For ZenCart v1.3.x
* API (Remote Auth)
  - [Download - Credit Card](./ZenCart_v1.3.x/zencart_1.3.8_api_module.zip)
  - [Download - Auto Rec Bill](./ZenCart_v1.3.x/zencart_1.3.8_arb_module.zip)
  - [Download - Bill Member](./ZenCart_v1.3.x/zencart_1.3.8_bm_module.zip)
* Smart Screens v1 (Legacy Gateway Hosted Solution)
  - [Download - Credit Card](./ZenCart_v1.3.x/zencart_1.3.8_cc_ss_module.zip)
  - [Download - ACH/eCheck](./ZenCart_v1.3.x/zencart_1.3.8_ach_ss_module.zip)

For ZenCart v1.2.x
* API (Remote Auth)
  - [Download - Credit Card](./ZenCart_v1.2.x/zencart_1.2.4.1_api_module.zip)
* Smart Screens v1 (Legacy Gateway Hosted Solution)
  - [Download - Credit Card](./ZenCart_v1.2.x/zencart_1.2.4.1_ss_module.zip)

## Installation

For complete instructions on how to install/setup any of our ZenCart payment modules, please refer to the README file within the zip file of that module.

However the basic process is:
* download the zip file of the module you want to install
* unzip it & refer to the README file
* upload the given files to their respective places in ZenCart
* activate the module in the ZenCart Plug-Ins section
* configure the module in the ZenCart Payments section

## Usage

Here is a break down of what each payment module offers/does:

API
* This method permits ZenCart to handle the entire checkout process.
* We offer separate API based modules for Credit Card & ACH/eCheck options.
* This module directly requires ZenCart to collect all payment information.
* Customer never leaves the given site during the checkout process.
* Customer never sees our payment gateway during the checkout process.
* This module requires the given website to be properly SSL secured.

Smart Screens v2
* This is the most current version of our Smart Screens payment method.
* Supports Credit Card, ACH/eCheck &/or any other payment options configured with us.
* ZenCart will NOT collect any sensitive payment info from the customer at checkout.
* Customer will be redirected to our gateway's secure billing pages to complete their payment.
* Our payment gateway will directly collect the payment data via our secure billing pages.
* After the payment info is submitted & approved, we'll redirect the customer back to ZenCart.
* This module does NOT require the given site to be SSL secured, but its still HIGHLY recommended.

Smart Screens v2 (CardX Build)
* This is a modified version of our normal Smart Screens v2 module, but for CardX specific clients.
* If you have a CardX account, you should use this ZenCart module instead of our generic one.

Smart Screens v1 (Legacy)
* TO PREVENT ISSUES, DO NOT USE THIS MODULE, UNLESS TOLD TO BY PLUGNPAY STAFF!
* This is a legacy version of our Smart Screens payment method, for older/custom PnP accounts.
* Supports Credit Card &/or ACH/eCheck payment options configured with us.
* ZenCart will NOT collect any sensitive payment info from the customer at checkout.
* Customer will be redirected to our gateway's secure billing pages to complete their payment.
* Our payment gateway will directly collect the payment data via our secure billing pages.
* After the payment info is submitted & approved, we'll redirect the customer back to ZenCart.
* This module does NOT require the given site to be SSL secured, but its still HIGHLY recommended.
* IF POSSIBLE, USE THE SMART SCREENS V2 MODULE INSTEAD FOR BEST RESULTS.

