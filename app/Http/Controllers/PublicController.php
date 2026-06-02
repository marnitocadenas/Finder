<?php
namespace App\Http\Controllers;
use App\Models\Claim;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PublicController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();
        $foundItems = FoundItem::with('category')->filtered($request->only('q', 'category_id'))->latest()->take(8)->get();

        return view('public.home', [
            'foundItems' => $foundItems,
            'categories' => $categories,
            'landingStats' => [
                ['label' => 'Found Items Posted', 'value' => FoundItem::count(), 'icon' => 'fa-box-open'],
                ['label' => 'Lost Reports Submitted', 'value' => LostItem::count(), 'icon' => 'fa-clipboard-list'],
                ['label' => 'Claims Resolved', 'value' => Claim::whereNotNull('released_at')->count(), 'icon' => 'fa-handshake'],
                ['label' => 'Active Categories', 'value' => $categories->count(), 'icon' => 'fa-tags'],
            ],
        ]);
    }

    public function foundItem(FoundItem $foundItem): View
    {
        return view('public.found-show', ['item' => $foundItem->load(['category', 'staff'])]);
    }
}
