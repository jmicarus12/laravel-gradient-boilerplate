<?php

namespace App\Http\Requests\Auth;

use App\Services\ClientService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ClientLoginRequest extends FormRequest
{
    /** Failed attempts allowed per IP before a 60-second lockout. */
    protected const MAX_ATTEMPTS = 5;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'view_code' => ['required', 'string'],
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

        // Clients have no password, so attempt() can't be used (it always checks one).
        // We find the client ourselves, then log them in directly.
        $client = app(ClientService::class)->findByViewCode($this->string('view_code')->trim());

        if (! $client) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'view_code' => __('Unknown view code, please check it or ask your administrator.'),
            ]);
        }

        Auth::guard($guard)->login($client);
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
            'view_code' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
                'minutes' => ceil(RateLimiter::availableIn($this->throttleKey()) / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        // IP only: with the code in the key, every guessed code would get a fresh counter.
        return 'client-login|'.$this->ip();
    }
}
