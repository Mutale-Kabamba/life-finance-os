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
        $socialUser = Socialite::driver($driver)->stateless()->user();
        $email = $socialUser->getEmail();

        if (blank($email)) {
            throw ValidationException::withMessages([
                'email' => __(ucfirst($provider) . ' account did not provide an email address.'),
            ]);
        }

        $providerColumn = $this->providerIdColumns[$provider];
        $providerUserId = $socialUser->getId();

        $user = User::query()
            ->where($providerColumn, $providerUserId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->forceFill([
                'name' => $socialUser->getName() ?: $user->name,
                $providerColumn => $providerUserId,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'name' => $socialUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                $providerColumn => $providerUserId,
                'password' => Str::random(64),
            ]);

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
}
