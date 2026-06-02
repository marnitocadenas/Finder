<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q'));

        $users = collect();
        $lostItems = collect();
        $foundItems = collect();
        $claims = collect();

        if ($term !== '') {
            $users = User::where(fn($query) => $query->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                ->latest()
                ->take(8)
                ->get();
            $lostItems = LostItem::with('category')
                ->where(fn($query) => $query->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")->orWhere('location_lost', 'like', "%{$term}%"))
                ->latest()
                ->take(8)
                ->get();
            $foundItems = FoundItem::with('category')
                ->where(fn($query) => $query->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")->orWhere('location_found', 'like', "%{$term}%"))
                ->latest()
                ->take(8)
                ->get();
            $claims = Claim::with(['student', 'foundItem'])
                ->where('claim_description', 'like', "%{$term}%")
                ->orWhereHas('student', fn($query) => $query->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                ->orWhereHas('foundItem', fn($query) => $query->where('title', 'like', "%{$term}%"))
                ->latest()
                ->take(8)
                ->get();
        }

        return view('admin.search.index', compact('term', 'users', 'lostItems', 'foundItems', 'claims'));
    }
}
