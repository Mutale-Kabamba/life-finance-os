<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function mount(): void
    {
        parent::mount();

        $previous = url()->previous();

        // Remember where the user came from (but ignore profile page loops).
        if (filled($previous) && ! str_contains($previous, '/profile')) {
            session(['filament.profile_return_url' => $previous]);
        }
    }

    protected function getRedirectUrl(): ?string
    {
        return session('filament.profile_return_url')
            ?? Filament::getCurrentPanel()?->getUrl()
            ?? null;
    }
}
