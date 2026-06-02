<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\WatchedFoundItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrowseController extends Controller
{
    public function index(Request $request): View
    {
        $browseStats = [
            ['label' => 'Available', 'value' => FoundItem::where('status', 'unclaimed')->count(), 'icon' => 'fa-box-open', 'tone' => 'success'],
            ['label' => 'Claimed', 'value' => FoundItem::where('status', 'claimed')->count(), 'icon' => 'fa-circle-check', 'tone' => 'primary'],
            ['label' => 'Turned Over', 'value' => FoundItem::where('status', 'turned_over')->count(), 'icon' => 'fa-building-shield', 'tone' => 'warning'],
        ];

        $query = FoundItem::with('category')
            ->filtered($request->all())
            ->when($request->boolean('available_only'), fn($q) => $q->where('status', 'unclaimed'))
            ->when($request->sort === 'oldest', fn($q) => $q->oldest(), fn($q) => $q->latest());

        return view('student.browse.index', [
            'items' => $query->paginate(12)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'browseStats' => $browseStats,
            'watchedIds' => WatchedFoundItem::where('user_id', $request->user()->id)->pluck('found_item_id')->all(),
            'existingClaimFoundIds' => Claim::where('student_id', $request->user()->id)->where('status', 'pending')->pluck('found_item_id')->all(),
        ]);
    }
}
