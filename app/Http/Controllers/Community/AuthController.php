<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityUser;
use App\Rules\NotTestEmail;
use App\Rules\NotTestName;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('community.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('community')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::guard('community')->user();
            if ($user->is_banned) {
                Auth::guard('community')->logout();
                return back()->withErrors(['email' => 'Your account has been banned.']);
            }
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('community.index'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('community.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:100', new NotTestName],
            'email'    => ['required', 'email', 'unique:mysql.community_users,email', new NotTestEmail],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = CommunityUser::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::guard('community')->login($user);

        return redirect()->route('community.index')
            ->with('success', 'Welcome to the OxNet Community! Please verify your email.');
    }

    public function logout(Request $request)
    {
        Auth::guard('community')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('community.index');
    }

    public function showForgotPassword()
    {
        return view('community.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::broker('community_users')->sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('community.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('community_users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (CommunityUser $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('community.login')->with('success', 'Password reset successfully.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function verificationNotice()
    {
        return view('community.auth.verify-email');
    }
}
