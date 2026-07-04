{{-- Spec-matching styling for the Filament admin sidebar. Injected via PanelsRenderHook::STYLES_AFTER. --}}
<style>
    /* ============================================================
       Sidebar navigation — layout & interaction spec
       Accent is driven by the panel's --primary-* colors so it
       stays consistent with the app theme (currently Emerald).
       ============================================================ */

    /* --- Sidebar shell: subtle right border (spec §1) --- */
    .fi-sidebar {
        border-right: 1px solid rgb(var(--gray-200));
    }
    .dark .fi-sidebar {
        border-right-color: rgb(var(--gray-800));
    }

    /* --- Brand header keeps 64px height (Filament default h-16) --- */
    .fi-sidebar-header {
        border-bottom: 1px solid rgb(var(--gray-200));
    }
    .dark .fi-sidebar-header {
        border-bottom-color: rgb(var(--gray-800));
    }

    /* --- Group (section) labels: 11px, 600, uppercase, tracked (spec §1/§6) --- */
    .fi-sidebar-group-label {
        font-size: 11px !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgb(var(--gray-400)) !important;
    }

    /* Space between groups (spec §7: 24px between sections) */
    .fi-sidebar-nav .fi-sidebar-group + .fi-sidebar-group {
        margin-top: 0.75rem;
    }

    /* --- Nav items: 48px rows, 12/16 padding, transparent left rail (spec §2) --- */
    .fi-sidebar-item-button {
        min-height: 2.75rem;
        border-radius: 8px;
        border-inline-start: 3px solid transparent;
        padding-inline-start: 0.8125rem;
        padding-inline-end: 0.75rem;
        justify-content: flex-start;
        transition: background-color .15s ease, color .15s ease, border-color .2s ease;
    }

    /* Icons at 20px (spec §8) */
    .fi-sidebar-item-icon,
    .fi-sidebar-group-icon {
        height: 1.25rem !important;
        width: 1.25rem !important;
    }

    /* Hover (spec §2) */
    .fi-sidebar-item-button:hover {
        background-color: rgb(var(--gray-100)) !important;
    }
    .dark .fi-sidebar-item-button:hover {
        background-color: rgb(var(--gray-800)) !important;
    }

    /* Active / selected: accent left border + tint (spec §2) */
    .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
    .fi-sidebar-item.fi-sidebar-item-active > .fi-sidebar-item-button {
        border-inline-start-color: rgb(var(--primary-500));
        background-color: rgb(var(--primary-500) / 0.10) !important;
    }
    .dark .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
    .dark .fi-sidebar-item.fi-sidebar-item-active > .fi-sidebar-item-button {
        background-color: rgb(var(--primary-400) / 0.14) !important;
    }
    .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
        color: rgb(var(--primary-500)) !important;
    }

    /* Focus ring (spec §10) */
    .fi-sidebar-item-button:focus-visible {
        outline: 2px solid rgb(var(--primary-500));
        outline-offset: 2px;
    }

    /* --- Collapsed rail: center items, remove left rail (spec §4) --- */
    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-button {
        justify-content: center;
        border-inline-start-color: transparent;
        padding-inline: 0.5rem;
    }

    /* --- Thin scrollbar (spec §14) --- */
    .fi-sidebar-nav {
        scrollbar-width: thin;
        scrollbar-color: rgb(var(--gray-300)) transparent;
    }
    .fi-sidebar-nav::-webkit-scrollbar { width: 6px; }
    .fi-sidebar-nav::-webkit-scrollbar-track { background: transparent; }
    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background: rgb(var(--gray-300));
        border-radius: 999px;
    }
    .dark .fi-sidebar-nav { scrollbar-color: rgb(var(--gray-600)) transparent; }
    .dark .fi-sidebar-nav::-webkit-scrollbar-thumb { background: rgb(var(--gray-600)); }

    /* --- Solid separator between main nav groups (expanded + collapsed) --- */
    .fi-sidebar .fi-sidebar-group + .fi-sidebar-group {
        margin-top: 0.875rem;
        padding-top: 0.875rem;
        border-top: 1px solid rgb(var(--gray-300));
    }
    .dark .fi-sidebar .fi-sidebar-group + .fi-sidebar-group {
        border-top-color: rgb(var(--gray-700));
    }

    /* ============================================================
       Bottom user profile block (spec §1.4 / §12)
       Rendered via PanelsRenderHook::SIDEBAR_FOOTER.
       ============================================================ */
    .lfos-sidebar-user {
        margin-top: auto;
        padding: 0.5rem;
        border-top: 1px solid rgb(var(--gray-200));
    }
    .dark .lfos-sidebar-user { border-top-color: rgb(var(--gray-800)); }

    .lfos-user-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.625rem;
        border-radius: 10px;
        text-decoration: none;
        transition: background-color .15s ease;
    }
    .lfos-user-link:hover { background-color: rgb(var(--gray-100)); }
    .dark .lfos-user-link:hover { background-color: rgb(var(--gray-800)); }
    .lfos-user-link:focus-visible {
        outline: 2px solid rgb(var(--primary-500));
        outline-offset: 2px;
    }

    .lfos-user-avatar {
        width: 2.25rem;
        height: 2.25rem;
        min-width: 2.25rem;
        border-radius: 9999px;
        object-fit: cover;
    }

    .lfos-user-meta {
        display: flex;
        flex-direction: column;
        min-width: 0;
        line-height: 1.2;
    }
    .lfos-user-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: rgb(var(--gray-800));
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark .lfos-user-name { color: rgb(var(--gray-100)); }
    .lfos-user-email {
        font-size: 0.75rem;
        font-weight: 400;
        color: rgb(var(--gray-400));
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Collapsed rail: center avatar only */
    .fi-sidebar:not(.fi-sidebar-open) .lfos-user-link { justify-content: center; padding-inline: 0; }
</style>
