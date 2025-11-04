<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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

    public function register(UserRequest $request)
    {
        $user = $this->authService->register($request->validated());

        Auth::login($user);

        return redirect()
            ->route('home')
            ->with('success', 'Cadastro realizado com sucesso!');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        try {
            $this->authService->login($request->validated());
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        } catch (Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Logout realizado.');
    }
}
