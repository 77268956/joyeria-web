<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un formato de correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        // 1. Check rate limiting (max 5 attempts, locked for 60 seconds)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->logSecurityEvent('login.locked', $request, ['seconds' => $seconds]);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => "Demasiados intentos fallidos. Por favor, intenta de nuevo en {$seconds} segundos.",
                ]);
        }

        // 2. Check if user exists and is active
        $user = User::where('email', $request->input('email'))->first();

        if ($user && ! $user->is_active) {
            RateLimiter::hit($throttleKey, 60);

            $this->logSecurityEvent('login.inactive', $request, ['email' => $request->input('email')]);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Esta cuenta se encuentra desactivada. Por favor, comunícate con la administración.',
                ]);
        }

        // 3. Attempt authentication
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey, 60);

            $this->logSecurityEvent('login.failed', $request, ['email' => $request->input('email')]);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
                ]);
        }

        // 4. Successful login
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // Update last login timestamp
        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();
        $authenticatedUser->update([
            'last_login_at' => now(),
        ]);

        $this->logSecurityEvent('login.success', $request, ['user_id' => $authenticatedUser->id]);

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sesión cerrada exitosamente.');
    }

    /**
     * Write an authentication event to the dedicated security log.
     *
     * @param  array<string, mixed>  $context
     */
    protected function logSecurityEvent(string $event, Request $request, array $context = []): void
    {
        Log::channel('security')->info($event, array_merge([
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], $context));
    }
}
