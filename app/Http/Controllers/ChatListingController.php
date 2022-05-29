<?php

namespace App\Http\Controllers;

use App\Models\ChatListing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatListingController extends Controller
{
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

        return Inertia::location(route('calendar'));
    }

    public function destroy(Request $request)
    {
        $chatListing = ChatListing::findOrFail($request->get('listing_id'));
        if ($chatListing->user->id !== auth()->user()->id) {
            abort(401, 'You can not delete others people listings');
        }

        $chatListing->delete();

        return Inertia::location(route('calendar'));
    }
}
