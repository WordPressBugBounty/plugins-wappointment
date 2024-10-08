<?php

namespace Wappointment\Services;

use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Helpers\Translations;
use Wappointment\Models\Location;
// @codingStandardsIgnoreFile
class Service implements \Wappointment\Services\ServiceInterface
{
    public static function save($serviceData)
    {
        $validator = new RakitValidator();
        $validation_messages = ['type' => __('Please select how do you perform the service', 'wappointment'), 'options.countries' => __('You need to select countries you will cover for the phone service', 'wappointment')];
        $validator->setMessages(apply_filters('wappointment_service_validation_messages', $validation_messages));
        $validationRules = ['name' => 'required|is_adv_string|max:100', 'duration' => 'required|numeric', 'type' => 'required|array|hasvalues:physical,phone,skype,zoom', 'address' => 'required_if_has:type,physical', 'options' => '', 'options.countries' => 'required_if_has:type,phone|array', 'options.video' => 'required_if_has:type,zoom'];
        $validationRules = apply_filters('wappointment_service_validation_rules', $validationRules);
        $validation = $validator->make($serviceData, $validationRules);
        $validation->validate();
        if ($validation->fails()) {
            throw new \WappointmentValidationException(Translations::get('error_saving'), 1, null, $validation->errors()->toArray());
            return $validation->errors()->toArray();
        }
        return static::saveService($serviceData);
    }
    public static function saveService($serviceData)
    {
        $service = static::get('service');
        // to test the existing service
        $serviceData['options'] = \array_merge($service['options'], $serviceData['options']);
        $serviceData = apply_filters('wappointment_service_before_saved', $serviceData, $service);
        //  return $serviceData;
        $resultSave = (bool) \Wappointment\Services\Settings::save('service', $serviceData);
        if (empty($service['name']) && empty($service['type'])) {
            self::createdService($serviceData['type']);
        }
        do_action('wappointment_service_saved', $serviceData);
        return $resultSave;
    }
    public static function patch($service_id, $data)
    {
        $serviceDB = static::get('service');
        $data['options'] = \array_merge($serviceDB['options'], $data['options']);
        $serviceDB = \array_merge($serviceDB, $data);
        \Wappointment\Services\Settings::save('service', $serviceDB);
    }
    public static function get($service_id = \false)
    {
        return \Wappointment\Services\Settings::get('service');
    }
    public static function getObject($service_id = \false)
    {
        return new \Wappointment\Decorators\Service(static::get());
    }
    public static function all()
    {
        return [static::get()];
    }
    public static function hasZoom($service)
    {
        return \in_array('zoom', $service['type']);
    }
    private static function createdService($types)
    {
        foreach (\Wappointment\Services\Reminder::getSeeds($types) as $reminder) {
            \Wappointment\Services\Reminder::save($reminder);
        }
    }
    public static function updateLocations($types, $options, $address)
    {
        $typeId = [];
        foreach ($types as $type_name) {
            $typeId[] = static::getLocationTypeId($type_name);
        }
        $locations = Location::whereIn('type', $typeId)->get();
        $types = [];
        foreach ($locations as $location) {
            $optionsTemp = $location->options;
            if ($location->type == Location::TYPE_ZOOM) {
                $optionsTemp['video'] = $options['video'];
                $types[] = 'zoom';
            }
            if ($location->type == Location::TYPE_AT_LOCATION) {
                $optionsTemp['address'] = $address;
                $types[] = 'physical';
            }
            if ($location->type == Location::TYPE_PHONE) {
                $optionsTemp['countries'] = $options['countries'];
                $types[] = 'phone';
            }
            if ($location->type == Location::TYPE_SKYPE) {
                $types[] = 'skype';
            }
            $location->options = $optionsTemp;
            $location->save();
        }
        if (!\Wappointment\Services\Flag::get('remindersSaved')) {
            if (\Wappointment\Models\Reminder::count() < 1) {
                foreach (\Wappointment\Services\Reminder::getSeeds($types) as $reminder) {
                    \Wappointment\Services\Reminder::save($reminder);
                }
            }
            \Wappointment\Services\Flag::save('remindersSaved', \true);
        }
        return $locations->map(function ($locationObj) {
            return $locationObj->id;
        });
    }
    public static function getLocationTypeId($type_name)
    {
        if ($type_name == 'skype') {
            return Location::TYPE_SKYPE;
        }
        if ($type_name == 'zoom') {
            return Location::TYPE_ZOOM;
        }
        if ($type_name == 'physical') {
            return Location::TYPE_AT_LOCATION;
        }
        if ($type_name == 'phone') {
            return Location::TYPE_PHONE;
        }
    }
}
