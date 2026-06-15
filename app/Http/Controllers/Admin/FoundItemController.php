<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FoundItemController extends Controller
{
    use LogsActivity;

    public function index(Request $request): View
    {
        $query = FoundItem::with(['staff', 'category'])
            ->when($request->deleted === 'trashed', fn($q) => $q->onlyTrashed())
            ->when($request->deleted === 'all', fn($q) => $q->withTrashed());

        $counts = FoundItem::selectRaw("
            COUNT(*) as total,
            SUM(status='unclaimed') as unclaimed,
            SUM(status='claimed') as claimed,
            SUM(status='turned_over') as turned_over
        ")->first();

        $foundStats = [
            ['label' => 'Active Items', 'value' => $counts->total ?? 0, 'icon' => 'fa-box-open', 'tone' => 'primary'],
            ['label' => 'Unclaimed', 'value' => (int) ($counts->unclaimed ?? 0), 'icon' => 'fa-inbox', 'tone' => 'warning'],
            ['label' => 'Claimed', 'value' => (int) ($counts->claimed ?? 0), 'icon' => 'fa-circle-check', 'tone' => 'success'],
            ['label' => 'Turned Over', 'value' => (int) ($counts->turned_over ?? 0), 'icon' => 'fa-building-columns', 'tone' => 'danger'],
        ];

        return view('items.found-index', [
            'role' => 'admin',
            'items' => $query->filtered($request->all())->latest()->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'foundStats' => $foundStats,
        ]);
    }

    public function create(): View
    {
        return view('items.found-form', [
            'item' => new FoundItem(),
            'categories' => Category::orderBy('name')->get(),
            'staffUsers' => User::where('role', 'staff')->orderBy('name')->get(),
            'action' => route('admin.found-items.store'),
            'method' => 'POST',
            'role' => 'admin',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:1000',
            'date_found' => 'required|date|before_or_equal:today',
            'location_found' => 'required|string|max:255',
            'status' => ['required', Rule::in(['unclaimed', 'claimed', 'turned_over'])],
            'staff_id' => 'nullable|exists:users,id',
            'guest_name' => 'nullable|string|max:255',
            'guest_contact' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('found-items', 'public');
        } else {
            unset($data['image']);
        }

        $foundItem = FoundItem::create($data);
        $this->logAction($request, 'Created found item '.$foundItem->title, $foundItem);

        return redirect()->route('admin.found-items.index')->with('success', 'Found item posted.');
    }

    public function show(FoundItem $foundItem): View
    {
        return view('items.found-show', ['item' => $foundItem->load(['staff', 'category']), 'role' => 'admin']);
    }

    public function edit(FoundItem $foundItem): View
    {
        return view('items.found-form', [
            'item' => $foundItem,
            'categories' => Category::orderBy('name')->get(),
            'staffUsers' => User::where('role', 'staff')->orderBy('name')->get(),
            'action' => route('admin.found-items.update', $foundItem),
            'method' => 'PUT',
            'role' => 'admin',
        ]);
    }

    public function update(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:1000',
            'date_found' => 'required|date|before_or_equal:today',
            'location_found' => 'required|string|max:255',
            'status' => ['required', Rule::in(['unclaimed', 'claimed', 'turned_over'])],
            'staff_id' => 'nullable|exists:users,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('found-items', 'public');
        }

        $foundItem->update($data);
        $this->logAction($request, 'Updated found item '.$foundItem->title, $foundItem);

        return redirect()->route('admin.found-items.index')->with('success', 'Found item updated.');
    }

    public function destroy(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $this->logAction($request, 'Deleted found item '.$foundItem->title, $foundItem);
        $foundItem->delete();

        return back()->with('success', 'Found item deleted.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $foundItem = FoundItem::onlyTrashed()->findOrFail($id);
        $foundItem->restore();
        $this->logAction($request, 'Restored found item '.$foundItem->title, $foundItem);

        return back()->with('success', 'Found item restored.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'action' => ['required', Rule::in(['delete', 'restore', 'unclaimed', 'claimed', 'turned_over'])],
        ]);

        $query = FoundItem::withTrashed()->whereIn('id', $data['ids']);

        if ($data['action'] === 'delete') {
            $count = (clone $query)->whereNull('deleted_at')->get()->each->delete()->count();
        } elseif ($data['action'] === 'restore') {
            $count = (clone $query)->onlyTrashed()->get()->each->restore()->count();
        } else {
            $count = (clone $query)->whereNull('deleted_at')->update(['status' => $data['action']]);
        }

        $this->logAction($request, 'Bulk '.$data['action'].' on '.$count.' found items');

        return back()->with('success', $count.' found items updated.');
    }
}
