<?php
namespace App\Http\Controllers;
use App\Models\Claim;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function reportLostForm(): View
    {
        return view('public.report-lost', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function reportLost(Request $request)
    {
        $data = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_contact' => 'required|string|max:255',
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'date_lost' => 'required|date',
            'location_lost' => 'required|string|max:255',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('lost-items', 'public');
        }

        LostItem::create([
            'user_id' => null,
            'guest_name' => $data['guest_name'],
            'guest_contact' => $data['guest_contact'],
            'title' => $data['title'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'date_lost' => $data['date_lost'],
            'location_lost' => $data['location_lost'],
            'image' => $data['image'] ?? null,
            'status' => 'lost',
        ]);

        return view('public.report-success', [
            'type' => 'lost',
            'contact' => $data['guest_contact'],
        ]);
    }

    public function reportFoundForm(): View
    {
        return view('public.report-found', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function reportFound(Request $request)
    {
        $data = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_contact' => 'required|string|max:255',
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'date_found' => 'required|date',
            'location_found' => 'required|string|max:255',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('found-items', 'public');
        }

        FoundItem::create([
            'staff_id' => null,
            'guest_name' => $data['guest_name'],
            'guest_contact' => $data['guest_contact'],
            'title' => $data['title'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'date_found' => $data['date_found'],
            'location_found' => $data['location_found'],
            'image' => $data['image'] ?? null,
            'status' => 'unclaimed',
        ]);

        return view('public.report-success', [
            'type' => 'found',
            'contact' => $data['guest_contact'],
        ]);
    }
}
