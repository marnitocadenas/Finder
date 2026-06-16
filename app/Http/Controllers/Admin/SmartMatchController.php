<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DismissedMatch;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Support\SmartMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SmartMatchController extends Controller
{
    public function index(Request $request): View
    {
        $lostItems = LostItem::with(['user', 'category'])
            ->where('status', 'lost')
            ->latest()
            ->take(50)
            ->get();

        $foundItems = FoundItem::with(['staff', 'category'])
            ->where('status', 'unclaimed')
            ->latest()
            ->take(120)
            ->get();

        $dismissedFoundIds = DismissedMatch::where('user_id', $request->user()->id)
            ->whereNotNull('found_item_id')
            ->pluck('found_item_id');

        $matches = SmartMatcher::buildMatches($lostItems, $foundItems, 3, $dismissedFoundIds);

        return view('admin.matches.index', compact('matches'));
    }
}
