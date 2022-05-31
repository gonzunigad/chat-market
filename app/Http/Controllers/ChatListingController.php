<?php

namespace App\Http\Controllers;

use App\Models\ChatListing;
use App\Services\GoogleCalendar\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatListingController extends Controller
{
    const DOUBLE_KARMA_OFFERED_COINS = 2;

    public function store(Request $request)
    {
        $this->validate($request, [
            'event_id' => 'required'
        ]);

        $chatListing = new ChatListing();
        $chatListing->google_calendar_event_id = $request->get('event_id');
        $chatListing->user_id = auth()->user()->getAuthIdentifier();
        $chatListing->offered_coins = $request->get('offered_coins', 1);
        $chatListing->save();

        session()->flash('message', ['type' => 'success', 'message' => 'Publicación creada correctamente']);

        return Inertia::location(route('calendar'));
    }

    public function take(Request $request)
    {
        $this->validate($request, [
            'listing_id' => 'required'
        ]);

        $chatListing = ChatListing::findOrFail($request->get('listing_id'));
        if ($chatListing->user->id === auth()->user()->id) {
            abort(401, 'You can not take your own listing');
        }

        $chatListing->accepted_at = Carbon::now();
        $chatListing->accepted_by = auth()->user()->id;
        $chatListing->save();

        Event::$oauthToken = auth()->user()->google_token;
        $googleCalendarEvent = Event::find($chatListing->google_calendar_event_id);
        $myUsername = strstr( auth()->user()->email, '@', true);
        $listingOwnerUsername = strstr($chatListing->user->email, '@', true);
        $listingOwnerEmail = $chatListing->user->email;

        $eventName = $googleCalendarEvent->googleEvent->getSummary();
        $eventName = str_replace($listingOwnerUsername, $myUsername . ' (reemplaza a ' . $listingOwnerUsername .')', $eventName);

        $this->setEventAttendees($googleCalendarEvent, $listingOwnerEmail);
        $googleCalendarEvent->googleEvent->summary = $eventName;
        $googleCalendarEvent->save();

        session()->flash('message', ['type' => 'success', 'message' => 'Turno tomado correctamente']);

        return Inertia::location(route('calendar'));
    }

    public function destroy(Request $request)
    {
        $chatListing = ChatListing::findOrFail($request->get('listing_id'));
        if ($chatListing->user->id !== auth()->user()->id) {
            abort(401, 'You can not delete others people listings');
        }

        $chatListing->delete();

        session()->flash('message', ['type' => 'success', 'message' => 'Publicación eliminada correctamente']);

        return Inertia::location(route('calendar'));
    }

    public function upgrade(Request $request)
    {
        $chatListing = ChatListing::findOrFail($request->get('listing_id'));
        if ($chatListing->user->id !== auth()->user()->id) {
            abort(401, 'You can not upgrade others people listings');
        }

        $chatListing->offered_coins = static::DOUBLE_KARMA_OFFERED_COINS;
        $chatListing->save();

        session()->flash('message', ['type' => 'success', 'message' => 'Ahora tu publicación está destacada. Si alguien la acepta deberás pagar dos turnos.']);

        return Inertia::location(route('calendar'));
    }

    /**
     * @param \Spatie\GoogleCalendar\Event $googleCalendarEvent
     * @param $listingOwnerEmail
     * @return void
     */
    public function setEventAttendees(\Spatie\GoogleCalendar\Event $googleCalendarEvent, $listingOwnerEmail): void
    {
        collect($googleCalendarEvent->googleEvent->getAttendees())->filter(function ($attendee) use ($listingOwnerEmail
            ) {
                return $attendee['email'] !== $listingOwnerEmail;
            })->each(function ($attendee) use ($googleCalendarEvent) {
                $googleCalendarEvent->addAttendee([
                    'displayName' => $attendee['displayName'],
                    'comment' => $attendee['comment'],
                    'email' => $attendee['email']
                ]);
            });

        $googleCalendarEvent->addAttendee([
            'displayName' => auth()->user()->name,
            'comment' => '',
            'email' => auth()->user()->email
        ]);
    }
}
