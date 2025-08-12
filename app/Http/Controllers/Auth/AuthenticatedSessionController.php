<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    // 1) Check email exists
    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return back()
            ->withErrors(['email' => 'This email address is not registered.'])
            ->onlyInput('email'); // keep typed email
    }

    // 2) Check password
    if (!Hash::check($request->password, $user->password)) {
        return back()
            ->withErrors(['password' => 'The password is incorrect.'])
            ->onlyInput('email'); // keep email filled
    }

    // 3) Login + remember
    Auth::login($user, $request->boolean('remember'));
    $request->session()->regenerate();

    // 4) Redirect by role
    $role = $user->role;
    if ($role === 'superadmin') {
        return redirect()->route('superadmin.pos.index');
    } elseif ($role === 'admin') {
        return redirect()->route('admin.pos.index');
    } elseif ($role === 'cashier') {
        return redirect()->route('cashier.pos.index');
    }

    return redirect()->intended('/');
}
    

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
