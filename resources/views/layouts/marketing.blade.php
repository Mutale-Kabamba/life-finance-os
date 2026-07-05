<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Life Finance OS marketing pages">
    <title>@yield('title', config('app.name', 'Life Finance OS'))</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logos/icon_BG.png') }}">

    <style>
        :root {
            --bg: #ffffff;
            --ink: #101613;
            --muted: #626b66;
            --line: #eaefec;
            --line-soft: rgba(16, 22, 19, 0.08);
            --brand: #009933;
            --brand-dark: #007a29;
            --accent: #004AAD;
            --accent-dark: #003b8a;
            --mint-1: #e8f9ef;
            --mint-2: #f4fff8;
            --radius: 16px;
        }

        [data-theme="dark"] {
            --bg: #0b1210;
            --ink: #e8f3ee;
            --muted: #96a69d;
            --line: #22312b;
            --line-soft: rgba(232, 243, 238, 0.12);
            --brand: #00b33c;
            --brand-dark: #009933;
            --accent: #4d87ff;
            --accent-dark: #2f66d1;
            --mint-1: rgba(0, 179, 60, 0.14);
            --mint-2: rgba(0, 179, 60, 0.08);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: radial-gradient(1200px 420px at 12% -16%, var(--mint-1), transparent 58%),
                radial-gradient(900px 360px at 88% -8%, rgba(0, 74, 173, 0.11), transparent 62%),
                var(--bg);
            color: var(--ink);
            line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }

        .container { width: 100%; max-width: 1140px; margin-inline: auto; padding-inline: 24px; }

        .nav {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--line);
        }

        [data-theme="dark"] .nav {
            background: rgba(11, 18, 16, 0.84);
        }

        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 72px; gap: 12px; }
        .brand { display: inline-flex; align-items: center; line-height: 1; }
        .brand-logo { display: block; height: 30px; width: auto; max-width: 190px; object-fit: contain; }
        .brand-logo-dark { display: none; }
        [data-theme="dark"] .brand-logo-light { display: none; }
        [data-theme="dark"] .brand-logo-dark { display: block; }

        .nav-links { display: flex; align-items: center; gap: 4px; font-size: .92rem; color: var(--muted); flex-wrap: wrap; }
        .nav-links > a,
        .nav-dropdown-toggle {
            padding: 8px 14px;
            border-radius: 10px;
            transition: .18s ease;
            cursor: pointer;
        }

        .nav-links > a:hover,
        .nav-dropdown-toggle:hover { background: rgba(0, 153, 51, 0.1); color: var(--ink); }

        .nav-links > a.active,
        .nav-dropdown-toggle.active,
        .mobile-links a.active {
            background: rgba(0, 153, 51, 0.16);
            color: var(--ink);
            font-weight: 600;
        }

        .nav-dropdown {
            position: relative;
        }

        .nav-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 260px;
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            padding: 8px;
            z-index: 60;
        }

        .nav-dropdown:hover .nav-dropdown-menu,
        .nav-dropdown:focus-within .nav-dropdown-menu {
            display: block;
        }

        .nav-dropdown-menu a {
            display: block;
            padding: 9px 10px;
            border-radius: 8px;
            color: var(--muted);
        }

        .nav-dropdown-menu a:hover {
            background: rgba(0, 153, 51, 0.1);
            color: var(--ink);
        }

        [data-theme="dark"] .nav-dropdown-menu {
            background: #101a17;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
        }

        .nav-actions { display: flex; align-items: center; gap: 8px; }

        .theme-toggle,
        .menu-toggle {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        [data-theme="dark"] .theme-toggle,
        [data-theme="dark"] .menu-toggle { background: #10231c; }

        .theme-icon-sun { display: none; }
        [data-theme="dark"] .theme-icon-sun { display: inline; }
        [data-theme="dark"] .theme-icon-moon { display: none; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: .9rem;
            border: 1px solid transparent;
        }

        .btn-dark { background: var(--accent); color: #fff; }
        .btn-dark:hover { background: var(--accent-dark); }
        .btn-ghost { color: var(--ink); }
        .btn-ghost:hover { background: rgba(0, 153, 51, 0.1); }
        .btn-primary { background: var(--brand); color: #fff; }
        .btn-primary:hover { background: var(--brand-dark); }
        .btn-outline {
            border-color: var(--line);
            color: var(--ink);
            background: #fff;
        }
        .btn-outline:hover {
            border-color: var(--brand);
            color: var(--brand-dark);
            background: var(--mint-2);
        }

        .hero {
            position: relative;
            margin: 14px auto 0;
            max-width: 1200px;
            border-radius: 28px;
            padding: 92px 24px 96px;
            min-height: 680px;
            text-align: center;
            overflow: hidden;
            background: transparent;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(135deg, rgba(17, 27, 41, .46) 0%, rgba(28, 44, 61, .38) 48%, rgba(26, 42, 55, .44) 100%),
                url('{{ asset('img/bg2.png') }}');
            background-size: cover, cover;
            background-repeat: no-repeat;
            background-position: center center, center center;
            opacity: .98;
            filter: grayscale(10%) saturate(94%) contrast(103%);
            -webkit-mask-image: linear-gradient(to top, transparent 0%, rgba(0, 0, 0, 0.14) 18%, rgba(0, 0, 0, 0.76) 38%, rgba(0, 0, 0, 1) 50%, rgba(0, 0, 0, 1) 100%);
            mask-image: linear-gradient(to top, transparent 0%, rgba(0, 0, 0, 0.14) 18%, rgba(0, 0, 0, 0.76) 38%, rgba(0, 0, 0, 1) 50%, rgba(0, 0, 0, 1) 100%);
            pointer-events: none;
            z-index: 0;
        }
        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(760px 320px at 50% -5%, rgba(255,255,255,.1) 0%, transparent 62%),
                linear-gradient(180deg, rgba(5, 9, 14, .72) 0%, rgba(5, 9, 14, .52) 32%, rgba(5, 9, 14, .24) 56%, rgba(5, 9, 14, .08) 74%, rgba(5, 9, 14, 0) 100%);
            pointer-events: none;
            z-index: 0;
        }
        .hero > * {
            position: relative;
            z-index: 1;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: var(--brand-dark);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 7px 16px;
            font-size: .82rem;
            font-weight: 600;
            margin-bottom: 26px;
            text-wrap: balance;
        }
        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--brand);
        }
        .hero h1 {
            font-size: clamp(2.4rem, 6.2vw, 4.6rem);
            line-height: 1.11;
            letter-spacing: -0.03em;
            font-weight: 700;
            max-width: 16ch;
            margin-inline: auto;
            text-wrap: balance;
            color: #f2f7f5;
            margin-bottom: 0;
        }
        .hero h1 .grad {
            background: linear-gradient(110deg, var(--accent), var(--brand) 62%, #47d36f);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            white-space: normal;
        }
        .lede {
            margin: 22px auto 0;
            font-size: clamp(0.98rem, 2.9vw, 1.12rem);
            color: rgba(227, 238, 232, 0.88);
            max-width: 46ch;
            text-wrap: pretty;
        }
        .hero-note {
            margin-top: 16px;
            font-size: .85rem;
            color: rgba(203, 220, 212, 0.85);
            max-width: none;
            margin-inline: auto;
            text-align: center;
        }

        .preview {
            position: relative;
            margin: 56px auto 0;
            max-width: 880px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: left;
        }
        .preview-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
        }
        .preview-title { font-weight: 600; font-size: .95rem; }
        .preview-title small { display: block; color: var(--muted); font-weight: 400; font-size: .78rem; }
        .win-dots { display: flex; gap: 6px; }
        .win-dots i { width: 10px; height: 10px; border-radius: 50%; background: var(--line); display: block; }
        .preview-body { display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px; padding: 20px; }
        .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; grid-column: 1 / -1; }
        .kpi { background: #fafcfb; border: 1px solid var(--line); border-radius: 14px; padding: 14px; }
        .kpi b { display: block; font-size: .74rem; color: var(--muted); font-weight: 600; }
        .kpi span {
            display: block;
            margin-top: 6px;
            font-size: clamp(1rem, 2.8vw, 1.15rem);
            font-weight: 700;
            letter-spacing: -.01em;
            line-height: 1.15;
        }
        .kpi em { font-style: normal; font-size: .74rem; color: var(--brand); font-weight: 600; }
        .chart { background: #fafcfb; border: 1px solid var(--line); border-radius: 14px; padding: 16px; }
        .chart h4, .list h4 { font-size: .8rem; color: var(--muted); font-weight: 600; margin-bottom: 14px; }
        .bars { display: flex; align-items: end; gap: 10px; height: 120px; }
        .bar { flex: 1; border-radius: 8px 8px 4px 4px; background: linear-gradient(180deg, var(--accent), var(--brand)); }
        .bar:nth-child(1){ height: 40% } .bar:nth-child(2){ height: 66% }
        .bar:nth-child(3){ height: 52% } .bar:nth-child(4){ height: 82% }
        .bar:nth-child(5){ height: 60% } .bar:nth-child(6){ height: 94% }
        .list { background: #fafcfb; border: 1px solid var(--line); border-radius: 14px; padding: 16px; }
        .list ul { list-style: none; display: grid; gap: 12px; padding-left: 0; }
        .list li { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: .82rem; margin-top: 0; }
        .track { width: 52%; height: 7px; background: #e8f2ec; border-radius: 999px; overflow: hidden; }
        .track i { display: block; height: 100%; background: linear-gradient(90deg, var(--brand), var(--accent)); }

        .menu-toggle { display: none; }

        .mobile-menu { display: none; border-top: 1px solid var(--line); padding: 10px 14px 14px; }
        .mobile-menu.open { display: block; }
        .mobile-links { display: grid; gap: 4px; }
        .mobile-links a { padding: 10px; border-radius: 8px; color: var(--muted); }
        .mobile-links a:hover { background: rgba(0, 153, 51, 0.1); color: var(--ink); }
        .mobile-actions { margin-top: 10px; display: grid; gap: 8px; }

        .page { padding: 0 0 52px; }
        .section { padding: 88px 0; }

        h1 { font-size: clamp(2rem, 4vw, 2.7rem); margin-bottom: 10px; line-height: 1.15; letter-spacing: -0.02em; }
        .lead { font-size: 1.06rem; color: var(--muted); max-width: 70ch; }
        .hero-actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        h2 { font-size: clamp(1.9rem, 4vw, 2.7rem); letter-spacing: -.03em; margin-top: 10px; line-height: 1.1; }
        p, li { color: #374151; }
        [data-theme="dark"] p,
        [data-theme="dark"] li { color: #d0ddd7; }
        p { max-width: 70ch; }
        p + p { margin-top: 10px; }
        ul { padding-left: 20px; }
        li + li { margin-top: 6px; }

        .eyebrow {
            color: var(--brand);
            font-weight: 700;
            font-size: .8rem;
            letter-spacing: .1em;
            text-transform: uppercase;
        }
        .section-head {
            max-width: 34ch;
            margin: 0 auto 48px;
            text-align: center;
        }
        .section-head p {
            margin-top: 14px;
            color: var(--muted);
            font-size: 1.05rem;
            max-width: 44ch;
            margin-inline: auto;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 30px 26px;
            transition: .2s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.16);
        }
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            margin-bottom: 20px;
            display: grid;
            place-items: center;
            background: var(--mint-1);
            border: 1px solid var(--mint-2);
        }
        .card-icon svg { width: 24px; height: 24px; stroke: var(--brand-dark); }
        .card h3 { font-size: 1.15rem; font-weight: 600; }
        .card p { margin-top: 10px; color: var(--muted); font-size: .95rem; max-width: none; }

        .faq-wrap {
            max-width: 860px;
            margin-inline: auto;
            display: grid;
            gap: 12px;
        }
        .faq-item {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            overflow: hidden;
        }
        .faq-item summary {
            list-style: none;
            cursor: pointer;
            font-weight: 600;
            color: var(--ink);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary::after {
            content: '+';
            font-size: 1.2rem;
            line-height: 1;
            color: var(--brand);
            transition: transform .2s ease;
            flex-shrink: 0;
        }
        .faq-item[open] summary::after { content: '−'; }
        .faq-content {
            border-top: 1px solid var(--line);
            padding: 12px 18px 16px;
            color: var(--muted);
            font-size: .95rem;
            max-width: none;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 22px;
            align-items: start;
        }
        .contact-card,
        .contact-info {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 22px;
        }
        .contact-card h3,
        .contact-info h3 { font-size: 1.2rem; margin-bottom: 8px; }
        .contact-card p,
        .contact-info p { color: var(--muted); font-size: .93rem; max-width: none; }
        .contact-form { margin-top: 16px; display: grid; gap: 12px; }
        .contact-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .field { display: grid; gap: 6px; }
        .field label { font-size: .82rem; color: var(--muted); font-weight: 600; }
        .field input,
        .field textarea,
        .field select {
            width: 100%;
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 12px;
            font: inherit;
            outline: none;
        }
        .field textarea { min-height: 110px; resize: vertical; }
        .field input:focus,
        .field textarea:focus,
        .field select:focus { border-color: rgba(0, 153, 51, .45); box-shadow: 0 0 0 3px rgba(0, 153, 51, .12); }
        .contact-pill {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            display: grid;
            gap: 3px;
            margin-top: 10px;
        }
        .contact-pill span { color: var(--muted); font-size: .78rem; font-weight: 600; }

        .section-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 18px;
        }
        .info-card {
            border: 1px solid var(--line-soft);
            border-radius: 16px;
            padding: 16px;
            background: #fff;
        }
        .info-card h3 {
            font-size: .98rem;
            line-height: 1.3;
            margin-bottom: 6px;
        }
        .info-card p {
            font-size: .95rem;
            color: var(--muted);
            max-width: none;
        }
        .stack {
            margin-top: 16px;
            display: grid;
            gap: 10px;
        }
        .stack .block {
            border: 1px solid var(--line-soft);
            border-radius: 14px;
            padding: 14px;
            background: #fff;
        }

        [data-theme="dark"] .card,
        [data-theme="dark"] .faq-item,
        [data-theme="dark"] .contact-card,
        [data-theme="dark"] .contact-info,
        [data-theme="dark"] .field input,
        [data-theme="dark"] .field textarea,
        [data-theme="dark"] .field select,
        [data-theme="dark"] .contact-pill {
            background: #0f1815;
            border-color: var(--line);
        }
        [data-theme="dark"] .faq-item summary,
        [data-theme="dark"] .contact-pill span,
        [data-theme="dark"] .field input,
        [data-theme="dark"] .field textarea,
        [data-theme="dark"] .field select {
            color: var(--ink);
        }
        [data-theme="dark"] .btn-outline {
            background: #0f1815;
        }

        .reveal {
            opacity: 0;
            transform: translateY(16px);
            animation: revealUp .6s ease forwards;
        }
        .delay-1 { animation-delay: .08s; }
        .delay-2 { animation-delay: .16s; }
        @keyframes revealUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cta {
            margin-top: 20px;
            background: linear-gradient(150deg, #0b100d, #123b2b);
            border-radius: 26px;
            padding: 54px 32px;
            text-align: center;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .cta h2 {
            margin: 0;
            color: #ffffff;
            font-size: clamp(1.55rem, 3.2vw, 2rem);
        }
        .cta p {
            margin: 12px auto 0;
            color: #b8c7bf;
            max-width: 54ch;
        }
        .cta .hero-actions {
            justify-content: center;
            margin-top: 22px;
        }
        .cta .btn-outline {
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.08);
        }
        .cta .btn-outline:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.7);
        }

        footer { border-top: 1px solid var(--line); padding: 26px 0; margin-top: 36px; }
        .foot-inner { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; color: var(--muted); font-size: .88rem; }

        @media (max-width: 980px) {
            .nav-links,
            .nav-actions a { display: none; }

            .menu-toggle { display: inline-flex; }

            .nav-inner {
                height: 68px;
            }

            .brand-logo {
                height: 24px;
                max-width: 150px;
            }

            .section { padding: 56px 0; }
            .grid { grid-template-columns: 1fr; }
            .hero {
                padding: 68px 20px 72px;
                border-radius: 22px;
                margin-top: 8px;
                min-height: 620px;
            }
            .preview {
                margin-top: 40px;
            }
            .preview-top {
                padding: 14px 16px;
            }
            .preview-body {
                grid-template-columns: 1fr;
                padding: 14px;
                gap: 12px;
            }
            .kpis { grid-template-columns: 1fr; }
            .contact-grid { grid-template-columns: 1fr; }
            .contact-row { grid-template-columns: 1fr; }
            .hero-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .cta {
                padding: 38px 20px;
                border-radius: 20px;
            }
        }
    </style>
</head>
<body>
    @php
        $loginUrl = Route::has('filament.admin.auth.login') ? route('filament.admin.auth.login') : url('/admin/login');
        $registerUrl = Route::has('filament.admin.auth.register') ? route('filament.admin.auth.register') : url('/admin/register');
        $isPolicies = request()->routeIs('privacy-policy')
            || request()->routeIs('data-deletion-instructions')
            || request()->routeIs('terms-and-conditions');
    @endphp

    <header class="nav">
        <div class="container nav-inner">
            <a href="{{ route('home') }}" class="brand">
                <img src="{{ asset('img/logos/lf_BG.png') }}" alt="{{ config('app.name', 'Life Finance OS') }} logo" class="brand-logo brand-logo-light" />
                <img src="{{ asset('img/logos/lf_W.png') }}" alt="{{ config('app.name', 'Life Finance OS') }} logo" class="brand-logo brand-logo-dark" />
            </a>

            <nav class="nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('how-it-works') }}" class="{{ request()->routeIs('how-it-works') ? 'active' : '' }}">How it works</a>
                <a href="{{ route('features-faq') }}" class="{{ request()->routeIs('features-faq') ? 'active' : '' }}">Features & FAQ</a>
                <a href="{{ route('contacts') }}" class="{{ request()->routeIs('contacts') ? 'active' : '' }}">Contacts</a>
                <div class="nav-dropdown">
                    <span class="nav-dropdown-toggle {{ $isPolicies ? 'active' : '' }}">Policies</span>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('privacy-policy') }}" class="{{ request()->routeIs('privacy-policy') ? 'active' : '' }}">Privacy Policy</a>
                        <a href="{{ route('data-deletion-instructions') }}" class="{{ request()->routeIs('data-deletion-instructions') ? 'active' : '' }}">Data Deletion Instructions</a>
                        <a href="{{ route('terms-and-conditions') }}" class="{{ request()->routeIs('terms-and-conditions') ? 'active' : '' }}">Terms & Conditions</a>
                    </div>
                </div>
            </nav>

            <nav class="nav-actions">
                <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode" title="Toggle dark mode">
                    <span class="theme-icon-moon">◐</span>
                    <span class="theme-icon-sun">☀</span>
                </button>
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-dark">Go to dashboard</a>
                @else
                    <a href="{{ $loginUrl }}" class="btn btn-ghost">Log in</a>
                    <a href="{{ $registerUrl }}" class="btn btn-dark">Get started</a>
                @endauth
                <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobile-menu">☰</button>
            </nav>
        </div>

        <div class="mobile-menu" id="mobile-menu">
            <nav class="mobile-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('how-it-works') }}" class="{{ request()->routeIs('how-it-works') ? 'active' : '' }}">How it works</a>
                <a href="{{ route('features-faq') }}" class="{{ request()->routeIs('features-faq') ? 'active' : '' }}">Features & FAQ</a>
                <a href="{{ route('contacts') }}" class="{{ request()->routeIs('contacts') ? 'active' : '' }}">Contacts</a>
                <a href="{{ route('privacy-policy') }}" class="{{ request()->routeIs('privacy-policy') ? 'active' : '' }}">Privacy Policy</a>
                <a href="{{ route('data-deletion-instructions') }}" class="{{ request()->routeIs('data-deletion-instructions') ? 'active' : '' }}">Data Deletion Instructions</a>
                <a href="{{ route('terms-and-conditions') }}" class="{{ request()->routeIs('terms-and-conditions') ? 'active' : '' }}">Terms & Conditions</a>
            </nav>
            <div class="mobile-actions">
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-dark">Go to dashboard</a>
                @else
                    <a href="{{ $loginUrl }}" class="btn btn-ghost">Log in</a>
                    <a href="{{ $registerUrl }}" class="btn btn-dark">Get started</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="page">
        @php
            $showHeroPreview = trim($__env->yieldContent('hero_show_preview', '1')) !== '0';
        @endphp

        <section class="hero">
            <span class="pill reveal"><span class="dot"></span> @yield('hero_pill', 'Personal · Family · Business · Wealth')</span>
            <h1 class="reveal">@yield('hero_title_prefix', 'One dashboard,') <span class="grad">@yield('hero_title_highlight', 'total financial control.')</span></h1>
            <p class="lede reveal delay-1">@yield('hero_caption', config('app.name', 'Life Finance OS') . ' brings every part of your financial life into one calm, modern dashboard.') </p>
            <div class="hero-actions reveal delay-1">
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-primary">Open your dashboard</a>
                @else
                    <a href="{{ $registerUrl }}" class="btn btn-primary">Create free account</a>
                    <a href="{{ $loginUrl }}" class="btn btn-outline">Log in</a>
                @endauth
            </div>
            <p class="hero-note reveal delay-1">No credit card required · Set up in minutes</p>

            @if ($showHeroPreview)
            <div class="preview reveal delay-2" aria-label="Dashboard preview">
                <div class="preview-top">
                    <div class="preview-title">
                        Financial overview
                        <small>Updated just now</small>
                    </div>
                    <div class="win-dots"><i></i><i></i><i></i></div>
                </div>
                <div class="preview-body">
                    <div class="kpis">
                        <div class="kpi">
                            <b>Total balance</b>
                            <span>ZMW 82,326</span>
                            <em>+8.2% this month</em>
                        </div>
                        <div class="kpi">
                            <b>Monthly inflow</b>
                            <span>ZMW 4,268</span>
                            <em>+3.1% vs last</em>
                        </div>
                        <div class="kpi">
                            <b>Budget on track</b>
                            <span>92%</span>
                            <em>Healthy</em>
                        </div>
                    </div>
                    <div class="chart">
                        <h4>Cash flow trend</h4>
                        <div class="bars">
                            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                        </div>
                    </div>
                    <div class="list">
                        <h4>Budget health</h4>
                        <ul>
                            <li>Household <span class="track"><i style="width:78%"></i></span></li>
                            <li>Business <span class="track"><i style="width:64%"></i></span></li>
                            <li>Investing <span class="track"><i style="width:86%"></i></span></li>
                            <li>Savings <span class="track"><i style="width:71%"></i></span></li>
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </section>

        <section class="section">
            <div class="container">
                @yield('content')
            </div>
        </section>

        <section class="section" style="padding-top: 0;">
            <div class="container">
                <div class="cta reveal delay-1">
                    <h2>Ready to simplify your financial life?</h2>
                    <p>Start in minutes and bring personal, family, business, and wealth data into one clear workflow.</p>
                    <div class="hero-actions">
                        @auth
                            <a href="{{ url('/admin') }}" class="btn btn-primary">Open your dashboard</a>
                        @else
                            <a href="{{ $registerUrl }}" class="btn btn-primary">Create free account</a>
                            <a href="{{ $loginUrl }}" class="btn btn-outline">Log in</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container foot-inner">
            <span>© {{ date('Y') }} {{ config('app.name', 'Life Finance OS') }}. All rights reserved.</span>
            <span>Your money, all in one place.</span>
        </div>
    </footer>

    <script>
        (() => {
            const root = document.documentElement;
            const themeToggle = document.getElementById('theme-toggle');
            const menuToggle = document.getElementById('menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');

            const applyTheme = (theme) => {
                root.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
            };

            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || savedTheme === 'light') {
                applyTheme(savedTheme);
            } else {
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                applyTheme(prefersDark ? 'dark' : 'light');
            }

            themeToggle?.addEventListener('click', () => {
                const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                applyTheme(next);
            });

            menuToggle?.addEventListener('click', () => {
                const open = mobileMenu?.classList.toggle('open');
                menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        })();
    </script>
</body>
</html>
