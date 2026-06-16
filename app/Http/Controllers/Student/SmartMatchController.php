<?php

namespace App\Http\Controllers\Student;

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
            'role' => 'student',
            'claimRoute' => 'student.claims',
        ]);
    }
}
