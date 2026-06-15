<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $ids = FoundItem::where('staff_id', $request->user()->id)->pluck('id');
        $itemsPosted = $ids->count();
        $pendingClaims = Claim::whereIn('found_item_id', $ids)->where('status', 'pending')->count();
        $approvedClaims = Claim::whereIn('found_item_id', $ids)->where('status', 'approved')->count();
        $unclaimedItems = FoundItem::where('staff_id', $request->user()->id)->where('status', 'unclaimed')->count();
        $agingItems = FoundItem::where('staff_id', $request->user()->id)->where('status', 'unclaimed')->where('created_at', '<=', now()->subDays(14))->count();
        $readyForRelease = Claim::whereIn('found_item_id', $ids)->where('status', 'approved')->whereNull('released_at')->count();
        $releasedThisMonth = Claim::where('released_by', $request->user()->id)->whereMonth('released_at', now()->month)->whereYear('released_at', now()->year)->count();
        $possibleMatches = FoundItem::where('staff_id', $request->user()->id)
            ->where('status', 'unclaimed')
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('lost_items')
                    ->whereColumn('lost_items.category_id', 'found_items.category_id')
                    ->where('lost_items.status', 'lost')
                    ->whereNull('lost_items.deleted_at');
            })
            ->count();

        $myLostReports = LostItem::where('user_id', $request->user()->id)->count();
        $myOpenLostReports = LostItem::where('user_id', $request->user()->id)->where('status', 'lost')->count();
        $myClaimsFiled = Claim::where('student_id', $request->user()->id)->count();

        $stats = [
            ['label' => 'Items Posted', 'value' => $itemsPosted, 'icon' => 'fa-box-open', 'color' => 'primary'],
            ['label' => 'Pending Claims', 'value' => $pendingClaims, 'icon' => 'fa-clock', 'color' => 'warning'],
            ['label' => 'My Lost Reports', 'value' => $myLostReports, 'icon' => 'fa-magnifying-glass', 'color' => 'danger'],
            ['label' => 'My Claims Filed', 'value' => $myClaimsFiled, 'icon' => 'fa-file-signature', 'color' => 'success'],
        ];

        $workQueue = [
            [
                'label' => 'Pending claim reviews',
                'value' => $pendingClaims,
                'icon' => 'fa-hourglass-half',
                'tone' => 'warning',
                'route' => route('staff.claims.index', ['status' => 'pending']),
            ],
            [
                'label' => 'My open lost reports',
                'value' => $myOpenLostReports,
                'icon' => 'fa-magnifying-glass',
                'tone' => 'danger',
                'route' => route('staff.lost-items.index', ['status' => 'lost']),
            ],
            [
                'label' => 'Unclaimed found items',
                'value' => $unclaimedItems,
                'icon' => 'fa-box-open',
                'tone' => 'danger',
                'route' => route('staff.found-items.index', ['status' => 'unclaimed']),
            ],
            [
                'label' => 'Open lost reports',
                'value' => LostItem::where('status', 'lost')->count(),
                'icon' => 'fa-magnifying-glass',
                'tone' => 'primary',
                'route' => route('staff.lost-reports.index', ['status' => 'lost']),
            ],
            [
                'label' => 'Possible matches',
                'value' => $possibleMatches,
                'icon' => 'fa-wand-magic-sparkles',
                'tone' => 'success',
                'route' => route('staff.matches'),
            ],
            [
                'label' => 'Ready for pickup',
                'value' => $readyForRelease,
                'icon' => 'fa-handshake',
                'tone' => 'warning',
                'route' => route('staff.claims.index', ['status' => 'approved']),
            ],
            [
                'label' => 'Aging unclaimed',
                'value' => $agingItems,
                'icon' => 'fa-triangle-exclamation',
                'tone' => 'danger',
                'route' => route('staff.found-items.index', ['status' => 'unclaimed']),
            ],
        ];

        $recentFoundItems = FoundItem::with('category')
            ->where('staff_id', $request->user()->id)
            ->latest()
            ->take(5)
            ->get();

        $recentClaims = Claim::with(['student', 'foundItem.category'])
            ->whereIn('found_item_id', $ids)
            ->latest()
            ->take(5)
            ->get();

        $performance = [
            ['label' => 'Posted this month', 'value' => FoundItem::where('staff_id', $request->user()->id)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(), 'icon' => 'fa-calendar-plus', 'tone' => 'primary'],
            ['label' => 'Reviewed claims', 'value' => Claim::where('reviewed_by', $request->user()->id)->count(), 'icon' => 'fa-clipboard-check', 'tone' => 'success'],
            ['label' => 'Released this month', 'value' => $releasedThisMonth, 'icon' => 'fa-handshake', 'tone' => 'warning'],
        ];

        return view('dashboards.staff', compact('stats', 'workQueue', 'recentFoundItems', 'recentClaims', 'performance'));
    }
}
