<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CommercialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * Credentials are scoped to the `commercial` role so that an admin
     * or developer sharing the same name can never sign in here.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $authenticated = Auth::attempt([
            'name' => $this->string('name')->toString(),
            'password' => $this->string('password')->toString(),
            'role' => 'commercial',
        ], true);

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'name' => [trans('auth.failed')],
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'name' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('name')).'|'.$this->ip());
    }
}
