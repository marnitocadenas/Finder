<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $settings = [
            'pickup_location' => AdminSetting::value('pickup_location', 'Student Affairs Office'),
            'auto_close_days' => AdminSetting::value('auto_close_days', '30'),
            'claim_proof_required' => AdminSetting::value('claim_proof_required', '1'),
            'contact_email' => AdminSetting::value('contact_email', config('mail.from.address')),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pickup_location' => 'required|string|max:120',
            'auto_close_days' => 'required|integer|min:1|max:365',
            'claim_proof_required' => 'nullable|boolean',
            'contact_email' => 'required|email|max:120',
        ]);

        $data['claim_proof_required'] = $request->boolean('claim_proof_required') ? '1' : '0';

        foreach ($data as $key => $value) {
            AdminSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        $this->logAction($request, 'Updated admin settings');

        return back()->with('success', 'Admin settings updated.');
    }
}
