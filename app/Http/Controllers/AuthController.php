<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'type' => 'in:Admin,Cliente'
        ]);

        $user = $this->authService->register($data);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Cadastro realizado com sucesso!');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            $this->authService->login($credentials);
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Login realizado com sucesso!');
        } catch (Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logout realizado.');
    }
}
