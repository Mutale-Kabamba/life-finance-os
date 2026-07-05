{{-- Bottom user-profile block for the Filament sidebar. Injected via PanelsRenderHook::SIDEBAR_FOOTER. --}}
@php
    $panel = filament()->getCurrentPanel();
    $user = filament()->auth()->user();
    $userName = $user ? filament()->getUserName($user) : '';
    $userAvatar = $user ? filament()->getUserAvatarUrl($user) : null;
    $profileUrl = $panel && $panel->hasProfile() ? filament()->getProfileUrl() : null;
@endphp

@if ($user)
    <div class="lfos-sidebar-user">
        <a
            @if ($profileUrl) href="{{ $profileUrl }}" @endif
            class="lfos-user-link"
            @if ($profileUrl) aria-label="{{ $userName }} — account" @endif
        >
            @if ($userAvatar)
                <img src="{{ $userAvatar }}" alt="" class="lfos-user-avatar">
            @endif

            <span
                class="lfos-user-meta"
                x-show="$store.sidebar.isOpen"
                x-transition:enter="lg:transition lg:delay-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
            >
                <span class="lfos-user-name">{{ $userName }}</span>
                @if (filled($user->email ?? null))
                    <span class="lfos-user-email">{{ $user->email }}</span>
                @endif
            </span>
        </a>
    </div>
@endif
