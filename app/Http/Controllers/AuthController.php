<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = $request->input('login');
        $field = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (! Auth::attempt([$field => $loginField, 'password' => $request->password], $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Username/email atau password salah'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function showForgotForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return back()->with('status', 'Email tidak ditemukan di sistem.');
        }

        $token = Password::broker()->createToken($user);
        $link = route('password.reset', $token) . '?email=' . urlencode($user->email);

        return back()->with('status', 'Reset password berhasil dibuat. Gunakan tautan ini: ' . $link);
    }

    public function showResetForm(string $token, Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login lagi.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
