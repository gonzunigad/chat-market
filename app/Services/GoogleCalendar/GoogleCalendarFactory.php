<?php

namespace App\Services\GoogleCalendar;

use Google_Client;
use Google_Service_Calendar;
use Spatie\GoogleCalendar\Exceptions\InvalidConfiguration;
use Spatie\GoogleCalendar\GoogleCalendar;

class GoogleCalendarFactory extends \Spatie\GoogleCalendar\GoogleCalendarFactory
{
    public static function createForCalendarId(string $calendarId, string $oauthToken = null): GoogleCalendar
    {

        $config = config('google-calendar');
        $client = static::createAuthenticatedGoogleClient($config, $oauthToken);

        $service = new Google_Service_Calendar($client);

        return self::createCalendarClient($service, $calendarId);
    }

    public static function createAuthenticatedGoogleClient(array $config, string $oauthToken = null): Google_Client
    {
        $authProfile = $config['default_auth_profile'];

        if ($authProfile === 'service_account') {
            return self::createServiceAccountClient($config['auth_profiles']['service_account']);
        }
        if ($authProfile === 'oauth') {
            return self::createOAuthClient($config['auth_profiles']['oauth'], $oauthToken);
        }

        throw InvalidConfiguration::invalidAuthenticationProfile($authProfile);
    }

    protected static function createOAuthClient(array $authProfile, string $oauthToken = null): Google_Client
    {
        $client = new Google_Client;

        $client->setScopes([
            Google_Service_Calendar::CALENDAR,
        ]);

        $client->setAuthConfig($authProfile['credentials_json']);

        $client->setAccessToken($oauthToken);

        return $client;
    }
}
