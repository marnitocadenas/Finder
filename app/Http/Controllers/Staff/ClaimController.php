<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Admin\ClaimController as BaseClaimController;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\TmcNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClaimController extends BaseClaimController
{
    public function index(Request $request): View
    {
        $ids = FoundItem::where('staff_id', $request->user()->id)->pluck('id');

        return view('claims.index', [
            'role' => 'staff',
            'claims' => Claim::with(['student', 'foundItem.category'])
                ->whereIn('found_item_id', $ids)
                ->filtered($request->all())
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Claim $claim): View
    {
        abort_if($claim->foundItem?->staff_id !== auth()->id(), 403);

        return view('claims.show', [
            'role' => 'staff',
            'claim' => $claim->load(['student', 'foundItem.category', 'lostItem', 'reviewer', 'releaser']),
        ]);
    }

    public function update(Request $request, Claim $claim): RedirectResponse
    {
        abort_if($claim->foundItem?->staff_id !== $request->user()->id, 403);
        abort_if($claim->student_id === $request->user()->id, 403, 'You cannot review your own claims.');

        return parent::update($request, $claim);
    }

    public function release(Request $request, Claim $claim): RedirectResponse
    {
        abort_if($claim->foundItem?->staff_id !== $request->user()->id, 403);
        abort_if($claim->student_id === $request->user()->id, 403, 'You cannot review your own claims.');

        return parent::release($request, $claim);
    }

    public function destroy(Request $request, Claim $claim): RedirectResponse
    {
        abort_if($claim->foundItem?->staff_id !== $request->user()->id, 403);

        return parent::destroy($request, $claim);
    }

    public function requestInfo(Request $request, Claim $claim): RedirectResponse
    {
        abort_if($claim->foundItem?->staff_id !== $request->user()->id, 403);
        abort_if($claim->student_id === $request->user()->id, 403, 'You cannot review your own claims.');

        $data = $request->validate(['message' => 'required|string|max:1000']);

        $claim->update(['review_note' => $data['message']]);

        TmcNotification::create([
            'user_id' => $claim->student_id,
            'title' => 'More claim information needed',
            'message' => $data['message'],
            'type' => 'claim_update',
            'link' => route('student.claims.show', $claim),
        ]);

        $this->logAction($request, 'Requested more information for claim #'.$claim->id, $claim);

        return back()->with('success', 'Information request sent to the student.');
    }
}
