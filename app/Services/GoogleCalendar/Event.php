<?php

namespace App\Services\GoogleCalendar;

use Google_Client;
use Spatie\GoogleCalendar\GoogleCalendar;

class Event extends \Spatie\GoogleCalendar\Event
{
    static string|null $oauthToken = null;

    protected static function getGoogleCalendar(string $calendarId = null): GoogleCalendar
    {
        $calendarId = $calendarId ?? config('google-calendar.calendar_id');

        return GoogleCalendarFactory::createForCalendarId($calendarId, static::$oauthToken);
    }
}
