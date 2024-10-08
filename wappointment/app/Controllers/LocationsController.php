<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Helpers\Translations;
use Wappointment\Models\Location;
use Wappointment\Services\Location as LocationService;
// @codingStandardsIgnoreFile
class LocationsController extends RestController
{
    public function get(Request $request)
    {
        $locations = Location::get();
        if ($request->input('usable')) {
            $locations = $locations->filter(function ($value) {
                if ($value->type == Location::TYPE_AT_LOCATION && empty($value->options['address'])) {
                    return \false;
                }
                if ($value->type == Location::TYPE_PHONE && empty($value->options['countries'])) {
                    return \false;
                }
                if ($value->type == Location::TYPE_ZOOM && empty($value->options['video'])) {
                    return \false;
                }
                return \true;
            });
        }
        return \array_values($locations->toArray());
    }
    public function save(Request $request)
    {
        $result = LocationService::save($request->only(['id', 'name', 'type', 'options']));
        return ['message' => Translations::get('element_saved'), 'result' => $result, 'locations' => $this->get($request)];
    }
    public function delete(Request $request)
    {
        if ((int) $request->input('id') < 5) {
            throw new \WappointmentException(Translations::get('error_deleting'), 1);
        }
        return ['message' => Translations::get('element_deleted'), 'result' => Location::destroy($request->input('id')), 'deleted' => $request->input('id')];
    }
}
