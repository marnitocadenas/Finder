<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DismissedMatch;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Support\SmartMatcher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartMatchController extends Controller
{
    public function index(Request $request): View
    {
        $foundItems = FoundItem::with('category')
            ->where('staff_id', $request->user()->id)
            ->where('status', 'unclaimed')
            ->latest()
            ->take(50)
            ->get();

        $lostItems = LostItem::with(['user', 'category'])
            ->where('status', 'lost')
            ->latest()
            ->take(120)
            ->get();

        $dismissedLostIds = DismissedMatch::where('user_id', $request->user()->id)
            ->whereNotNull('lost_item_id')
            ->pluck('lost_item_id');

        $matches = SmartMatcher::buildReverseMatches($foundItems, $lostItems, 4, $dismissedLostIds);

        return view('staff.matches.index', compact('matches'));
    }

    /**
     * Personal smart matches: staff's own lost reports matched against all found items.
     */
    public function personalMatches(Request $request): View
    {
        $lostItems = LostItem::with('category')
            ->where('user_id', $request->user()->id)
            ->where('status', 'lost')
            ->latest()
            ->get();

        $foundItems = FoundItem::with('category')
            ->where('status', 'unclaimed')
            ->latest()
            ->take(120)
            ->get();

        $dismissedFoundIds = DismissedMatch::where('user_id', $request->user()->id)
            ->whereNotNull('found_item_id')
            ->pluck('found_item_id');

        $matches = SmartMatcher::buildMatches($lostItems, $foundItems, 4, $dismissedFoundIds);

        return view('student.matches.index', [
            'matches' => $matches,
            'role' => 'staff',
            'claimRoute' => 'staff.my-claims',
        ]);
    }
}
