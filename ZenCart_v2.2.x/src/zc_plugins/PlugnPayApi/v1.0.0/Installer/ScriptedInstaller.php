<?php
/**
 * @copyright Copyright (c) PlugnPay Technologies
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Zencart\PluginSupport\ScriptedInstaller as BaseScriptedInstaller;

class ScriptedInstaller extends BaseScriptedInstaller
{
    protected function executeInstall()
    {
        return true;
    }

    protected function executeUpgrade($oldVersion)
    {
        return true;
    }

    protected function executeUninstall()
    {
        // Payment module configuration is managed via Modules > Payment.
        // Clean it up when the plugin is fully uninstalled from Plugin Manager.
        if (defined('MODULE_PAYMENT_PLUGNPAY_API_STATUS')) {
            global $db;
            $db->Execute(
                "DELETE FROM " . TABLE_CONFIGURATION .
                " WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PLUGNPAY\_API\_%'"
            );
        }

        return true;
    }
}
