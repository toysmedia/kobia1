<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    public function showLoginForm(): View
    {
        return view('customer.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    public function showRegistrationForm(): View
    {
        return view('customer.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255'],
            'phone'                 => ['required', 'string', 'min:9', 'max:15'],
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        // Block obvious test/dummy data patterns
        $testPatterns = ['/test/i', '/dummy/i', '/example\.com$/i', '/fake/i'];
        foreach ($testPatterns as $pattern) {
            if (preg_match($pattern, $data['email']) || preg_match($pattern, $data['name'])) {
                return back()->withErrors(['email' => 'Please use a valid name and email address.'])->withInput();
            }
        }

        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['name']));
        $username = $baseUsername;
        $counter = 1;
        while (Subscriber::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
        }

        $subscriber = Subscriber::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'],
            'username'      => $username,
            'password_hash' => Hash::make($data['password']),
            'status'        => 'inactive',
        ]);

        Auth::guard('customer')->login($subscriber);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')
            ->with('success', 'Account created! Please renew your subscription to get connected.');
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }

    // -------------------------------------------------------------------------
    // Password Reset (stubs — extend with PasswordBroker as needed)
    // -------------------------------------------------------------------------

    public function showForgotPasswordForm(): View
    {
        return view('customer.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        // TODO: integrate with PasswordBroker using 'customers' broker once configured
        return back()->with('status', 'If that email is registered, a reset link has been sent.');
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('customer.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        // TODO: integrate with PasswordBroker using 'customers' broker once configured
        return redirect()->route('customer.login')
            ->with('status', 'Password reset successfully. Please login.');
    }
}
