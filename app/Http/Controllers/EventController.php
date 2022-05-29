<?php

namespace App\Http\Controllers;

use App\Models\ChatListing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\GoogleCalendar\Event;

class EventController extends Controller
{
    public function index()
    {
        $calendarEvents = Event::get();
        $calendarEventsIds = $calendarEvents->pluck('googleEvent.id');
        $chatListings = ChatListing::whereIn('google_calendar_event_id', $calendarEventsIds)->with('user', 'accepted_by_user')->get();

        $events = $calendarEvents->map(function($event) use ($chatListings) {
            return $this->mapEventData($event, $chatListings);
        });

        $yourEvents = $events->filter(function($event) {
            $myEmail = auth()->user()->email;
            foreach ($event['attendees'] as $attendee) {
                if ($attendee['email'] == $myEmail) {
                    return true;
                }
            }
            return false;
        });

        $listings = $events->filter(function ($event)  {
            return $event['listing'] !== null && $event['listing']['accepted_by'] === null;
        });

        $myListingsCount = $chatListings->where('user_id', auth()->user()->id)->whereNotNull('accepted_at')->count();
        $myAcceptedCount = $chatListings->where('accepted_by', auth()->user()->id)->count();
        $chatCoins = $myAcceptedCount - $myListingsCount;

        return Inertia::render('Calendar', ['yourEvents' => $yourEvents, 'listings' => $listings, 'chatCoins' => $chatCoins]);

    }
    /**
     * @param $event
     * @return array
     */
    function mapEventData($event, $chatListings): array
    {
        $id = $event->googleEvent->id;
        $time = Carbon::parse($event->googleEvent->start->dateTime, $event->googleEvent->start->timeZone);
        $listing = $chatListings->firstWhere('google_calendar_event_id', $id);

        return [
            'id' => $id,
            'summary' => $event->googleEvent->summary,
            'start' => $time,
            'canBePublished' => $listing === null,
            'listing' => $listing,
            'friendlyTime' => $time->isoFormat('D \d\e MMMM hh:mm'),
            'dayOfWeek' => $time->isoFormat('dddd'),
            'timezone' => $event->googleEvent->start->timeZone,
            'attendees' => collect($event->googleEvent->attendees)->map(function ($attendee) {
                return [
                    'email' => $attendee['email'],
                    'username' => strstr($attendee['email'], '@', true)
                ];
            })
        ];
    }
}
