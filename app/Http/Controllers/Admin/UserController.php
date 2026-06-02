<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthPasswordRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use LogsActivity;

    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->status === 'trashed', fn($q) => $q->onlyTrashed())
            ->when($request->status === 'all', fn($q) => $q->withTrashed())
            ->when($request->q, fn($q, $t) => $q->where(fn($i) => $i->where('name', 'like', "%$t%")->orWhere('email', 'like', "%$t%")))
            ->when($request->role, fn($q, $r) => $q->where('role', $r))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $userStats = [
            ['label' => 'Active Users', 'value' => User::count(), 'icon' => 'fa-user-check', 'tone' => 'success'],
            ['label' => 'Admins', 'value' => User::where('role', 'admin')->count(), 'icon' => 'fa-user-shield', 'tone' => 'primary'],
            ['label' => 'Staff', 'value' => User::where('role', 'staff')->count(), 'icon' => 'fa-id-badge', 'tone' => 'warning'],
            ['label' => 'Students', 'value' => User::where('role', 'student')->count(), 'icon' => 'fa-graduation-cap', 'tone' => 'danger'],
        ];

        return view('admin.users.index', compact('users', 'userStats'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['user' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'student_id' => 'nullable|string|max:20|unique:users,student_id',
            'role' => ['required', Rule::in(['admin', 'staff', 'student'])],
            'password' => AuthPasswordRules::rules(false),
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $this->logAction($request, 'Created user '.$user->email, $user);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'student_id' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'staff', 'student'])],
            'password' => AuthPasswordRules::optionalRules(),
        ]);

        if ($data['password'] ?? false) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $this->logAction($request, 'Updated user '.$user->email, $user);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'You cannot delete your own account.');
        $this->logAction($request, 'Deleted user '.$user->email, $user);
        $user->delete();

        return back()->with('success', 'User deleted.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        $this->logAction($request, 'Restored user '.$user->email, $user);

        return back()->with('success', 'User restored.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'action' => ['required', Rule::in(['delete', 'restore'])],
        ]);

        $ids = collect($data['ids'])->reject(fn($id) => (int) $id === $request->user()->id);
        $query = User::withTrashed()->whereIn('id', $ids);

        if ($data['action'] === 'restore') {
            $count = (clone $query)->onlyTrashed()->get()->each->restore()->count();
        } else {
            $count = (clone $query)->whereNull('deleted_at')->get()->each->delete()->count();
        }

        $this->logAction($request, 'Bulk '.$data['action'].' on '.$count.' users');

        return back()->with('success', $count.' users updated.');
    }
}
