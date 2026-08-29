<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function index(Request $request)
    {
        // Strictly filter only customer accounts
        $query = User::withCount('orders')->whereIn('role', ['customer', 'user']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === '1');
        }

        $users = $query->latest()->paginate(20);

        if (filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN)) {
            $users->getCollection()->transform(function ($u) {
                $u->email = $this->maskEmail($u->email);
                $u->phone = $this->maskPhone($u->phone);
                return $u;
            });
        }

        $roles = UserRole::cases();

        $stats = [
            'total'     => User::count(),
            'customers' => User::whereIn('role', ['customer', 'user'])->count(),
            'sellers'   => User::whereIn('role', ['store_owner', 'store_manager', 'store_staff'])->count(),
            'drivers'   => User::where('role', 'delivery_partner')->count(),
            'admins'    => User::whereIn('role', ['super_admin', 'admin', 'manager', 'staff'])->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'stats'));
    }

    public function create()
    {
        $roles = UserRole::cases();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        if (filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->back()->with('error', 'User creation is disabled in Demo Mode.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        // Create wallet and apply signup bonus if applicable
        $this->walletService->createWallet($user);
        $this->walletService->applySignupBonus($user);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $user->load(['orders' => fn($q) => $q->latest()->limit(10), 'addresses', 'ownedStore']);

        if (filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN)) {
            $user->email = $this->maskEmail($user->email);
            $user->phone = $this->maskPhone($user->phone);
            if ($user->relationLoaded('addresses')) {
                foreach ($user->addresses as $address) {
                    if (isset($address->phone)) {
                        $address->phone = $this->maskPhone($address->phone);
                    }
                }
            }
        }

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = UserRole::cases();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        if (filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->back()->with('error', 'User modifications are disabled in Demo Mode.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        if (filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->back()->with('error', 'User deletion is disabled in Demo Mode.');
        }

        if ($user->role === UserRole::SUPER_ADMIN) {
            return back()->with('error', 'Cannot delete super admin');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted');
    }

    public function toggleStatus(User $user)
    {
        if (filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN)) {
            return response()->json([
                'success' => false,
                'message' => 'Toggling user status is disabled in Demo Mode.',
            ], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $user->is_active,
        ]);
    }

    protected function maskEmail(?string $email): string
    {
        if (empty($email)) {
            return '—';
        }
        $parts = explode('@', $email, 2);
        if (count($parts) < 2) {
            return '********';
        }
        $name = $parts[0];
        $domain = $parts[1];
        $visibleLen = min(2, strlen($name));
        $maskedName = substr($name, 0, $visibleLen) . str_repeat('*', max(strlen($name) - $visibleLen, 4));
        
        $domainParts = explode('.', $domain, 2);
        $domainName = $domainParts[0];
        $tld = $domainParts[1] ?? 'com';
        $maskedDomain = substr($domainName, 0, 1) . str_repeat('*', max(strlen($domainName) - 1, 3)) . '.' . $tld;

        return $maskedName . '@' . $maskedDomain;
    }

    protected function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '—';
        }
        $len = strlen($phone);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return substr($phone, 0, 2) . str_repeat('*', $len - 4) . substr($phone, -2);
    }
}
