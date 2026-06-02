<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $categoryLabels = Category::orderBy('name')->pluck('name');
        $categoryCounts = Category::withCount(['lostItems', 'foundItems'])
            ->orderBy('name')
            ->get()
            ->map(fn($category) => $category->lost_items_count + $category->found_items_count);

        $claimsByMonth = Claim::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $rawOutcomes = Claim::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $outcomes = collect(['approved', 'pending', 'rejected'])
            ->mapWithKeys(fn($status) => [$status => (int) ($rawOutcomes[$status] ?? 0)]);

        $totalLostItems = LostItem::count();
        $totalFoundItems = FoundItem::count();
        $openLostItems = LostItem::where('status', 'lost')->count();
        $unclaimedFoundItems = FoundItem::where('status', 'unclaimed')->count();
        $totalItems = $totalLostItems + $totalFoundItems;
        $totalClaims = Claim::count();
        $approvedClaims = Claim::where('status', 'approved')->count();
        $pendingClaims = Claim::where('status', 'pending')->count();
        $rejectedClaims = Claim::where('status', 'rejected')->count();
        $resolutionRate = $totalClaims > 0 ? round(($approvedClaims / $totalClaims) * 100) : 0;
        $reviewedClaims = $approvedClaims + $rejectedClaims;

        $reportStats = [
            ['label' => 'Total Items', 'value' => $totalItems, 'helper' => 'Lost and found records', 'icon' => 'fa-boxes-stacked', 'tone' => 'primary'],
            ['label' => 'Total Claims', 'value' => $totalClaims, 'helper' => 'Submitted requests', 'icon' => 'fa-file-signature', 'tone' => 'warning'],
            ['label' => 'Pending Claims', 'value' => $pendingClaims, 'helper' => 'Awaiting review', 'icon' => 'fa-hourglass-half', 'tone' => 'danger'],
            ['label' => 'Resolution Rate', 'value' => $resolutionRate.'%', 'helper' => $approvedClaims.' approved, '.$rejectedClaims.' rejected', 'icon' => 'fa-circle-check', 'tone' => 'success'],
        ];

        return view('admin.reports.index', compact(
            'categoryLabels',
            'categoryCounts',
            'claimsByMonth',
            'outcomes',
            'totalItems',
            'totalClaims',
            'approvedClaims',
            'pendingClaims',
            'resolutionRate',
            'rejectedClaims',
            'reviewedClaims',
            'totalLostItems',
            'totalFoundItems',
            'openLostItems',
            'unclaimedFoundItems',
            'reportStats'
        ));
    }

    public function export(): StreamedResponse
    {
        $filename = 'tmc-lost-found-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Lost records', LostItem::count()]);
            fputcsv($handle, ['Open lost records', LostItem::where('status', 'lost')->count()]);
            fputcsv($handle, ['Found records', FoundItem::count()]);
            fputcsv($handle, ['Unclaimed found records', FoundItem::where('status', 'unclaimed')->count()]);
            fputcsv($handle, ['Claims', Claim::count()]);
            fputcsv($handle, ['Pending claims', Claim::where('status', 'pending')->count()]);
            fputcsv($handle, ['Approved claims', Claim::where('status', 'approved')->count()]);
            fputcsv($handle, ['Rejected claims', Claim::where('status', 'rejected')->count()]);
            fputcsv($handle, []);
            fputcsv($handle, ['Category', 'Lost Items', 'Found Items']);

            Category::withCount(['lostItems', 'foundItems'])->orderBy('name')->each(function ($category) use ($handle) {
                fputcsv($handle, [$category->name, $category->lost_items_count, $category->found_items_count]);
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
