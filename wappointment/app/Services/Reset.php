<?php

namespace Wappointment\Services;

use Wappointment\Config\Database;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\WP\Scheduler as WPScheduler;
use Wappointment\Services\Wappointment\DotCom;
use Wappointment\Repositories\Availability;
use Wappointment\Repositories\CalendarsBack;
use Wappointment\Repositories\Services;
// @codingStandardsIgnoreFile
class Reset
{
    private $options = ['flags', 'wizard_step', 'subscribed_status', 'widget_settings', 'site_details', 'site_key', 'installation_completed', 'installation_step', 'db_version_created', 'db_version', 'appointments_must_refresh', 'appointments_update', 'staff_settings', 'addons_db_version', 'group_settings', 'packages_settings', 'stripe_settings', 'paypal_settings', 'woocommerce_settings'];
    private $user_options = ['availability', 'calendar_logs', 'cal_urls', 'viewed_updates', 'hello_page', 'preferences', 'since_last_refresh'];
    private $db_drop = ['appointments_clients_packages', 'clients_packages', 'appointments_participants', 'appointments_clients_participants', 'packages_services', 'packages', 'calendar_service', 'appointments', 'calendars', 'custom_fields', 'clients', 'failed_jobs', 'jobs', 'locations', 'logs', 'migrations', 'order_price', 'orders', 'prices', 'reminders', 'services', 'service_location', 'statuses'];
    public function proceed()
    {
        static::eraseCache();
        $this->dotComInforms();
        $this->dropTables();
        $this->removeStaffSettings();
        $this->removeCoreSettings();
        WPScheduler::clearScheduler();
    }
    private function dotComInforms()
    {
        $dotcomapi = new DotCom();
        $dotcomapi->setStaff();
    }
    private function removeStaffSettings()
    {
        foreach (\Wappointment\Services\Staff::getIds() as $staff_id) {
            \Wappointment\Services\Settings::deleteStaff($staff_id);
            foreach ($this->user_options as $option_key) {
                WPHelpers::deleteStaffOption($option_key, $staff_id);
            }
        }
    }
    public function dropTables()
    {
        //Capsule::schema()->disableForeignKeyConstraints();
        $db_list = [];
        foreach ($this->db_drop as $table_name) {
            $db_list[] = Database::$prefix_self . '_' . $table_name;
        }
        global $wpdb;
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
        foreach (apply_filters('wappointment_db_drop', $db_list) as $table_name) {
            $full_table = Database::getWpSitePrefix() . $table_name;
            $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %s;", $full_table));
        }
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
        //Capsule::schema()->enableForeignKeyConstraints();
    }
    private function removeCoreSettings()
    {
        foreach ($this->options as $option_key) {
            WPHelpers::deleteOption($option_key);
        }
        \Wappointment\Services\Settings::delete();
    }
    public static function refreshCache()
    {
        (new CalendarsBack())->refresh();
        (new Services())->refresh();
        (new Availability())->refresh();
    }
    public static function eraseCache()
    {
        (new CalendarsBack())->clear();
        (new Services())->clear();
        (new Availability())->clear();
    }
}
