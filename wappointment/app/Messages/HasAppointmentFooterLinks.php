<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;
trait HasAppointmentFooterLinks
{
    protected $separator = ' | ';
    private function calendarLink()
    {
        return '<a href="[appointment:linkAddEventToCalendar]">' . Settings::get('save_appointment_text_link') . '</a>';
    }
    private function rescheduleAndCancelLinks()
    {
        $links = '';
        if (apply_filters('wappointment_reschedule_allowed', Settings::get('allow_rescheduling'), $this->params)) {
            $links .= '<a href="[appointment:linkRescheduleEvent]">' . Settings::get('reschedule_link') . '</a>';
        }
        if (Settings::get('allow_cancellation')) {
            if (!empty($links)) {
                $links .= $this->separator;
            }
            $links .= '<a href="[appointment:linkCancelEvent]">' . Settings::get('cancellation_link') . '</a></p>';
        }
        return empty($links) ? '' : $links;
    }
    protected function footerLinks()
    {
        $footer = '';
        if (!empty(Settings::get('email_footer'))) {
            $footer .= '<p>' . \nl2br(wp_strip_all_tags(Settings::get('email_footer'))) . '</p>';
        }
        $rescheduleAndCancelLinks = $this->rescheduleAndCancelLinks();
        if (!empty($rescheduleAndCancelLinks)) {
            $rescheduleAndCancelLinks = $this->separator . $rescheduleAndCancelLinks;
        }
        $footer .= '<p>' . $this->calendarLink() . $rescheduleAndCancelLinks . '</p>';
        return $footer;
    }
}
