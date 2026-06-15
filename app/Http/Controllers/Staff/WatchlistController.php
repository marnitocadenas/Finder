<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\WatchedFoundItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function index(Request $request): View
    {
        $watched = WatchedFoundItem::with('foundItem.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('student.watchlist.index', [
            'role' => 'staff',
            'claimRoute' => 'staff.my-claims',
            'watched' => $watched,
        ]);
    }

    public function store(Request $request, FoundItem $foundItem): RedirectResponse
    {
        WatchedFoundItem::firstOrCreate([
            'user_id' => $request->user()->id,
            'found_item_id' => $foundItem->id,
        ]);

        return back()->with('success', 'Found item saved to your watchlist.');
    }

    public function destroy(Request $request, FoundItem $foundItem): RedirectResponse
    {
        WatchedFoundItem::where('user_id', $request->user()->id)
            ->where('found_item_id', $foundItem->id)
            ->delete();

        return back()->with('success', 'Found item removed from your watchlist.');
    }
}
