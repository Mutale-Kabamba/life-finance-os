<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_users_can_be_created_on_callback(): void
    {
        $socialiteUser = $this->mockGoogleUser(
            id: 'google-123',
            name: 'Google User',
            email: 'google-user@example.com',
        );

        $provider = $this->mockSocialiteProvider($socialiteUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $user = User::query()->where('email', 'google-user@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_existing_users_are_linked_by_email_on_google_callback(): void
    {
        $user = User::factory()->unverified()->create([
            'name' => 'Local Account',
            'email' => 'linked@example.com',
            'google_id' => null,
        ]);

        $socialiteUser = $this->mockGoogleUser(
            id: 'google-linked',
            name: 'Linked Account',
            email: 'linked@example.com',
        );

        $provider = $this->mockSocialiteProvider($socialiteUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $user->refresh();

        $this->assertSame('google-linked', $user->google_id);
        $this->assertSame('Linked Account', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_google_redirect_route_redirects_to_provider(): void
    {
        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    private function mockGoogleUser(string $id, string $name, string $email): SocialiteUser
    {
        $socialiteUser = \Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($id);
        $socialiteUser->shouldReceive('getName')->andReturn($name);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);

        return $socialiteUser;
    }

    private function mockSocialiteProvider(SocialiteUser $socialiteUser): Provider
    {
        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        return $provider;
    }
}
