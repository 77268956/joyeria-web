<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * Validates and remembers supervisor PIN authorizations.
 *
 * A validated PIN unlocks sensitive actions for a short period. The
 * authorization is stored in the session so every request can be checked
 * server-side without exposing the PIN to the browser again.
 */
class SupervisorAuthorizationService
{
    public const SESSION_KEY = 'supervisor_authorization';

    /**
     * Validate a supervisor PIN and return the authorizing user.
     *
     * @throws InvalidArgumentException when the PIN is empty or does not match an active supervisor
     */
    public function authorizeByPin(string $pin, User $actor): User
    {
        $pin = trim($pin);

        if ($pin === '') {
            throw new InvalidArgumentException('Ingresa el PIN de supervisor.');
        }

        $query = User::query()
            ->where('is_active', true)
            ->whereNotNull('supervisor_pin');

        if ($actor->store_id !== null) {
            $query->where(function ($query) use ($actor): void {
                $query->where('store_id', $actor->store_id)
                    ->orWhereHas('roles', fn ($roles) => $roles->where('name', 'Dueño'));
            });
        }

        foreach ($query->get() as $supervisor) {
            if (Hash::check($pin, $supervisor->supervisor_pin)) {
                return $supervisor;
            }
        }

        throw new InvalidArgumentException('PIN incorrecto o el supervisor no está activo.');
    }

    /**
     * Remember the validated supervisor for the configured session window.
     */
    public function startSession(User $supervisor, int $minutes = 10): Carbon
    {
        $expiresAt = now()->addMinutes(max(1, $minutes));

        session([
            self::SESSION_KEY => [
                'user_id' => $supervisor->id,
                'name' => $supervisor->name,
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ]);

        return $expiresAt;
    }

    /**
     * Return the currently authorized supervisor, if the session is still valid.
     */
    public function currentSupervisor(): ?User
    {
        $data = session(self::SESSION_KEY);

        if (! is_array($data) || empty($data['user_id']) || empty($data['expires_at'])) {
            return null;
        }

        if (now()->greaterThan(Carbon::parse($data['expires_at']))) {
            $this->forget();

            return null;
        }

        $supervisor = User::query()
            ->whereKey($data['user_id'])
            ->where('is_active', true)
            ->first();

        if ($supervisor === null) {
            $this->forget();

            return null;
        }

        return $supervisor;
    }

    /**
     * Determine whether a supervisor is currently authorized.
     */
    public function isAuthorized(): bool
    {
        return $this->currentSupervisor() !== null;
    }

    /**
     * Clear the current supervisor authorization.
     */
    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
