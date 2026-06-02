<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LostItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LostItemController extends Controller
{
    use LogsActivity;

    public function index(Request $request): View
    {
        $query = LostItem::with(['user', 'category'])
            ->when($request->deleted === 'trashed', fn($q) => $q->onlyTrashed())
            ->when($request->deleted === 'all', fn($q) => $q->withTrashed());

        $lostStats = [
            ['label' => 'Active Reports', 'value' => LostItem::count(), 'icon' => 'fa-magnifying-glass', 'tone' => 'primary'],
            ['label' => 'Still Lost', 'value' => LostItem::where('status', 'lost')->count(), 'icon' => 'fa-location-dot', 'tone' => 'danger'],
            ['label' => 'Found', 'value' => LostItem::where('status', 'found')->count(), 'icon' => 'fa-circle-check', 'tone' => 'success'],
            ['label' => 'Closed', 'value' => LostItem::where('status', 'closed')->count(), 'icon' => 'fa-folder-closed', 'tone' => 'warning'],
        ];

        return view('items.lost-index', [
            'role' => 'admin',
            'items' => $query->filtered($request->all())->latest()->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'lostStats' => $lostStats,
        ]);
    }

    public function show(LostItem $lostItem): View
    {
        return view('items.lost-show', ['item' => $lostItem->load(['user', 'category']), 'role' => 'admin']);
    }

    public function edit(LostItem $lostItem): View
    {
        return view('items.lost-form', [
            'item' => $lostItem,
            'categories' => Category::orderBy('name')->get(),
            'action' => route('admin.lost-items.update', $lostItem),
            'method' => 'PUT',
            'role' => 'admin',
        ]);
    }

    public function update(Request $request, LostItem $lostItem): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:1000',
            'date_lost' => 'required|date|before_or_equal:today',
            'location_lost' => 'required|string|max:255',
            'status' => ['required', Rule::in(['lost', 'found', 'closed'])],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('lost-items', 'public');
        } else {
            unset($data['image']);
        }

        $lostItem->update($data);
        $this->logAction($request, 'Updated lost item '.$lostItem->title, $lostItem);

        return redirect()->route('admin.lost-items.index')->with('success', 'Lost report updated.');
    }

    public function destroy(Request $request, LostItem $lostItem): RedirectResponse
    {
        $this->logAction($request, 'Deleted lost item '.$lostItem->title, $lostItem);
        $lostItem->delete();

        return back()->with('success', 'Lost report deleted.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $lostItem = LostItem::onlyTrashed()->findOrFail($id);
        $lostItem->restore();
        $this->logAction($request, 'Restored lost item '.$lostItem->title, $lostItem);

        return back()->with('success', 'Lost report restored.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'action' => ['required', Rule::in(['delete', 'restore', 'lost', 'found', 'closed'])],
        ]);

        $query = LostItem::withTrashed()->whereIn('id', $data['ids']);

        if ($data['action'] === 'delete') {
            $count = (clone $query)->whereNull('deleted_at')->get()->each->delete()->count();
        } elseif ($data['action'] === 'restore') {
            $count = (clone $query)->onlyTrashed()->get()->each->restore()->count();
        } else {
            $count = (clone $query)->whereNull('deleted_at')->update(['status' => $data['action']]);
        }

        $this->logAction($request, 'Bulk '.$data['action'].' on '.$count.' lost reports');

        return back()->with('success', $count.' lost reports updated.');
    }
}
