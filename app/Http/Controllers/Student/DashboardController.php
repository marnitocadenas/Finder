<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\WatchedFoundItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $studentId = $request->user()->id;

        // Combine lost report counts into a single query
        $lostStats = LostItem::where('user_id', $studentId)
            ->selectRaw("COUNT(*) as total, SUM(status='lost') as open")
            ->first();
        $lostReports = $lostStats->total ?? 0;
        $openLostReports = (int) ($lostStats->open ?? 0);

        // Combine claim counts into a single query
        $claimStats = Claim::where('student_id', $studentId)
            ->selectRaw("COUNT(*) as total, SUM(status='pending') as pending, SUM(status='approved') as approved")
            ->first();
        $claims = $claimStats->total ?? 0;
        $pendingClaims = (int) ($claimStats->pending ?? 0);
        $approvedClaims = (int) ($claimStats->approved ?? 0);

        $availableFoundItems = FoundItem::where('status', 'unclaimed')->count();
        $unreadNotifications = $request->user()->notifications()->where('is_read', false)->count();
        $watchedItems = WatchedFoundItem::where('user_id', $studentId)->count();

        $possibleMatches = LostItem::where('user_id', $studentId)
            ->where('status', 'lost')
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('found_items')
                    ->whereColumn('found_items.category_id', 'lost_items.category_id')
                    ->where('found_items.status', 'unclaimed')
                    ->whereNull('found_items.deleted_at');
            })
            ->count();

        $readyForPickup = Claim::where('student_id', $studentId)
            ->where('status', 'approved')
            ->whereNull('released_at')
            ->count();

        $stats = [
            ['label' => 'My Lost Reports', 'value' => $lostReports, 'helper' => $openLostReports.' still open', 'icon' => 'fa-magnifying-glass', 'color' => 'danger'],
            ['label' => 'My Claims', 'value' => $claims, 'helper' => $pendingClaims.' pending review', 'icon' => 'fa-file-signature', 'color' => 'primary'],
            ['label' => 'Approved Claims', 'value' => $approvedClaims, 'helper' => 'Ready for staff pickup guidance', 'icon' => 'fa-circle-check', 'color' => 'success'],
            ['label' => 'Found Items', 'value' => $availableFoundItems, 'helper' => 'Available to browse', 'icon' => 'fa-box-open', 'color' => 'warning'],
        ];

        $overview = [
            [
                'label' => 'Open lost reports',
                'value' => $openLostReports,
                'icon' => 'fa-magnifying-glass',
                'tone' => 'danger',
                'route' => route('student.lost-items.index', ['status' => 'lost']),
            ],
            [
                'label' => 'Pending claims',
                'value' => $pendingClaims,
                'icon' => 'fa-hourglass-half',
                'tone' => 'warning',
                'route' => route('student.claims.index', ['status' => 'pending']),
            ],
            [
                'label' => 'Unread alerts',
                'value' => $unreadNotifications,
                'icon' => 'fa-bell',
                'tone' => 'primary',
                'route' => route('notifications'),
            ],
            [
                'label' => 'Possible matches',
                'value' => $possibleMatches,
                'icon' => 'fa-wand-magic-sparkles',
                'tone' => 'success',
                'route' => route('student.matches'),
            ],
            [
                'label' => 'Ready for pickup',
                'value' => $readyForPickup,
                'icon' => 'fa-handshake',
                'tone' => 'warning',
                'route' => route('student.claims.index', ['status' => 'approved']),
            ],
            [
                'label' => 'Saved found items',
                'value' => $watchedItems,
                'icon' => 'fa-bookmark',
                'tone' => 'primary',
                'route' => route('student.watchlist'),
            ],
        ];

        $recentLostReports = LostItem::with('category')
            ->where('user_id', $studentId)
            ->latest()
            ->take(5)
            ->get();

        $recentClaims = Claim::with(['foundItem.category'])
            ->where('student_id', $studentId)
            ->latest()
            ->take(5)
            ->get();

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(4)
            ->get();

        return view('dashboards.student', compact('stats', 'overview', 'recentLostReports', 'recentClaims', 'notifications'));
    }
}
