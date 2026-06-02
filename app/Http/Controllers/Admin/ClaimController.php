<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\TmcNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClaimController extends Controller
{
    use LogsActivity;

    public function index(Request $request): View
    {
        $claimStats = [
            ['label' => 'Total Claims', 'value' => Claim::count(), 'icon' => 'fa-file-signature', 'tone' => 'primary'],
            ['label' => 'Pending', 'value' => Claim::where('status', 'pending')->count(), 'icon' => 'fa-hourglass-half', 'tone' => 'warning'],
            ['label' => 'Approved', 'value' => Claim::where('status', 'approved')->count(), 'icon' => 'fa-circle-check', 'tone' => 'success'],
            ['label' => 'Rejected', 'value' => Claim::where('status', 'rejected')->count(), 'icon' => 'fa-circle-xmark', 'tone' => 'danger'],
        ];

        return view('claims.index', [
            'role' => 'admin',
            'claims' => Claim::with(['student', 'foundItem.category'])->filtered($request->all())->latest()->paginate(15)->withQueryString(),
            'claimStats' => $claimStats,
        ]);
    }

    public function show(Claim $claim): View
    {
        return view('claims.show', ['role' => 'admin', 'claim' => $claim->load(['student', 'foundItem.category', 'lostItem', 'reviewer', 'releaser'])]);
    }

    public function update(Request $request, Claim $claim): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => 'nullable|string|max:1000',
            'pickup_instruction' => 'nullable|string|max:1000',
        ]);

        $claim->update($data + ['reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        if ($data['status'] === 'approved') {
            $claim->foundItem->update(['status' => 'claimed']);
        }

        TmcNotification::create([
            'user_id' => $claim->student_id,
            'title' => 'Claim '.$data['status'],
            'message' => 'Your claim for '.$claim->foundItem->title.' was '.$data['status'].'.',
            'type' => 'claim_update',
            'link' => route('student.claims.show', $claim),
        ]);

        $this->logAction($request, ucfirst($data['status']).' claim #'.$claim->id, $claim);

        return back()->with('success', 'Claim reviewed.');
    }

    public function release(Request $request, Claim $claim): RedirectResponse
    {
        abort_unless($claim->status === 'approved', 422, 'Only approved claims can be released.');

        $claim->update([
            'released_by' => $request->user()->id,
            'released_at' => now(),
        ]);
        $claim->foundItem->update(['status' => 'turned_over']);

        TmcNotification::create([
            'user_id' => $claim->student_id,
            'title' => 'Item released',
            'message' => 'Your claimed item '.$claim->foundItem->title.' has been marked as released.',
            'type' => 'claim_update',
            'link' => route('student.claims.show', $claim),
        ]);

        $this->logAction($request, 'Released claim #'.$claim->id, $claim);

        return back()->with('success', 'Claim marked as released.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'action' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => 'nullable|string|max:1000',
        ]);

        $claims = Claim::with('foundItem')->whereIn('id', $data['ids'])->where('status', 'pending')->get();

        foreach ($claims as $claim) {
            $claim->update([
                'status' => $data['action'],
                'review_note' => $data['review_note'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            if ($data['action'] === 'approved') {
                $claim->foundItem?->update(['status' => 'claimed']);
            }
        }

        $this->logAction($request, 'Bulk '.$data['action'].' on '.$claims->count().' claims');

        return back()->with('success', $claims->count().' pending claims updated.');
    }
}
