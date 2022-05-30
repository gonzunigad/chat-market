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
        $cacheExpirationTimeInSeconds = 60*60*24*31;
        $calendarEvents = cache()->remember('events', $cacheExpirationTimeInSeconds , function() {
          return Event::get();
        });

        $calendarEventIds = $calendarEvents->pluck('googleEvent.id');
        $chatListings = ChatListing::whereIn('google_calendar_event_id', $calendarEventIds)
            ->with('user', 'accepted_by_user')
            ->get();

        $events = $this->mapEvents($calendarEvents, $chatListings);
        $yourEvents = $this->getYourEvents($events);
        $listings = $this->getListings($events);

        $chatCoins = $this->getChatCoins($chatListings);

        return Inertia::render('Calendar', [
            'yourEvents' => $yourEvents->toArray(),
            'listings' => $listings,
            'chatCoins' => $chatCoins
        ]);

    }

    /**
     * @param $event
     * @return array
     */
    function mapEventData($event, $chatListings): array
    {
        $id = $event->googleEvent->id;
        $time = Carbon::parse($event->googleEvent->start->dateTime, $event->googleEvent->start->timeZone);
        $listing = $chatListings->sortBy('accepted_by')->firstWhere('google_calendar_event_id', $id);

        return [
            'id' => $id,
            'summary' => $event->googleEvent->summary,
            'start' => $time,
            'canBePublished' => $listing === null || $listing['user']['id'] !== auth()->user()->id,
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
    /**
     * @param \Illuminate\Support\Collection $events
     * @return \Illuminate\Support\Collection
     */
    public function getYourEvents(\Illuminate\Support\Collection $events): \Illuminate\Support\Collection
    {
        $myEmail = auth()->user()->email;
        $myId = auth()->user()->id;

        return $events->filter(function ($event) use ($myEmail, $myId) {
            if ($event['listing']['accepted_by_user']['id'] ?? null === $myId) {
                return true;
            }

            foreach ($event['attendees'] as $attendee) {
                if ($attendee['email'] == $myEmail) {
                    return true;
                }
            }

            return false;
        })->values();
    }
    /**
     * @param \Illuminate\Support\Collection $events
     * @return \Illuminate\Support\Collection
     */
    public function getListings(\Illuminate\Support\Collection $events): \Illuminate\Support\Collection
    {
        return $events->filter(function ($event) {
            return $event['listing'] !== null && $event['listing']['accepted_by'] === null;
        });
    }
    /**
     * @param $chatListings
     * @return mixed
     */
    public function getChatCoins($chatListings)
    {
        $myListingsCount = $chatListings->where('user_id', auth()->user()->id)->whereNotNull('accepted_at')->sum('offered_coins');
        $myAcceptedCount = $chatListings->where('accepted_by', auth()->user()->id)->sum('offered_coins');
        $chatCoins = $myAcceptedCount - $myListingsCount;

        return $chatCoins;
    }
    /**
     * @param \Illuminate\Support\Collection $calendarEvents
     * @param $chatListings
     * @return \Illuminate\Support\Collection
     */
    public function mapEvents(\Illuminate\Support\Collection $calendarEvents, $chatListings
    ): \Illuminate\Support\Collection {
        $events = $calendarEvents->map(function ($event) use ($chatListings) {
            return $this->mapEventData($event, $chatListings);
        });

        return $events;
    }
}
