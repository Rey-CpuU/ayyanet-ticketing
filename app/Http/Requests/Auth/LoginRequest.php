<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public const MAX_ATTEMPTS = 5;

    /**
     * Throttle window in seconds; also the minimum gap between two lockout alert emails.
     */
    public const DECAY_SECONDS = 60;

    public const DEACTIVATED_MESSAGE = 'Akun Anda telah dinonaktifkan. Hubungi admin untuk mengaktifkannya kembali.';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Emails are stored lowercase, so sign-in is case-insensitive.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => User::normalizeEmail($this->input('email'))]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // The callback only runs once the password is verified, so the "deactivated" message is
        // never shown for a wrong password.
        $deactivated = false;
        $authenticated = Auth::attemptWhen(
            $this->only('email', 'password'),
            function (User $user) use (&$deactivated) {
                $deactivated = $user->isDeactivated();

                return ! $deactivated;
            },
            $this->boolean('remember'),
        );

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => $deactivated ? self::DEACTIVATED_MESSAGE : 'Email atau kata sandi salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        // Fired on every throttled attempt; SendLockoutAlert mails at most once per window.
        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
