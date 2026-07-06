<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\HtmlString;

class Login extends \Filament\Pages\Auth\Login
{
    /**
     * @return array<int, array{label: string, provider: string, color: string, icon_class: string}>
     */
    private function oauthProviders(): array
    {
        return [
            ['label' => 'Google', 'provider' => 'google', 'color' => '#EA4335', 'icon_class' => 'fa-google'],
            ['label' => 'Facebook', 'provider' => 'facebook', 'color' => '#1877F2', 'icon_class' => 'fa-facebook-f'],
            // ['label' => 'X', 'provider' => 'x', 'color' => '#111111', 'icon_class' => 'fa-x-twitter'],
            // ['label' => 'LinkedIn OpenID', 'provider' => 'linkedin-openid', 'color' => '#0A66C2', 'icon_class' => 'fa-linkedin-in'],
            // ['label' => 'GitHub', 'provider' => 'github', 'color' => '#24292E', 'icon_class' => 'fa-github'],
        ];
    }

    private function makeProviderAction(array $provider): Action
    {
        $iconBadge = '<span style="display:inline-flex;width:2rem;height:2rem;border-radius:9999px;align-items:center;justify-content:center;background:' . e($provider['color']) . ';color:#fff;"><i class="fa-brands ' . e($provider['icon_class']) . '" style="font-size:1.1rem;line-height:1;"></i></span>';

        return Action::make('oauth_' . $provider['provider'])
            ->label(new HtmlString($iconBadge))
            ->url(route('auth.provider.redirect', ['provider' => $provider['provider']]))
            ->color('gray')
            ->tooltip('Continue with ' . $provider['label'])
            ->extraAttributes([
                'title' => 'Continue with ' . $provider['label'],
                'aria-label' => 'Continue with ' . $provider['label'],
                'style' => 'width: 3.25rem; min-width: 3.25rem; justify-content: center; align-items: center; padding-inline: 0.5rem; border-color: #cfe0ff; background: #ffffff;',
            ]);
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->extraAttributes([
                'style' => 'flex: 1 0 100%; width: 100%; justify-content: center;',
            ]);
    }

    private function getOauthSeparatorAction(): Action
    {
        return Action::make('oauth_separator')
            ->label(new HtmlString('<span style="display:block;width:100%;text-align:center;font-weight:600;color:#64748b;">or Sign In With</span>'))
            ->disabled()
            ->extraAttributes([
                'style' => 'flex: 1 0 100%; width: 100%; justify-content: center; pointer-events: none; box-shadow: none; border-color: #e5e7eb; background: transparent;',
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        $oauthActions = array_map(fn (array $provider): Action => $this->makeProviderAction($provider), $this->oauthProviders());

        return [
            ...parent::getFormActions(),
            $this->getOauthSeparatorAction(),
            ...$oauthActions,
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::Center;
    }
}
