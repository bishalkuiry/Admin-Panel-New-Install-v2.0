<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $path = $request->path();
        $portalTitle = 'Admin Portal';
        $portalSubtitle = 'Admin & Platform Management';
        $loginType = 'admin';

        if (str_contains($path, 'store') || str_contains($path, 'seller')) {
            $portalTitle = 'Store Panel Login';
            $portalSubtitle = 'Store Manager & Merchant Portal';
            $loginType = 'store';
        } elseif (str_contains($path, 'employee') || str_contains($path, 'staff')) {
            $portalTitle = 'Employee Portal Login';
            $portalSubtitle = 'Staff & Team Member Portal';
            $loginType = 'employee';
        }

        return view('auth.login', compact('portalTitle', 'portalSubtitle', 'loginType'));
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email.'])->withInput();
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.'])->withInput();
        }

        if (!$user->is_active) {
            return back()->with('error', 'Your account is inactive. Please contact support.')->withInput();
        }

        // Check if user can access admin/seller panel
        // Allow if admin, store role, or has dashboard permission
        if (!$user->isAdmin() && !$user->isStoreRole() && !$user->hasPermission('dashboard.view')) {
            return back()->with('error', 'You do not have permission to access this panel.')->withInput();
        }

        // Always false to force logout on browser close
        Auth::login($user, false);

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Redirect based on user role
     */
    protected function redirectBasedOnRole(User $user)
    {
        if ($user->isAdmin() || $user->hasPermission('dashboard.view')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isStoreRole()) {
            return redirect()->route('seller.dashboard');
        }

        if (\Illuminate\Support\Facades\Route::has('landing')) {
            return redirect()->route('landing');
        }
        return redirect()->route('admin.login');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (\Illuminate\Support\Facades\Route::has('landing')) {
            return redirect()->route('landing')->with('success', 'You have been logged out.');
        }
        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }
}
