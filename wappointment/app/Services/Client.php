<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Site;
use Wappointment\Models\Client as MClient;
use Wappointment\Validators\HttpRequest\Booking;
// @codingStandardsIgnoreFile
class Client
{
    public static function book(Booking $booking)
    {
        $client = static::clientLoadAdd($booking);
        if (static::maxActiveBookingReached($client, $booking)) {
            throw new \WappointmentException(__('Max active bookings reached! Cancel one of your appointments in order to book a new one.', 'wappointment'), 1);
        }
        //book with that client
        return $client->book($booking);
    }
    public static function bookLegacy(Booking $booking)
    {
        $client = static::clientLoadAdd($booking);
        //book with that client
        return $client->bookLegacy($booking);
    }
    protected static function maxActiveBookingReached(MClient $client, Booking $booking)
    {
        $max_active_bookings = (int) \Wappointment\Services\Settings::get('max_active_bookings');
        return $max_active_bookings > 0 && $client->hasActiveBooking($booking->get('staff_id')) >= $max_active_bookings;
    }
    protected static function clientLoadAdd(Booking $booking)
    {
        //create or load client account
        $client = MClient::where('email', $booking->getUserEmail())->withTrashed()->first();
        if (!empty($client) && !empty($client->deleted_at)) {
            $client->restore();
        }
        $dataClient = $booking->preparedData();
        $dataClient['options'] = static::addRtl($dataClient['options']);
        if (empty($dataClient['name'])) {
            $dataClient['name'] = '';
        }
        if (empty($dataClient['options']['locale'])) {
            $dataClient['options']['locale'] = Site::locale();
        }
        if (empty($client)) {
            $client = MClient::create($dataClient);
        } else {
            unset($dataClient['email']);
            $options = $client->options;
            foreach ($dataClient as $key => $value) {
                if ($key !== 'options') {
                    $client->{$key} = $value;
                }
            }
            foreach ($dataClient['options'] as $key => $optionvalue) {
                $options[$key] = $optionvalue;
            }
            $client->options = $options;
            $client->save();
        }
        return $client;
    }
    public static function search($email, $size = 30)
    {
        $clients = MClient::where('email', 'like', $email . '%')->get()->toArray();
        foreach ($clients as &$client) {
            $client['avatar'] = get_avatar_url($client['email'], ['size' => $size]);
        }
        return $clients;
    }
    protected static function addRtl($options)
    {
        $options['rtl'] = is_rtl();
        return $options;
    }
    public static function save($data)
    {
        $data['options'] = static::addRtl($data['options']);
        //create or load client account
        $client = MClient::firstOrCreate(['email' => $data['email']], ['name' => $data['name'], 'options' => ['tz' => $data['options']['tz'], 'skype' => $data['options']['skype'], 'phone' => $data['options']['phone']]]);
        $options = $client->options;
        foreach ($data['options'] as $key => $value) {
            $options[$key] = $value;
        }
        $client->options = $options;
        $client->name = $data['name'];
        $client->save();
        //book with that client
        return $client;
    }
}
