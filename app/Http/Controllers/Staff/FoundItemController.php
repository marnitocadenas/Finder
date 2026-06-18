<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\TmcNotification;
use App\Support\SmartMatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FoundItemController extends Controller
{
    use LogsActivity;

    public function index(Request $request): View
    {
        return view('items.found-index', [
            'role' => 'staff',
            'items' => FoundItem::with('category')
                ->where('staff_id', $request->user()->id)
                ->filtered($request->all())
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('items.found-form', [
            'role' => 'staff',
            'item' => new FoundItem(['date_found' => now()]),
            'categories' => Category::orderBy('name')->get(),
            'action' => route('staff.found-items.store'),
            'method' => 'POST',
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('found-items', 'public');
        }

        $item = FoundItem::create($data + [
            'staff_id' => $request->user()->id,
            'status' => 'unclaimed',
        ]);

        // Use SmartMatcher to find real matches and notify the owners
        $lostItems = LostItem::with('category')
            ->where('category_id', $item->category_id)
            ->where('status', 'lost')
            ->get();

        $notifiedUserIds = collect();
        $now = now();
        $notifications = [];

        foreach ($lostItems as $lost) {
            if ($notifiedUserIds->contains($lost->user_id)) {
                continue;
            }

            $score = SmartMatcher::score($lost, $item);
            if ($score >= SmartMatcher::THRESHOLD) {
                $notifications[] = [
                    'user_id' => $lost->user_id,
                    'title' => 'Possible match found',
                    'message' => 'A found item may match your lost report: ' . $item->title,
                    'type' => 'match_alert',
                    'link' => route('student.matches'),
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $notifiedUserIds->push($lost->user_id);
            }
        }

        if (! empty($notifications)) {
            TmcNotification::insert($notifications);
        }

        $this->logAction($request, 'Posted found item ' . $item->title, $item);

        $count = count($notifications);

        return redirect()->route('staff.found-items.index')->with(
            'success',
            'Found item posted.' . ($count > 0 ? " {$count} student(s) notified of possible matches." : '')
        );
    }

    public function show(FoundItem $foundItem): View
    {
        $this->authorizeOwner($foundItem);

        return view('items.found-show', [
            'item' => $foundItem->load(['staff', 'category']),
            'role' => 'staff',
        ]);
    }

    public function edit(FoundItem $foundItem): View
    {
        $this->authorizeOwner($foundItem);

        return view('items.found-form', [
            'role' => 'staff',
            'item' => $foundItem,
            'categories' => Category::orderBy('name')->get(),
            'action' => route('staff.found-items.update', $foundItem),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $this->authorizeOwner($foundItem);

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

        return redirect()->route('staff.found-items.index')->with('success', 'Found item updated.');
    }

    public function status(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $this->authorizeOwner($foundItem);

        $data = $request->validate([
            'status' => ['required', Rule::in(['unclaimed', 'claimed', 'turned_over'])],
        ]);

        $foundItem->update($data);
        $this->logAction($request, 'Marked found item ' . $foundItem->title . ' as ' . $data['status'], $foundItem);

        return back()->with('success', 'Found item status updated.');
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
