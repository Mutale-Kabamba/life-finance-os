<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        if ($response = $this->ensureProviderConfigured($provider)) {
            return $response;
        }

        if ($provider === 'facebook') {
            $facebookDriver = Socialite::driver($driver);

            $facebookScopes = config('services.facebook.scopes', ['email', 'public_profile']);

            if (method_exists($facebookDriver, 'scopes')) {
                $facebookDriver = $facebookDriver->scopes($facebookScopes);
            } elseif (method_exists($facebookDriver, 'setScopes')) {
                $facebookDriver = $facebookDriver->setScopes($facebookScopes);
            }

            if (method_exists($facebookDriver, 'fields')) {
                $facebookDriver = $facebookDriver->fields(config('services.facebook.fields', ['name', 'email']));
            }

            return $facebookDriver->redirect();
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

        if ($response = $this->ensureProviderConfigured($provider)) {
            return $response;
        }

        if ($provider === 'google') {
            $googleDriver = Socialite::driver($driver);

            if (method_exists($googleDriver, 'scopes')) {
                $googleDriver = $googleDriver->scopes(config('services.google.scopes', []));
            } elseif (method_exists($googleDriver, 'setScopes')) {
                $googleDriver = $googleDriver->setScopes(config('services.google.scopes', []));
            }

            $socialUser = $googleDriver->stateless()->user();
        } elseif ($provider === 'facebook') {
            $facebookDriver = Socialite::driver($driver);

            $facebookScopes = config('services.facebook.scopes', ['email', 'public_profile']);

            if (method_exists($facebookDriver, 'scopes')) {
                $facebookDriver = $facebookDriver->scopes($facebookScopes);
            } elseif (method_exists($facebookDriver, 'setScopes')) {
                $facebookDriver = $facebookDriver->setScopes($facebookScopes);
            }

            if (method_exists($facebookDriver, 'fields')) {
                $facebookDriver = $facebookDriver->fields(config('services.facebook.fields', ['name', 'email']));
            }

            $socialUser = $facebookDriver->stateless()->user();
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

    private function ensureProviderConfigured(string $provider): ?RedirectResponse
    {
        $requirements = [
            'google' => ['GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'GOOGLE_REDIRECT_URI'],
            'facebook' => ['FACEBOOK_CLIENT_ID', 'FACEBOOK_CLIENT_SECRET', 'FACEBOOK_REDIRECT_URI'],
            'x' => ['X_CLIENT_ID', 'X_CLIENT_SECRET', 'X_REDIRECT_URI'],
            'linkedin-openid' => ['LINKEDIN_CLIENT_ID', 'LINKEDIN_CLIENT_SECRET', 'LINKEDIN_REDIRECT_URI'],
            'github' => ['GITHUB_CLIENT_ID', 'GITHUB_CLIENT_SECRET', 'GITHUB_REDIRECT_URI'],
        ];

        $serviceConfig = [
            'google' => [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect' => config('services.google.redirect'),
            ],
            'facebook' => [
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'redirect' => config('services.facebook.redirect'),
            ],
            'x' => [
                'client_id' => config('services.x.client_id'),
                'client_secret' => config('services.x.client_secret'),
                'redirect' => config('services.x.redirect'),
            ],
            'linkedin-openid' => [
                'client_id' => config('services.linkedin-openid.client_id'),
                'client_secret' => config('services.linkedin-openid.client_secret'),
                'redirect' => config('services.linkedin-openid.redirect'),
            ],
            'github' => [
                'client_id' => config('services.github.client_id'),
                'client_secret' => config('services.github.client_secret'),
                'redirect' => config('services.github.redirect'),
            ],
        ];

        $values = $serviceConfig[$provider] ?? [];
        $missing = [];

        foreach ($values as $key => $value) {
            if (blank($value)) {
                $missing[] = $key;
            }
        }

        if ($missing === []) {
            return null;
        }

        Log::warning('OAuth provider is not configured', [
            'provider' => $provider,
            'missing_config_keys' => $missing,
            'required_env_vars' => $requirements[$provider] ?? [],
        ]);

        return redirect()
            ->route('login')
            ->withErrors([
                'oauth' => 'Login with '.strtoupper($provider).' is temporarily unavailable. Missing configuration: '.implode(', ', $requirements[$provider] ?? []).'.',
            ]);
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
