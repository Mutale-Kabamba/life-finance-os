<style>
    .fi-simple-main-ctn {
        align-items: center;
    }

    .fi-simple-layout::after {
        content: 'By Ori Studio Limited';
        display: block;
        width: 100%;
        text-align: center;
        color: rgb(var(--gray-500));
        font-size: 0.75rem;
        line-height: 1.3;
        padding: 0 0 0.85rem;
    }

    .dark .fi-simple-layout::after {
        color: rgb(var(--gray-400));
    }

    @media (max-width: 640px) {
        .fi-simple-layout {
            padding-inline: 0.75rem;
        }

        .fi-simple-main-ctn {
            align-items: center;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .fi-simple-main {
            margin-top: 0.75rem !important;
            margin-bottom: 0.75rem !important;
            padding: 1rem !important;
            border-radius: 0.875rem !important;
        }

        .fi-simple-header {
            gap: 0.125rem;
        }

        .fi-simple-header .fi-logo {
            max-width: 9.5rem;
            height: auto;
        }
    }
</style>