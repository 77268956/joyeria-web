<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Store;
use App\Models\User;
use App\Traits\BuildsActivityChanges;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use BuildsActivityChanges;

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $query = User::with(['roles', 'store'])->withTrashed();

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->role($role);
        }

        // Filter by status (active / inactive / trashed)
        if ($request->filled('status')) {
            if ($request->input('status') === 'trashed') {
                $query->onlyTrashed();
            } elseif ($request->input('status') === 'inactive') {
                $query->withoutTrashed()->where('is_active', false);
            } elseif ($request->input('status') === 'active') {
                $query->withoutTrashed()->where('is_active', true);
            }
        } else {
            $query->withoutTrashed();
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();
        $roles = Role::all();
        $stores = Store::where('is_active', true)->get();

        $summary = [
            'total' => User::withoutTrashed()->count(),
            'active' => User::withoutTrashed()->where('is_active', true)->count(),
            'inactive' => User::withoutTrashed()->where('is_active', false)->count(),
            'roles' => $roles->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'stores', 'summary'));
    }

    /**
     * Show the form to register a new user.
     */
    public function create(): View
    {
        return view('admin.users.create', $this->formData());
    }

    /**
     * Display the user's profile and access summary.
     */
    public function show(User $user): View
    {
        $user->load(['roles.permissions', 'store']);

        $recentActivity = Activity::query()
            ->where('subject_type', $user->getMorphClass())
            ->where('subject_id', $user->id)
            ->with('causer')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.users.show', [
            'user' => $user,
            'permissions' => $user->getAllPermissions()->sortBy('name')->values(),
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Show the form to edit a user.
     */
    public function edit(User $user): View
    {
        $user->load(['roles', 'store']);

        return view('admin.users.edit', [
            'user' => $user,
            ...$this->formData(),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $storeId = $validated['store_id'] ?? Auth::user()->store_id ?? Store::first()?->id;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'supervisor_pin' => ! empty($validated['supervisor_pin']) ? Hash::make($validated['supervisor_pin']) : null,
            'store_id' => $storeId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        $changes = $this->activityCreatedChanges($user);
        $changes['attributes']['role'] = $validated['role'];

        activity('usuarios')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->event('created')
            ->withChanges($changes)
            ->log("Usuario creado: {$user->name} con rol {$validated['role']}");

        return redirect()->route('admin.users.show', $user)
            ->with('status', "El usuario '{$user->name}' ha sido creado exitosamente.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $snapshot = $this->activitySnapshot($user);
        $previousRole = $user->getRoleNames()->first();

        // Prevent self-deactivation
        if ($user->id === Auth::id() && ! $request->boolean('is_active')) {
            return back()->withErrors(['error' => 'No puedes desactivar tu propia cuenta mientras estás en sesión activa.']);
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! empty($validated['store_id'])) {
            $data['store_id'] = $validated['store_id'];
        }

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if (! empty($validated['supervisor_pin'])) {
            $data['supervisor_pin'] = Hash::make($validated['supervisor_pin']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        $changes = $this->activityUpdatedChanges($user, $snapshot);

        if ($previousRole !== $validated['role']) {
            $changes['old']['role'] = $previousRole;
            $changes['attributes']['role'] = $validated['role'];
        }

        activity('usuarios')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->event('updated')
            ->withChanges($changes)
            ->log("Usuario actualizado: {$user->name}");

        return redirect()->route('admin.users.show', $user)
            ->with('status', "El usuario '{$user->name}' ha sido actualizado exitosamente.");
    }

    /**
     * Toggle the active status of the specified user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Por seguridad, no puedes cambiar el estado activo de tu propia cuenta.']);
        }

        $snapshot = $this->activitySnapshot($user);
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $statusLabel = $user->is_active ? 'activado' : 'desactivado';

        activity('usuarios')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->event($user->is_active ? 'activated' : 'deactivated')
            ->withChanges($this->activityUpdatedChanges($user, $snapshot))
            ->log("Usuario {$statusLabel}: {$user->name}");

        return redirect()->route('admin.users.index')
            ->with('status', "El usuario '{$user->name}' ha sido {$statusLabel} correctamente.");
    }

    /**
     * Shared data for the create and edit forms.
     *
     * @return array{roles: Collection<int, Role>, stores: Collection<int, Store>}
     */
    private function formData(): array
    {
        return [
            'roles' => Role::query()->orderBy('name')->get(),
            'stores' => Store::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    /**
     * Remove the specified user from storage (soft delete).
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Por seguridad, no puedes eliminar tu propia cuenta de usuario.']);
        }

        $name = $user->name;
        $snapshot = $this->activitySnapshot($user);
        $user->delete();

        activity('usuarios')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->event('deleted')
            ->withChanges($this->activityDeletedChanges($snapshot))
            ->log("Usuario eliminado: {$name}");

        return redirect()->route('admin.users.index')
            ->with('status', "El usuario '{$name}' ha sido eliminado exitosamente.");
    }
}
