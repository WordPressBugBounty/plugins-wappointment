<?php

namespace Wappointment\Services;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Carbon;
use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Helpers\Translations;
// @codingStandardsIgnoreFile
class Settings
{
    public static $key_option = 'settings';
    public static $settings = \false;
    public static $staffSettings = [];
    public static $msg = '';
    public static $valid = \true;
    protected static function updateLocalSettings($values)
    {
        static::$settings = $values;
    }
    protected static function updateLocalStaffSettings($staff_id, $values)
    {
        static::$staffSettings[$staff_id] = $values;
    }
    protected static function getValues($reset = \false)
    {
        if (static::$settings === \false) {
            static::$settings = WPHelpers::getOption(static::$key_option);
        }
        return static::$settings;
    }
    protected static function getStaffValues($staff_id = \false, $reset = \false)
    {
        if ($staff_id === \false) {
            $staff_id = \Wappointment\Services\Settings::get('activeStaffId');
        }
        if (!isset(static::$staffSettings[$staff_id])) {
            static::$staffSettings[$staff_id] = WPHelpers::getStaffOption(static::$key_option, $staff_id, self::staffDefaults());
        }
        return static::$staffSettings[$staff_id];
    }
    public static function allDefaults()
    {
        return [
            'service' => ['name' => '', 'duration' => 60, 'type' => '', 'address' => '', 'options' => ['countries' => []]],
            'email_logo' => \false,
            'wappointment_allowed' => \false,
            'buffer_time' => 0,
            'scheduler_mode' => 0,
            'front_page' => 0,
            'approval_mode' => 1,
            // 1 stands for automatic
            'week_starts_on' => 1,
            // 1 stands for monday
            'date_format' => WPHelpers::getWPOption('date_format'),
            'time_format' => WPHelpers::getWPOption('time_format'),
            'date_time_union' => ' - ',
            'allow_cancellation' => \true,
            'allow_rescheduling' => \true,
            'email_footer' => '',
            'email_link_color' => '#6664cb',
            'hours_before_booking_allowed' => 3,
            'hours_before_cancellation_allowed' => 24,
            'hours_before_rescheduling_allowed' => 24,
            'activeStaffId' => \false,
            'weekly_summary' => \false,
            'weekly_summary_day' => 1,
            'weekly_summary_time' => 10,
            'daily_summary' => \false,
            'daily_summary_time' => 10,
            'notify_new_appointments' => \true,
            'notify_pending_appointments' => \true,
            'notify_canceled_appointments' => \true,
            'notify_rescheduled_appointments' => \true,
            'email_notifications' => '',
            'mail_status' => \true,
            'mail_config' => ['method' => 'wpmail', 'from_address' => self::getFromEmail(), 'from_name' => self::getFromName(), 'mgdomain' => '', 'mgkey' => '', 'host' => '', 'port' => '', 'username' => '', 'password' => '', 'wpmail_html' => \false, 'attachments_off' => \false],
            'reschedule_link' => __('Reschedule', 'wappointment'),
            'cancellation_link' => __('Cancel', 'wappointment'),
            'save_appointment_text_link' => __('Save to calendar', 'wappointment'),
            'new_booking_link' => __('Book a new appointment', 'wappointment'),
            'booking_page' => 0,
            'show_welcome' => \false,
            'force_ugly_permalinks' => \false,
            'allow_staff_cf' => \false,
            'currency' => 'USD',
            'services_sold' => \false,
            'calendar_roles' => ['administrator', 'author', 'editor', 'contributor', 'wappointment_staff'],
            'max_active_bookings' => 0,
            'max_active_per_staff' => \false,
            'autofill' => \true,
            'onsite_enabled' => \true,
            'cache' => \false,
            'tax' => 0,
            'payments_order' => [],
            'alt_port' => \false,
            'video_link_shows' => 0,
            'forceemail' => \false,
            'allow_refreshavb' => \false,
            'refreshavb_at' => 23,
            'clean_pending_every' => 25,
            'clean_last_check' => \false,
            'regavDefault' => ['monday' => [[480, 720], [840, 1140]], 'tuesday' => [[480, 720], [840, 1140]], 'wednesday' => [[480, 720], [840, 1140]], 'thursday' => [[480, 720], [840, 1140]], 'friday' => [[480, 720], [840, 1140]], 'saturday' => [], 'sunday' => [], 'precise' => \true],
            'servicesDefault' => \true,
            'calendar_handles_free' => \false,
            'calendar_ignores_free' => \false,
            'invoice' => \false,
            'invoice_seller' => '',
            'invoice_num' => __('Order nº', 'wappointment'),
            'invoice_client' => ['name'],
            'wp_remote' => \false,
            'jitsi_url' => '',
            'frontend_weekstart' => \false,
            'availability_fluid' => \false,
            'more_st' => \false,
            'starting_each' => 30,
            'collate' => '',
            'charset' => '',
            'big_prices' => \false,
        ];
    }
    public static function getFromName()
    {
        return \ucfirst(WPHelpers::currentUserName());
    }
    public static function getFromEmail()
    {
        return 'contact@' . self::getHost();
    }
    public static function getHost()
    {
        $parsed_url = wp_parse_url(WPHelpers::getWPOption('home'));
        return !empty($parsed_url['host']) ? static::cleanHost($parsed_url['host']) : 'example.com';
    }
    protected static function cleanHost($host)
    {
        return \strpos($host, 'www.') !== -1 ? \str_replace('www.', '', $host) : $host;
    }
    public static function staffDefaults()
    {
        $timezone = WPHelpers::getWPOption('timezone_string');
        return ['regav' => static::get('regavDefault'), 'availaible_booking_days' => 60, 'calurl' => '', 'timezone' => $timezone, 'avatarId' => \false, 'viewed_updates' => \false, 'email_logo' => \false, 'per_page' => 10, 'display_name' => '', 'dotcom' => \false];
    }
    public static function defaultGet($key)
    {
        $default_settings = static::allDefaults();
        return isset($default_settings[$key]) ? $default_settings[$key] : \false;
    }
    public static function defaultStaff($key)
    {
        $default_settings = static::staffDefaults();
        return isset($default_settings[$key]) ? $default_settings[$key] : \false;
    }
    public static function get($setting_key, $default = null)
    {
        $values = static::getValues();
        if (isset($values[$setting_key])) {
            $method = $setting_key . 'GetTransform';
            return \method_exists(static::class, $method) ? static::$method($values[$setting_key]) : $values[$setting_key];
        } else {
            return $default !== null ? $default : static::defaultGet($setting_key);
        }
    }
    protected static function prepareSave($setting_key, $value)
    {
        if (!static::$valid || static::valid($setting_key, $value)) {
            $updatedValues = static::getValues();
            if ($setting_key == 'service') {
                unset($value['id']);
            }
            $updatedValues[$setting_key] = static::format($setting_key, $value);
            //before save
            $method = $setting_key . 'BeforeSave';
            if (\method_exists(static::class, $method)) {
                static::$method($value);
            }
            $methodTransform = $setting_key . 'TransformValue';
            if (\method_exists(static::class, $methodTransform)) {
                $updatedValues[$setting_key] = static::$methodTransform($value);
            }
            static::updateLocalSettings($updatedValues);
            $method = $setting_key . 'Saved';
            if (\method_exists(static::class, $method)) {
                static::$method($setting_key, $value);
            }
            return $updatedValues;
        }
        return \false;
    }
    public static function save($setting_key, $value)
    {
        $updatedValues = static::prepareSave($setting_key, $value);
        if ($updatedValues !== \false) {
            WPHelpers::setOption(static::$key_option, $updatedValues, \true);
            return ['message' => Translations::get('element_saved')];
        }
    }
    public static function saveMultiple($settings)
    {
        foreach ($settings as $key => $value) {
            $updatedValues = static::prepareSave($key, $value);
        }
        WPHelpers::setOption(static::$key_option, $updatedValues, \true);
        return ['message' => Translations::get('element_saved')];
    }
    public static function delete()
    {
        static::$settings = \false;
        return WPHelpers::deleteOption(static::$key_option);
    }
    public static function getStaff($setting_key, $staff_id = \false)
    {
        $settingsStaff = static::getStaffValues($staff_id);
        $value = static::defaultStaff($setting_key);
        if (isset($settingsStaff[$setting_key])) {
            $value = $settingsStaff[$setting_key];
        }
        $preparedValue = static::prepare($setting_key, $value);
        if ($preparedValue === \false) {
            $preparedValue = $value;
        }
        return $preparedValue;
    }
    protected static function prepare($setting_key, $value)
    {
        $method = $setting_key . 'Prepare';
        return \method_exists(\get_called_class(), $method) ? static::$method($value) : \false;
    }
    public static function hasStaff($setting_key, $staff_id = \false)
    {
        return static::getStaff($setting_key, $staff_id) ? \true : \false;
    }
    public static function saveStaff($setting_key, $value, $staff_id = \false)
    {
        if ($staff_id === \false) {
            $staff_id = \Wappointment\Services\Settings::get('activeStaffId');
        }
        if (static::valid($setting_key, $value, $staff_id)) {
            $values = static::getStaffValues($staff_id);
            $values[$setting_key] = $value;
            WPHelpers::setStaffOption(static::$key_option, $values, $staff_id);
            static::updateLocalStaffSettings($staff_id, $values);
            $methodAfterSaved = $setting_key . 'Saved';
            if (\method_exists(static::class, $methodAfterSaved)) {
                static::$methodAfterSaved($staff_id);
            }
            return ['message' => empty(static::$msg) ? Translations::get('element_saved') : static::$msg];
        }
    }
    public static function deleteStaff($staff_id = \false)
    {
        if ($staff_id === \false) {
            $staff_id = \Wappointment\Services\Settings::get('activeStaffId');
        }
        static::$staffSettings = [];
        return WPHelpers::deleteStaffOption(static::$key_option, $staff_id);
    }
    protected static function valid($setting_key, $value, $staff_id = \false)
    {
        $method = $setting_key . 'Valid';
        if (\method_exists(static::class, $method)) {
            return static::$method($value, $staff_id);
        }
        if (!isset(static::allDefaults()[$setting_key]) && !isset(static::staffDefaults()[$setting_key])) {
            return \false;
        }
        return \true;
    }
    protected static function format($setting_key, $value)
    {
        $method = $setting_key . 'Format';
        if (\method_exists(static::class, $method)) {
            return static::$method($value);
        }
        return $value;
    }
    protected static function taxValid($value)
    {
        if (self::between($value, 0, 100)) {
            return \true;
        }
        throw new \WappointmentException('Tax is not valid');
    }
    protected static function jitsi_urlValid($value)
    {
        $validated_url = \filter_var($value, \FILTER_VALIDATE_URL);
        if (empty($validated_url)) {
            throw new \WappointmentException('URL is not valid');
        }
        if (\substr($validated_url, -1) != '/') {
            $validated_url .= '/';
        }
        return $validated_url;
    }
    protected static function jitsi_urlFormat($value)
    {
        return static::jitsi_urlValid($value);
    }
    public static function save_appointment_text_linkValid($value)
    {
        return static::isEmpty($value);
    }
    public static function reschedule_linkValid($value)
    {
        return static::isEmpty($value);
    }
    public static function cancellation_linkValid($value)
    {
        return static::isEmpty($value);
    }
    public static function isEmpty($value)
    {
        if (!empty($value)) {
            return \true;
        }
        throw new \WappointmentException('Setting cannot be empty');
    }
    protected static function weekly_summary_dayValid($value)
    {
        if (self::between($value, 0, 6)) {
            return \true;
        }
        throw new \WappointmentException('Week day is not valid');
    }
    protected static function weekly_summary_timeValid($value)
    {
        if (self::between($value, 0, 23)) {
            return \true;
        }
        throw new \WappointmentException('Time is not valid');
    }
    protected static function weekly_summarySaved($key, $value)
    {
        if ($value) {
            \Wappointment\Services\Queue::queueWeeklyJob();
        } else {
            \Wappointment\Services\Queue::cancelWeeklyJob();
        }
    }
    protected static function weekly_summary_timeSaved($key, $value)
    {
        self::weekly_summarySaved($key, \true);
    }
    protected static function weekly_summary_daySaved($key, $value)
    {
        self::weekly_summarySaved($key, \true);
    }
    protected static function daily_summary_timeValid($value)
    {
        if (self::between($value, 0, 23)) {
            return \true;
        }
        throw new \WappointmentException('Time is not valid');
    }
    protected static function cacheSaved($key, $value)
    {
        if ($value) {
            \Wappointment\Services\Reset::refreshCache();
        } else {
            \Wappointment\Services\Reset::eraseCache();
        }
    }
    protected static function daily_summarySaved($key, $value)
    {
        if ($value) {
            \Wappointment\Services\Queue::queueDailyJob();
        } else {
            \Wappointment\Services\Queue::cancelDailyJob();
        }
    }
    protected static function allow_refreshavbSaved($key, $value)
    {
        if ($value) {
            \Wappointment\Services\Queue::queueRefreshAVBJob();
        } else {
            \Wappointment\Services\Queue::cancelRefreshAVBJob();
        }
        \Wappointment\Services\Regenerate::all();
    }
    protected static function refreshavb_atSaved($key, $value)
    {
        \Wappointment\Services\Regenerate::all();
        \Wappointment\Services\Queue::queueRefreshAVBJob();
    }
    protected static function daily_summary_timeSaved($key, $value)
    {
        self::daily_summarySaved($key, \true);
    }
    protected static function approval_modeValid($value)
    {
        if (self::between($value, 1, 2)) {
            return \true;
        }
        throw new \WappointmentException('Approval mode is not valid');
    }
    protected static function week_starts_onValid($value)
    {
        if (self::between($value, 0, 6)) {
            return \true;
        }
        throw new \WappointmentException('Week starts on is not valid');
    }
    protected static function date_time_unionValid($value)
    {
        if (empty($value)) {
            throw new \WappointmentException('Union field can\'t be empty');
        }
        return \true;
    }
    protected static function hours_before_booking_allowedValid($value)
    {
        if ($value < 0) {
            throw new \WappointmentException('This value must be greateer than 0');
        }
        return self::hourField($value);
    }
    protected static function hours_before_cancellation_allowedValid($value)
    {
        return self::hourField($value);
    }
    protected static function hours_before_rescheduling_allowedValid($value)
    {
        return self::hourField($value);
    }
    protected static function between($value, $minLimit, $maxLimit)
    {
        return \is_numeric($value) && $value >= $minLimit && $value <= $maxLimit ? \true : \false;
    }
    protected static function greaterEqualTo($value, $minLimit = 0)
    {
        return \is_numeric($value) && $value >= $minLimit ? \true : \false;
    }
    protected static function hourField($value)
    {
        if (self::greaterEqualTo($value)) {
            return \true;
        }
        throw new \WappointmentException('Hour field is not valid');
    }
    // remove slots that are in the future but are not bookable
    protected static function availabilityPrepare($availabilities)
    {
        $booking_allowed_from = Carbon::now()->timestamp + \Wappointment\Services\Settings::get('hours_before_booking_allowed') * 60 * 60;
        foreach ($availabilities as &$avail) {
            if ($booking_allowed_from > $avail[0]) {
                if ($booking_allowed_from < $avail[1]) {
                    $avail[0] = $booking_allowed_from;
                } else {
                    $avail = null;
                }
            }
        }
        //we clear the null records and reset the array index
        return \array_values(\array_filter($availabilities));
    }
    protected static function activeStaffIdValid($value)
    {
        return $value > 0 && WPHelpers::getUserBy('id', $value) !== \false ? \true : \false;
    }
    protected static function activeStaffIdBeforeSave($newStaffId)
    {
        //transfer all staff id settings to the right full owner
        WPHelpers::transferStaffOptions(\Wappointment\Services\Settings::get('activeStaffId'), $newStaffId);
    }
    protected static function email_notificationsValid($value)
    {
        $values = self::email_notificationsGetTransform($value);
        foreach ($values as $value) {
            if (!self::emailField($value)) {
                return \false;
            }
        }
        return \true;
    }
    protected static function email_notificationsGetTransform($value)
    {
        return \is_array($value) ? $value : \array_map('trim', \explode(',', $value));
    }
    protected static function emailField($value)
    {
        $validator = new RakitValidator();
        $validation = $validator->validate(['email' => $value], ['email' => 'required|email']);
        if ($validation->fails()) {
            $error = \sprintf('The Email %s is not valid', $value);
            throw new \WappointmentException($error);
        }
        return \true;
    }
}
