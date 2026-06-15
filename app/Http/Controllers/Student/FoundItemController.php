<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FoundItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FoundItemController extends Controller
{
    use LogsActivity;

    private function resolveRole(Request $request): string
    {
        $name = $request->route()->getName() ?? '';
        return str_starts_with($name, 'staff') ? 'staff' : 'student';
    }

    public function index(Request $request): View
    {
        $role = $this->resolveRole($request);

        return view('items.found-index', [
            'role' => $role,
            'personalView' => true,
            'items' => FoundItem::with('category')
                ->where('staff_id', $request->user()->id)
                ->filtered($request->all())
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $role = $this->resolveRole($request);

        return view('items.found-form', [
            'role' => $role,
            'item' => new FoundItem(['date_found' => now()]),
            'categories' => Category::orderBy('name')->get(),
            'action' => route($role . '.found-items.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $this->resolveRole($request);

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:1000',
            'date_found' => 'required|date|before_or_equal:today',
            'location_found' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('found-items', 'public');
        }

        $item = FoundItem::create($data + [
            'staff_id' => $request->user()->id,
            'status' => 'unclaimed',
        ]);

        $this->logAction($request, 'Posted found item ' . $item->title, $item);

        return redirect()
            ->route($role . '.found-items.index')
            ->with('success', 'Found item reported. Please bring the item to the campus front desk for verification.');
    }

    public function show(Request $request, FoundItem $foundItem): View
    {
        $this->authorizeOwner($foundItem);
        $role = $this->resolveRole($request);

        return view('items.found-show', [
            'item' => $foundItem->load(['staff', 'category']),
            'role' => $role,
        ]);
    }

    public function edit(Request $request, FoundItem $foundItem): View
    {
        $this->authorizeOwner($foundItem);
        $role = $this->resolveRole($request);

        return view('items.found-form', [
            'role' => $role,
            'item' => $foundItem,
            'categories' => Category::orderBy('name')->get(),
            'action' => route($role . '.found-items.update', $foundItem),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $this->authorizeOwner($foundItem);
        $role = $this->resolveRole($request);

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:1000',
            'date_found' => 'required|date|before_or_equal:today',
            'location_found' => 'required|string|max:255',
            'status' => ['required', Rule::in(['unclaimed', 'claimed', 'turned_over'])],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('found-items', 'public');
        } else {
            unset($data['image']);
        }

        $foundItem->update($data);
        $this->logAction($request, 'Updated found item ' . $foundItem->title, $foundItem);

        return redirect()
            ->route($role . '.found-items.index')
            ->with('success', 'Found item updated.');
    }

    public function destroy(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $this->authorizeOwner($foundItem);
        $this->logAction($request, 'Deleted found item ' . $foundItem->title, $foundItem);
        $foundItem->delete();

        return back()->with('success', 'Found item deleted.');
    }

    private function authorizeOwner(FoundItem $item): void
    {
        abort_if($item->staff_id !== auth()->id(), 403);
    }
}
