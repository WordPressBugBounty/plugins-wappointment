<?php

namespace Wappointment\Addons;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Helpers\Get;
use Wappointment\Services\CurrentUser;
use Wappointment\Services\Wappointment\Addons;
use Wappointment\ClassConnect\Carbon;
// @codingStandardsIgnoreFile
abstract class AbstractBoot implements \Wappointment\Addons\Boot
{
    public static $addon_key;
    public static $addon_name;
    public static $name_space;
    public static $addon_version;
    public static $addon_settings;
    public static $addon_settings_icon;
    public static $has_settings;
    public static $has_back_setup;
    public static $has_front_script;
    public static $has_js_var;
    public static $has_installation;
    public static $setting_key;
    public static $instructions;
    public static $addon_db_version_required;
    public static function getAddonSlug()
    {
        return \str_replace('_', '-', static::$addon_key);
    }
    public static function init()
    {
        add_filter('wappointment_addon_wrapper_' . static::$addon_key, [static::$name_space . 'Boot', 'addonStatusWrapper']);
        if (!\Wappointment\System\Status::isInstalled() || !static::canRun()) {
            return;
        }
        //only triggerred once the plugin is ready to be used
        static::installedFilters();
        static::checkIfUpdateDbRequired();
    }
    private static function checkIfUpdateDbRequired()
    {
        if (!empty(static::$addon_db_version_required) && static::isValid() && static::isInstalled() && CurrentUser::isAdmin()) {
            static::requiresUpdateCheck();
        }
    }
    public static function addToListOfDbUpdates($addons_require_db_update)
    {
        if (\method_exists(static::$name_space . 'Boot', 'runDbMigrate')) {
            $addons_require_db_update[] = ['key' => static::$addon_key, 'namespace' => static::$name_space . 'Boot'];
        }
        return $addons_require_db_update;
    }
    public static function isInstalledOrdoesntRequireInstallation()
    {
        return !static::$has_installation || static::isInstalled();
    }
    public static function addonStatusWrapper($package)
    {
        if (static::isInstalledOrdoesntRequireInstallation()) {
            $package->initial_wizard = static::isSetup();
            if (\is_array(static::$instructions) && \count(static::$instructions) > 0) {
                $package->instructions = static::$instructions;
            }
            if (static::$setting_key !== \false) {
                $package->settingKey = static::$setting_key;
            }
        } else {
            if (static::$has_installation) {
                $package->initial_install = \true;
            }
        }
        return $package;
    }
    public static function requiresDbUpdate()
    {
        if (static::$addon_db_version_required) {
            return static::addonDbVersion() === \false ? \true : \version_compare(static::addonDbVersion(), static::$addon_db_version_required) < 0;
        }
        return \false;
    }
    public static function getAddonsVersions($reset = \false)
    {
        static $addons_versions = \false;
        if ($addons_versions === \false) {
            $addons_versions = WPHelpers::getOption('addons_db_version', []);
        }
        if ($reset !== \false) {
            $addons_versions = $reset;
        }
        return $addons_versions;
    }
    public static function addonDbVersion()
    {
        $addons_versions = static::getAddonsVersions();
        return !empty($addons_versions[static::$addon_key]) ? $addons_versions[static::$addon_key] : \false;
    }
    public static function setAddonDbVersion()
    {
        $addons_versions = static::getAddonsVersions();
        $addons_versions[static::$addon_key] = static::$addon_db_version_required;
        static::getAddonsVersions($addons_versions);
        return WPHelpers::setOption('addons_db_version', $addons_versions);
    }
    public static function canRun()
    {
        return \true;
    }
    public static function isInstalled()
    {
        return \call_user_func(static::$name_space . 'Services\\Settings::get', 'installed_at');
    }
    public static function backSetup()
    {
        wp_enqueue_script(static::$addon_key . '_back_setup', plugins_url(static::getAddonSlug() . '/dist/back-setup.js'), [], static::$addon_version, \true);
    }
    public static function adminInit()
    {
        if (\Wappointment\WP\Helpers::isPluginPage()) {
            if (static::$has_back_setup) {
                static::backSetup();
            }
            if (static::isInstalledOrdoesntRequireInstallation()) {
                wp_enqueue_script(static::$addon_key . '_back', plugins_url(static::getAddonSlug() . '/dist/back.js'), [], static::$addon_version, \true);
                if (static::$has_front_script) {
                    self::frontEnqueue();
                }
            }
        }
    }
    public static function frontEnqueue()
    {
        wp_enqueue_script(static::$addon_key . '_front', plugins_url(static::getAddonSlug() . '/dist/front.js'), [], static::$addon_version, \true);
    }
    public static function getMainSettings($data)
    {
        return $data;
    }
    public static function requiresUpdateCheck()
    {
        if (CurrentUser::isAdmin() && static::requiresDbUpdate()) {
            add_filter('wappointment_addons_requires_update', [static::$name_space . 'Boot', 'addToListOfDbUpdates']);
        }
    }
    /**
     * todo remove
     *
     * @return void
     */
    public static function hooksAndFiltersWhenInstalled()
    {
        add_filter('wappointment_viewdata_' . static::$addon_settings, [static::$name_space . 'Boot', 'getMainSettings']);
        if (static::$has_front_script) {
            add_action('wappointment_enqueue_front_' . static::$addon_key, [static::$name_space . 'Boot', 'frontEnqueue']);
        }
    }
    public static function hooksAndFiltersAlways()
    {
        // needed when not installed yet
        add_filter('wappointment_active_addons', [static::$name_space . 'Boot', 'registerAddon']);
        if (static::$has_js_var) {
            add_filter('wappointment_js_vars', [static::$name_space . 'Boot', 'jsVariables']);
        }
        add_action('admin_init', [static::$name_space . 'Boot', 'adminInit'], 11);
        add_action('in_plugin_update_message-' . static::pluginNamekey() . '/index.php', [static::$name_space . 'Boot', 'versionUpdateWarning'], 10, 2);
    }
    protected static function pluginNamekey()
    {
        return \str_replace('_', '-', static::$addon_key);
    }
    public static function licenceExpired()
    {
        $licence = static::getLicenceDetail();
        $expires_at_carbon = new Carbon($licence->expires_at);
        return $expires_at_carbon->timestamp < \time();
    }
    public static function versionUpdateWarning($plugin)
    {
        if (static::licenceExpired() && !static::hasAccessToNewVersion($plugin['new_version'])) {
            ?>
            <hr class="w-major-update-warning__separator" />
            <div class="w-major-update-warning">
                <div class="error-message error inline notice-error notice-alt">
                    <div>
                        <?php 
            echo \sprintf(__('Your licence expired, you must renew in order to update to version %s', 'wappointment'), $plugin['new_version']);
            ?>
                        <a href="<?php 
            echo WAPPOINTMENT_SITE . "/renew/site_" . WPHelpers::getOption('site_key');
            ?>" target="_blank"><?php 
            echo __('Renew now', 'wappointment');
            ?></a>
                    </div>
                </div>
            </div>
<?php 
        }
    }
    protected static function hasAccessToNewVersion($new_version)
    {
        return \version_compare($new_version, static::getMaxVersion()) < 0;
    }
    protected static function getMaxVersion()
    {
        return static::getLicenceDetail()->latest_version->version;
    }
    protected static function getLicenceDetail()
    {
        static $siteDetails = \false;
        if ($siteDetails === \false) {
            $siteDetails = Addons::licensedSolutions();
        }
        foreach ($siteDetails as $solution) {
            if ($solution->namekey == static::pluginNamekey()) {
                return $solution;
            }
        }
    }
    public static function registerAddon($addons)
    {
        $addons[static::$addon_key] = ['name' => static::$addon_name, 'icon' => static::$addon_settings_icon, 'settings' => (bool) static::$has_settings];
        $wappo_compatible_with_addon_version = static::compatibleWithAddon();
        if (is_admin() && \version_compare(static::$addon_version, $wappo_compatible_with_addon_version, '<')) {
            $addons[static::$addon_key]['requires_update'] = $wappo_compatible_with_addon_version;
        }
        return $addons;
    }
    protected static function compatibleWithAddon()
    {
        return Get::list('addons')[static::$addon_key]['min'];
    }
    protected static function convertVersionToMajor($version)
    {
        $vexplode = \explode('.', $version);
        \array_pop($vexplode);
        return \implode('.', $vexplode);
    }
    public static function jsVariables($var)
    {
        $variables = ['installed_at' => static::isInstalled()];
        return $var .= 'var ' . static::$addon_key . ' = ' . wp_json_encode($variables) . ";\n";
    }
    public static function isSetup()
    {
        return \false;
    }
}
