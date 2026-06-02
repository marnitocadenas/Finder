<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Claim;
use App\Models\FoundItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::where('user_id', $request->user()->id)
            ->filtered($request->all())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $postedItems = FoundItem::where('staff_id', $request->user()->id)->count();
        $reviewedClaims = Claim::where('reviewed_by', $request->user()->id)->count();
        $releasedItems = Claim::where('released_by', $request->user()->id)->count();

        return view('staff.activity.index', compact('logs', 'postedItems', 'reviewedClaims', 'releasedItems'));
    }
}
