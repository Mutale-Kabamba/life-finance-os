<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * @var array<string, string>
     */
    private array $providerIdColumns = [
        'google' => 'google_id',
        'facebook' => 'facebook_id',
        'x' => 'twitter_id',
        'linkedin-openid' => 'linkedin_id',
        'github' => 'github_id',
    ];

    /**
     * @var array<string, string>
     */
    private array $socialiteDriverMap = [
        'google' => 'google',
        'facebook' => 'facebook',
        'x' => 'x',
        'linkedin-openid' => 'linkedin-openid',
        'github' => 'github',
    ];

    /**
     * Redirect the user to the selected provider's authentication page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $driver = $this->resolveDriver($provider);

        if ($provider === 'facebook') {
            return Socialite::driver($driver)
                ->setScopes(['public_profile'])
                ->redirect();
        }

        if ($provider === 'google') {
            $googleDriver = Socialite::driver($driver);

            if (method_exists($googleDriver, 'scopes')) {
                $googleDriver = $googleDriver->scopes(config('services.google.scopes', []));
            } elseif (method_exists($googleDriver, 'setScopes')) {
                $googleDriver = $googleDriver->setScopes(config('services.google.scopes', []));
            }

            if (method_exists($googleDriver, 'with')) {
                $googleDriver = $googleDriver->with([
                    'access_type' => 'offline',
                    'prompt' => 'consent',
                    'include_granted_scopes' => 'true',
                ]);
            }

            return $googleDriver->redirect();
        }

        return Socialite::driver($driver)->redirect();
    }

    /**
     * Handle callback from OAuth provider authentication.
     *
     * @throws ValidationException
     */
    public function callback(string $provider): RedirectResponse
    {
        $driver = $this->resolveDriver($provider);

        if ($provider === 'google') {
            $googleDriver = Socialite::driver($driver);

            if (method_exists($googleDriver, 'scopes')) {
                $googleDriver = $googleDriver->scopes(config('services.google.scopes', []));
            } elseif (method_exists($googleDriver, 'setScopes')) {
                $googleDriver = $googleDriver->setScopes(config('services.google.scopes', []));
            }

            $socialUser = $googleDriver->stateless()->user();
        } else {
            $socialUser = Socialite::driver($driver)->stateless()->user();
        }

        $providerUserId = (string) $socialUser->getId();
        $email = $socialUser->getEmail();

        if (blank($email)) {
            // Some providers (notably Facebook in local/dev setups) may not return email.
            $email = $provider . '_' . $providerUserId . '@oauth.local';
        }

        $providerColumn = $this->providerIdColumns[$provider];

        $user = User::query()
            ->where($providerColumn, $providerUserId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $payload = [
                'name' => $socialUser->getName() ?: $user->name,
                $providerColumn => $providerUserId,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ];

            if ($provider === 'google') {
                $payload = array_merge($payload, $this->googleTokenPayload($socialUser, $user));
            }

            $user->forceFill($payload)->save();
        } else {
            $payload = [
                'name' => $socialUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                $providerColumn => $providerUserId,
                'password' => Str::random(64),
            ];

            if ($provider === 'google') {
                $payload = array_merge($payload, $this->googleTokenPayload($socialUser, null));
            }

            $user = User::create($payload);

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function resolveDriver(string $provider): string
    {
        if (! array_key_exists($provider, $this->socialiteDriverMap)) {
            abort(404);
        }

        return $this->socialiteDriverMap[$provider];
    }

    /**
     * @return array<string, mixed>
     */
    private function googleTokenPayload(object $socialUser, ?User $existingUser): array
    {
        $token = $socialUser->token ?? null;
        $refreshToken = $socialUser->refreshToken ?? null;
        $expiresIn = $socialUser->expiresIn ?? null;

        return [
            'google_access_token' => $token,
            'google_refresh_token' => $refreshToken ?: $existingUser?->google_refresh_token,
            'google_token_expires_at' => is_numeric($expiresIn) ? now()->addSeconds((int) $expiresIn) : null,
            'google_calendar_connected_at' => now(),
        ];
    }
}
