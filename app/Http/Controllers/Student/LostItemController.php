<?php

namespace App\Http\Controllers\Student;

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

    /**
     * Determine the role from the current route name prefix.
     */
    private function resolveRole(Request $request): string
    {
        $name = $request->route()->getName() ?? '';

        return str_starts_with($name, 'staff') ? 'staff' : 'student';
    }

    public function index(Request $request): View
    {
        $role = $this->resolveRole($request);

        return view('items.lost-index', [
            'role' => $role,
            'personalView' => true,
            'items' => LostItem::with('category')
                ->where('user_id', $request->user()->id)
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

        return view('items.lost-form', [
            'role' => $role,
            'item' => new LostItem(['date_lost' => now()]),
            'categories' => Category::orderBy('name')->get(),
            'action' => route($role . '.lost-items.store'),
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
            'date_lost' => 'required|date|before_or_equal:today',
            'location_lost' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('lost-items', 'public');
        }

        $item = LostItem::create($data + [
            'user_id' => $request->user()->id,
            'status' => 'lost',
        ]);

        $this->logAction($request, 'Reported lost item ' . $item->title, $item);

        return redirect()
            ->route($role . '.lost-items.index')
            ->with('success', 'Lost item reported.');
    }

    public function show(Request $request, LostItem $lostItem): View
    {
        $this->authorizeOwner($lostItem);
        $role = $this->resolveRole($request);

        return view('items.lost-show', [
            'item' => $lostItem->load(['user', 'category']),
            'role' => $role,
        ]);
    }

    public function edit(Request $request, LostItem $lostItem): View
    {
        $this->authorizeOwner($lostItem);
        $role = $this->resolveRole($request);

        return view('items.lost-form', [
            'role' => $role,
            'item' => $lostItem,
            'categories' => Category::orderBy('name')->get(),
            'action' => route($role . '.lost-items.update', $lostItem),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, LostItem $lostItem): RedirectResponse
    {
        $this->authorizeOwner($lostItem);
        $role = $this->resolveRole($request);

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

        $this->logAction($request, 'Updated lost item ' . $lostItem->title, $lostItem);

        return redirect()
            ->route($role . '.lost-items.index')
            ->with('success', 'Lost report updated.');
    }

    public function status(Request $request, LostItem $lostItem): RedirectResponse
    {
        $this->authorizeOwner($lostItem);

        $data = $request->validate([
            'status' => ['required', Rule::in(['lost', 'found', 'closed'])],
        ]);

        $lostItem->update($data);

        $this->logAction($request, 'Marked lost report ' . $lostItem->title . ' as ' . $data['status'], $lostItem);

        return back()->with('success', 'Lost report status updated.');
    }

    public function destroy(Request $request, LostItem $lostItem): RedirectResponse
    {
        $this->authorizeOwner($lostItem);

        $this->logAction($request, 'Deleted lost item ' . $lostItem->title, $lostItem);

        $lostItem->delete();

        return back()->with('success', 'Lost report deleted.');
    }

    private function authorizeOwner(LostItem $item): void
    {
        abort_if($item->user_id !== auth()->id(), 403);
    }
}
