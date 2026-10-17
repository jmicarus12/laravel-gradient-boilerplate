<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** Failed attempts allowed per email + IP before a 60-second lockout. */
    protected const MAX_ATTEMPTS = 5;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Try to log in on the given guard, with brute-force protection.
     *
     * @throws ValidationException
     */
    public function authenticate(string $guard): void
    {
        $this->ensureIsNotRateLimited();

        $auth = Auth::guard($guard);
        $credentials = $this->only('email', 'password');

        // Accounts with an "active" column (admins) can be deactivated. attemptWhen() only logs in
        // when the check passes; otherwise it counts as a failed login (Failed event).
        $isActive = fn ($account) => $account->getAttribute('active') !== false;

        if (! $auth->attemptWhen($credentials, $isActive, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Only say "deactivated" when the password was right, so this never reveals which emails exist.
            $account = $auth->getLastAttempted();
            $deactivated = $account && ! $isActive($account) && $auth->getProvider()->validateCredentials($account, $credentials);

            throw ValidationException::withMessages([
                'email' => $deactivated ? __('Your account has been deactivated.') : __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
                'minutes' => ceil(RateLimiter::availableIn($this->throttleKey()) / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
