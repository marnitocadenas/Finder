<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\TmcNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClaimController extends Controller
{
    use LogsActivity;

    /**
     * Determine the role and claims route prefix from the current route.
     */
    private function resolveContext(Request $request): array
    {
        $name = $request->route()->getName() ?? '';
        $role = str_starts_with($name, 'staff') ? 'staff' : 'student';
        // Staff personal claims use "my-claims" prefix; student claims use "claims"
        $claimRoute = $role === 'staff' ? 'staff.my-claims' : 'student.claims';

        return [$role, $claimRoute];
    }

    public function index(Request $request): View
    {
        [$role, $claimRoute] = $this->resolveContext($request);

        return view('claims.index', [
            'role' => $role,
            'claimRoute' => $claimRoute,
            'claims' => Claim::with(['foundItem.category', 'reviewer'])
                ->where('student_id', $request->user()->id)
                ->filtered($request->all())
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        [$role, $claimRoute] = $this->resolveContext($request);
        $existingClaim = null;

        if ($request->found_item_id) {
            $existingClaim = Claim::where('student_id', $request->user()->id)
                ->where('found_item_id', $request->found_item_id)
                ->where('status', 'pending')
                ->first();
        }

        return view('claims.create', [
            'role' => $role,
            'claimRoute' => $claimRoute,
            'foundItems' => FoundItem::with('category')->where('status', 'unclaimed')->orderBy('title')->get(),
            'lostItems' => LostItem::where('user_id', $request->user()->id)->orderBy('title')->get(),
            'selectedFoundItem' => $request->found_item_id,
            'existingClaim' => $existingClaim,
            'proofRequired' => AdminSetting::value('claim_proof_required', '1') === '1',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$role, $claimRoute] = $this->resolveContext($request);
        $proofRule = AdminSetting::value('claim_proof_required', '1') === '1' ? 'required' : 'nullable';

        $data = $request->validate([
            'found_item_id' => ['required', Rule::exists('found_items', 'id')->where('status', 'unclaimed')],
            'lost_item_id' => 'nullable|exists:lost_items,id',
            'claim_description' => 'required|string|min:30|max:1000',
            'proof_image' => $proofRule . '|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $duplicate = Claim::where('student_id', $request->user()->id)
            ->where('found_item_id', $data['found_item_id'])
            ->where('status', 'pending')
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['found_item_id' => 'You already have a pending claim for this item.'])->withInput();
        }

        if ($request->hasFile('proof_image')) {
            $data['proof_image'] = $request->file('proof_image')->store('claim-proofs', 'public');
        }

        $claim = Claim::create($data + ['student_id' => $request->user()->id, 'status' => 'pending']);

        // Notify the staff member who owns the found item
        TmcNotification::create([
            'user_id' => $claim->foundItem->staff_id,
            'title' => 'New claim request',
            'message' => $request->user()->name . ' filed a claim for ' . $claim->foundItem->title . '.',
            'type' => 'claim_update',
            'link' => route('staff.claims.show', $claim),
        ]);

        // Bulk notify all admins using a single insert instead of N+1 creates
        $adminIds = User::where('role', 'admin')->pluck('id');
        if ($adminIds->isNotEmpty()) {
            $now = now();
            $notifications = $adminIds->map(fn($id) => [
                'user_id' => $id,
                'title' => 'New claim request',
                'message' => $request->user()->name . ' filed a claim for ' . $claim->foundItem->title . '.',
                'type' => 'claim_update',
                'link' => route('admin.claims.show', $claim),
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray();

            TmcNotification::insert($notifications);
        }

        $this->logAction($request, 'Filed claim #' . $claim->id, $claim);

        return redirect()->route($claimRoute . '.index')->with('success', 'Claim submitted. Staff will review it.');
    }

    public function show(Request $request, Claim $claim): View
    {
        abort_if($claim->student_id !== auth()->id(), 403);
        [$role, $claimRoute] = $this->resolveContext($request);

        return view('claims.show', [
            'role' => $role,
            'claimRoute' => $claimRoute,
            'claim' => $claim->load(['student', 'foundItem.category', 'lostItem', 'reviewer', 'releaser']),
        ]);
    }

    public function destroy(Request $request, Claim $claim): RedirectResponse
    {
        abort_if($claim->student_id !== auth()->id(), 403);
        abort_unless(in_array($claim->status, ['pending', 'rejected']), 422, 'You can only delete pending or rejected claims.');
        [$role, $claimRoute] = $this->resolveContext($request);

        $claim->delete();

        $this->logAction($request, 'Deleted claim #' . $claim->id, $claim);

        return redirect()->route($claimRoute . '.index')->with('success', 'Claim deleted successfully.');
    }
}
