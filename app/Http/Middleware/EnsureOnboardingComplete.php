<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Filament\Pages\OnboardingWizard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Force authenticated panel users into the onboarding wizard until
     * their profile has onboarding_completed = true.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $completed = (bool) ($user->profile?->onboarding_completed ?? false);

            $isExempt = $request->routeIs('filament.*.pages.onboarding-wizard')
                || $request->routeIs('filament.*.auth.logout')
                || $request->routeIs('filament.*.auth.*');

            if (! $completed && ! $isExempt) {
                return redirect()->to(OnboardingWizard::getUrl());
            }
        }

        return $next($request);
    }
}
