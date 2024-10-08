<?php

namespace Wappointment\Services;

use Wappointment\Remote\Request as RequestRemote;
use Wappointment\WP\Helpers as WPHelpers;
// @codingStandardsIgnoreFile
class Calendar
{
    public $url = '';
    public $staff = \false;
    public $response = null;
    public $calendar_id = null;
    public $calendar_logs = [];
    private $legacy = \false;
    public function __construct($calendar_url, $staff, $legacy = \true)
    {
        $this->url = $calendar_url;
        $this->staff = $staff;
        $this->calendar_id = \md5($this->url);
        $this->legacy = $legacy;
        $this->calendar_logs = $this->loadCalendarLogs();
    }
    protected function getStaffId()
    {
        return $this->legacy ? $this->staff : $this->staff->id;
    }
    protected function loadCalendarLogs()
    {
        return $this->legacy ? WPHelpers::getStaffOption('calendar_logs') : $this->returnStaffLogs();
    }
    public function returnStaffLogs()
    {
        return !empty($this->staff->options['calendar_logs']) ? $this->staff->options['calendar_logs'] : [];
    }
    public function refetch()
    {
        //make sure we can process it fully again
        $this->log('last-hash', \false);
        return $this->fetch();
    }
    public function fetch()
    {
        $start = \microtime(\true);
        $client = new RequestRemote();
        $responseCalendar = $client->getCalendar($this->url);
        // Only headers are downloaded here.
        if ($client->failed()) {
            throw new \WappointmentException(__('Cannot connect to the calendar', 'wappointment'));
        }
        if (!$client->headerIsEqual('content-type', 'text/calendar')) {
            throw new \WappointmentException('Invalid calendar');
        }
        $original_content = $responseCalendar->getContent();
        $body_string = $this->cleanContent($original_content);
        $result = \false;
        $this->log('last-checked', \time());
        if ($this->hasChanged($body_string)) {
            $parser = new \Wappointment\Services\CalendarParser($this->url, $original_content, $this->getStaffId());
            $this->log('last-parser', $parser->handle());
            $this->log('last-hash', \md5($body_string), \false);
            $this->log('last-parsed', \time(), \false);
            $result = \true;
        }
        $this->log('last-duration', \round(\microtime(\true) - $start, 2));
        return $result;
    }
    private function getCalendarLogs()
    {
        if (empty($this->calendar_logs[$this->calendar_id])) {
            return ['last-checked' => \false, 'last-hash' => \false, 'last-parsed' => \false, 'last-duration' => \false, 'last-parser' => \false];
        }
        return $this->calendar_logs[$this->calendar_id];
    }
    private function log($property, $value, $save = \true)
    {
        if (empty($this->calendar_logs[$this->calendar_id])) {
            $this->calendar_logs[$this->calendar_id] = $this->getCalendarLogs();
        }
        $this->calendar_logs[$this->calendar_id][$property] = $value;
        if ($save) {
            $this->saveCalendarLogs();
        }
    }
    private function saveCalendarLogs()
    {
        if ($this->legacy) {
            return WPHelpers::setStaffOption('calendar_logs', $this->calendar_logs, $this->getStaffId());
        } else {
            $options = $this->staff->options;
            $options['calendar_logs'] = $this->calendar_logs;
            $this->staff->update(['options' => $options]);
        }
    }
    private function cleanContent($content)
    {
        \preg_match('/^PRODID:.*\\n/m', $content, $matches);
        if (!empty($matches) && \strpos(\strtolower($matches[0]), 'google') !== \false) {
            return $this->googleClean($content);
        }
        return $content;
    }
    // used to then store a md5 version of the ics making sure it is cached
    private function googleClean($in)
    {
        $in = \preg_replace('/^DTSTAMP:.*\\n/m', '', $in);
        $in = \preg_replace('/^SUMMARY:.*\\n/m', '', $in);
        $in = \preg_replace('/^ACTION:.*\\n/m', '', $in);
        $in = \preg_replace('/^ATTENDEE:.*\\n/m', '', $in);
        return \preg_replace('/^TRIGGER:.*\\n/m', '', $in);
    }
    private function hasChanged($content)
    {
        return \md5($content) != $this->calendar_logs[$this->calendar_id]['last-hash'];
    }
}
