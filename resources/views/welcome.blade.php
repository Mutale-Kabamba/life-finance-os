<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Life Finance OS is a modern financial MIS for personal, family, and business finance management.">

    <title>{{ config('app.name', 'Life Finance OS') }} — Modern Financial MIS</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logos/icon_BG.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|space-grotesk:500,600,700" rel="stylesheet" />

    <style>
        :root {
            --bg: #ffffff;
            --ink: #101613;
            --muted: #626b66;
            --line: #eaefec;
            --brand: #009933;
            --brand-dark: #007a29;
            --accent: #004AAD;
            --accent-dark: #003b8a;
            --mint-1: #e9f7f0;
            --mint-2: #d7f0e4;
            --radius: 20px;
            --shadow-sm: 0 6px 20px -14px rgba(16, 22, 19, 0.35);
            --shadow-lg: 0 30px 80px -40px rgba(16, 22, 19, 0.4);
        }

        [data-theme="dark"] {
            --bg: #0b1210;
            --ink: #e8f3ee;
            --muted: #96a69d;
            --line: #22312b;
            --brand: #00b33c;
            --brand-dark: #009933;
            --accent: #4d87ff;
            --accent-dark: #2f66d1;
            --mint-1: #10231c;
            --mint-2: #173128;
            --shadow-sm: 0 6px 20px -14px rgba(0, 0, 0, 0.55);
            --shadow-lg: 0 30px 80px -40px rgba(0, 0, 0, 0.8);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { min-height: 100%; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }

        .container { width: 100%; max-width: 1140px; margin-inline: auto; padding-inline: 24px; }

        /* Nav */
        .nav {
            position: sticky; top: 0; z-index: 40;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--line);
        }
        [data-theme="dark"] .nav {
            background: rgba(11, 18, 16, 0.84);
        }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 72px; }
        .brand {
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }
        .brand-logo {
            display: block;
            height: 30px;
            width: auto;
            max-width: 190px;
            object-fit: contain;
        }
        .brand-logo-dark {
            display: none;
        }
        [data-theme="dark"] .brand-logo-light {
            display: none;
        }
        [data-theme="dark"] .brand-logo-dark {
            display: block;
        }
        .nav-links { display: flex; align-items: center; gap: 4px; font-size: .92rem; color: var(--muted); }
        .nav-links > a,
        .nav-dropdown-toggle {
            padding: 8px 14px;
            border-radius: 10px;
            transition: .18s ease;
            cursor: pointer;
        }
        .nav-links > a:hover,
        .nav-dropdown-toggle:hover { background: var(--mint-1); color: var(--ink); }
        .nav-links > a.active,
        .nav-dropdown-toggle.active,
        .mobile-links a.active {
            background: color-mix(in srgb, var(--mint-1) 75%, #c7f6d9 25%);
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
            min-width: 250px;
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            padding: 8px;
            z-index: 50;
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
            background: var(--mint-1);
            color: var(--ink);
        }
        [data-theme="dark"] .nav-dropdown-menu {
            background: #101a17;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
        }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .menu-toggle,
        .theme-toggle {
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
            transition: .18s ease;
        }
        .menu-toggle:hover,
        .theme-toggle:hover { background: var(--mint-1); }
        .menu-toggle { display: none; }
        [data-theme="dark"] .menu-toggle,
        [data-theme="dark"] .theme-toggle { background: #10231c; }
        .theme-icon-sun { display: none; }
        [data-theme="dark"] .theme-icon-sun { display: inline; }
        [data-theme="dark"] .theme-icon-moon { display: none; }

        .mobile-menu {
            display: none;
            border-top: 1px solid var(--line);
            padding: 12px 16px 14px;
            background: rgba(255, 255, 255, 0.95);
        }
        [data-theme="dark"] .mobile-menu {
            background: rgba(11, 18, 16, 0.96);
        }
        .mobile-menu.open { display: block; }
        .mobile-links {
            display: grid;
            gap: 4px;
        }
        .mobile-links a {
            padding: 10px 10px;
            border-radius: 10px;
            color: var(--muted);
        }
        .mobile-links a:hover { background: var(--mint-1); color: var(--ink); }
        .mobile-actions {
            margin-top: 12px;
            display: grid;
            gap: 8px;
        }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 20px; border-radius: 12px;
            font-weight: 600; font-size: .92rem; cursor: pointer;
            border: 1px solid transparent; transition: .18s ease; white-space: nowrap;
        }
        .btn-ghost { color: var(--ink); }
        .btn-ghost:hover { background: var(--mint-1); }
        .btn-dark { background: var(--accent); color: #fff; }
        .btn-dark:hover { transform: translateY(-1px); background: var(--accent-dark); }
        .btn-primary { background: var(--brand); color: #fff; box-shadow: var(--shadow-sm); }
        .btn-primary:hover { background: var(--brand-dark); transform: translateY(-1px); }
        .btn-outline { border-color: var(--line); color: var(--ink); background: #fff; }
        .btn-outline:hover { border-color: var(--brand); color: var(--brand-dark); }

        /* Hero */
        .hero {
            position: relative;
            margin: 18px auto 0;
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
            background-blend-mode: normal;
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
            display: inline-flex; align-items: center; gap: 8px;
            background: #fff; color: var(--brand-dark);
            border: 1px solid var(--line); border-radius: 999px;
            padding: 7px 16px; font-size: .82rem; font-weight: 600; margin-bottom: 26px;
            box-shadow: var(--shadow-sm);
            text-wrap: balance;
        }
        .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--brand); }
        h1 {
            font-size: clamp(2.4rem, 6.2vw, 4.6rem);
            line-height: 1.11; letter-spacing: -0.03em; font-weight: 700;
            max-width: 16ch; margin-inline: auto;
            text-wrap: balance;
            color: #f2f7f5;
        }
        h1 .grad {
            background: linear-gradient(110deg, var(--accent), var(--brand) 62%, #47d36f);
            -webkit-background-clip: text; background-clip: text; color: transparent;
            white-space: normal;
        }
        .lede {
            margin: 22px auto 0;
            font-size: clamp(0.98rem, 2.9vw, 1.12rem);
            color: rgba(227, 238, 232, 0.88);
            max-width: 46ch;
            text-wrap: pretty;
        }
        .hero-actions { margin-top: 32px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .hero-note {
            margin-top: 16px;
            font-size: .85rem;
            color: rgba(203, 220, 212, 0.85);
            margin-inline: auto;
            text-align: center;
            max-width: none;
        }
        .hero-copy {
            transition: opacity .32s ease, transform .32s ease;
            will-change: opacity, transform;
        }
        .hero-copy.is-changing {
            opacity: 0;
            transform: translateY(6px);
        }

        /* Floating dashboard card */
        .preview {
            position: relative;
            margin: 56px auto 0;
            max-width: 880px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            text-align: left;
        }
        .preview-top {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1px solid var(--line);
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
        .bar { flex: 1; border-radius: 8px 8px 4px 4px; background: linear-gradient(180deg, var(--accent), var(--brand)); animation: rise .8s ease-out both; }
        .bar:nth-child(1){ height: 40%; animation-delay:.05s } .bar:nth-child(2){ height: 66%; animation-delay:.12s }
        .bar:nth-child(3){ height: 52%; animation-delay:.19s } .bar:nth-child(4){ height: 82%; animation-delay:.26s }
        .bar:nth-child(5){ height: 60%; animation-delay:.33s } .bar:nth-child(6){ height: 94%; animation-delay:.4s }
        .list { background: #fafcfb; border: 1px solid var(--line); border-radius: 14px; padding: 16px; }
        .list ul { list-style: none; display: grid; gap: 12px; }
        .list li { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: .82rem; }
        .track { width: 52%; height: 7px; background: #e8f2ec; border-radius: 999px; overflow: hidden; }
        .track i { display: block; height: 100%; background: linear-gradient(90deg, var(--brand), var(--accent)); }

        /* Logos */
        .logos { padding: 44px 0 8px; }
        .logos p { text-align: center; color: var(--muted); font-size: .82rem; margin-bottom: 20px; }
        .logo-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 40px; opacity: .7; }
        .logo-row span { font-weight: 700; font-size: 1.05rem; letter-spacing: -.02em; color: #7b847f; }

        /* Sections */
        .section { padding: 88px 0; }
        .section-head { max-width: 34ch; margin: 0 auto 48px; text-align: center; }
        .eyebrow { color: var(--brand); font-weight: 700; font-size: .8rem; letter-spacing: .1em; text-transform: uppercase; }
        h2 { font-size: clamp(1.9rem, 4vw, 2.7rem); letter-spacing: -.03em; margin-top: 10px; line-height: 1.1; }
        .section-head p { margin-top: 14px; color: var(--muted); font-size: 1.05rem; }

        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .card {
            background: #fff; border: 1px solid var(--line);
            border-radius: var(--radius); padding: 30px 26px; transition: .2s ease;
        }
        .card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .card-icon {
            width: 50px; height: 50px; border-radius: 14px; margin-bottom: 20px;
            display: grid; place-items: center; background: var(--mint-1); border: 1px solid var(--mint-2);
        }
        .card-icon svg { width: 24px; height: 24px; stroke: var(--brand-dark); }
        .card h3 { font-size: 1.15rem; font-weight: 600; }
        .card p { margin-top: 10px; color: var(--muted); font-size: .95rem; }

        /* FAQ */
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
        .faq-item[open] summary::after {
            content: '−';
        }
        .faq-content {
            border-top: 1px solid var(--line);
            padding: 12px 18px 16px;
            color: var(--muted);
            font-size: .95rem;
        }

        /* Contact */
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
        .contact-info h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
        }
        .contact-card p,
        .contact-info p {
            color: var(--muted);
            font-size: .93rem;
        }
        .contact-form {
            margin-top: 16px;
            display: grid;
            gap: 12px;
        }
        .contact-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .field {
            display: grid;
            gap: 6px;
        }
        .field label {
            font-size: .82rem;
            color: var(--muted);
            font-weight: 600;
        }
        .field input,
        .field textarea,
        .field select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 12px;
            font: inherit;
            color: var(--ink);
            background: #fff;
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .field input:focus,
        .field textarea:focus,
        .field select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(15, 157, 108, .16);
        }
        .field textarea {
            min-height: 130px;
            resize: vertical;
        }
        .contact-list {
            margin-top: 16px;
            display: grid;
            gap: 10px;
        }
        .contact-pill {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 12px;
            background: #fafcfb;
        }
        .contact-pill b {
            display: block;
            font-size: .82rem;
            color: var(--muted);
            margin-bottom: 2px;
        }
        .contact-pill span {
            font-size: .95rem;
            color: var(--ink);
            font-weight: 600;
        }

        /* CTA */
        .cta {
            background: linear-gradient(150deg, #0b100d, #123b2b);
            border-radius: 28px; padding: 72px 40px; text-align: center; color: #fff;
        }
        .cta h2 { color: #fff; }
        .cta p { margin-top: 14px; color: #b8c7bf; font-size: 1.08rem; max-width: 44ch; margin-inline: auto; }
        .cta .hero-actions { margin-top: 30px; }
        .btn-light { background: #fff; color: var(--ink); }
        .btn-light:hover { background: var(--mint-1); transform: translateY(-1px); }
        .btn-clear { border-color: rgba(255,255,255,.25); color: #fff; }
        .btn-clear:hover { background: rgba(255,255,255,.1); }

        /* Footer */
        footer { border-top: 1px solid var(--line); padding: 36px 0; }
        .foot-inner { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; color: var(--muted); font-size: .88rem; }

        .reveal { opacity: 0; animation: revealUp .7s ease forwards; }
        .delay-1 { animation-delay: .1s; } .delay-2 { animation-delay: .2s; }
        @keyframes revealUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes rise { from { transform: scaleY(.35); transform-origin: bottom; opacity: .3; } to { transform: scaleY(1); transform-origin: bottom; opacity: 1; } }

        @media (max-width: 860px) {
            .grid { grid-template-columns: 1fr; }
            .contact-grid { grid-template-columns: 1fr; }
            .preview-body { grid-template-columns: 1fr; }
            .kpis { grid-template-columns: 1fr; }
            .nav-links { display: none; }
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
                padding: 14px;
                gap: 12px;
            }
            .kpi {
                padding: 12px;
            }
            .list li {
                font-size: .8rem;
            }
            .cta { padding: 52px 24px; }
        }

        @media (max-width: 700px) {
            .nav-inner {
                height: auto;
                min-height: 68px;
                padding-block: 10px;
                flex-wrap: nowrap;
                gap: 10px;
            }

            .brand {
                font-size: .98rem;
            }

            .nav-actions {
                justify-content: flex-end;
                margin-left: auto;
            }

            .nav-actions .btn {
                padding: 10px 14px;
                font-size: .86rem;
            }

            .nav-actions .btn-ghost,
            .nav-actions .btn-dark {
                display: none;
            }

            .menu-toggle {
                display: inline-flex;
            }

            .mobile-menu {
                display: none;
            }

            .hero {
                padding: 56px 14px 56px;
                min-height: 560px;
            }

            .hero::before {
                background-position: 62% center;
                opacity: .48;
            }

            .pill {
                font-size: .76rem;
                padding: 6px 12px;
                margin-bottom: 18px;
            }

            h1 {
                font-size: clamp(1.95rem, 10.3vw, 2.8rem);
                max-width: 12ch;
                line-height: 1.12;
            }

            .hero-actions {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .hero-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .preview {
                border-radius: 16px;
            }

            .preview-title {
                font-size: .88rem;
            }

            .bars {
                height: 92px;
                gap: 8px;
            }

            .track {
                width: 48%;
            }

            .section {
                padding: 64px 0;
            }

            .section-head {
                margin-bottom: 30px;
            }

            .card {
                padding: 24px 18px;
                border-radius: 16px;
            }

            .faq-item summary {
                padding: 14px 14px;
                font-size: .95rem;
            }

            .faq-content {
                padding: 10px 14px 14px;
                font-size: .9rem;
            }

            .contact-card,
            .contact-info {
                padding: 16px;
            }

            .contact-row {
                grid-template-columns: 1fr;
            }

            .cta {
                border-radius: 18px;
                padding: 36px 16px;
            }

            .cta .hero-actions {
                margin-top: 18px;
            }
        }

        @media (max-width: 520px) {
            .container { padding-inline: 16px; }
            .logo-row { gap: 24px; }

            .brand-logo {
                height: 24px;
                max-width: 150px;
            }

            .nav-actions {
                gap: 8px;
            }

            .preview-top {
                align-items: flex-start;
            }

            .win-dots {
                margin-top: 3px;
            }

            .kpis {
                gap: 10px;
            }

            .logo-row {
                gap: 16px;
            }

            .logo-row span {
                font-size: .95rem;
            }

            footer {
                padding: 26px 0;
            }

            .foot-inner {
                font-size: .82rem;
                justify-content: center;
                text-align: center;
            }
        }

        [data-theme="dark"] .preview,
        [data-theme="dark"] .kpi,
        [data-theme="dark"] .chart,
        [data-theme="dark"] .list,
        [data-theme="dark"] .card,
        [data-theme="dark"] .faq-item,
        [data-theme="dark"] .contact-card,
        [data-theme="dark"] .contact-info,
        [data-theme="dark"] .contact-pill,
        [data-theme="dark"] .field input,
        [data-theme="dark"] .field textarea,
        [data-theme="dark"] .field select {
            background: #0f1b17;
        }

        [data-theme="dark"] .preview,
        [data-theme="dark"] .kpi,
        [data-theme="dark"] .chart,
        [data-theme="dark"] .list,
        [data-theme="dark"] .card,
        [data-theme="dark"] .faq-item,
        [data-theme="dark"] .faq-content,
        [data-theme="dark"] .contact-card,
        [data-theme="dark"] .contact-info,
        [data-theme="dark"] .contact-pill,
        [data-theme="dark"] .field input,
        [data-theme="dark"] .field textarea,
        [data-theme="dark"] .field select,
        [data-theme="dark"] .pill,
        [data-theme="dark"] .btn-outline,
        [data-theme="dark"] footer {
            border-color: var(--line);
        }

        [data-theme="dark"] .faq-item summary,
        [data-theme="dark"] .contact-pill span,
        [data-theme="dark"] .field input,
        [data-theme="dark"] .field textarea,
        [data-theme="dark"] .field select {
            color: var(--ink);
        }

        [data-theme="dark"] .btn-outline { background: #10231c; }
        [data-theme="dark"] .btn-dark {
            background: var(--accent);
            color: #ffffff;
            border-color: transparent;
        }
        [data-theme="dark"] .btn-dark:hover {
            background: var(--accent-dark);
        }
        [data-theme="dark"] .btn-light {
            background: #f2faf6;
            color: #0b1210;
            border-color: transparent;
        }
        [data-theme="dark"] .btn-light:hover {
            background: #dcefe5;
            color: #0b1210;
        }
        [data-theme="dark"] .btn-clear {
            color: #f2faf6;
            border-color: rgba(242,250,246,.6);
            background: rgba(242,250,246,.06);
        }
        [data-theme="dark"] .btn-ghost:hover,
        [data-theme="dark"] .btn-outline:hover { background: #183229; }
    </style>
</head>
<body>
    @php
        $loginUrl = Route::has('filament.admin.auth.login')
            ? route('filament.admin.auth.login')
            : url('/admin/login');
        $registerUrl = Route::has('filament.admin.auth.register')
            ? route('filament.admin.auth.register')
            : url('/admin/register');
        $isPolicies = request()->routeIs('privacy-policy')
            || request()->routeIs('data-deletion-instructions')
            || request()->routeIs('terms-and-conditions');
    @endphp

    <header class="nav">
        <div class="container nav-inner">
            <a href="/" class="brand">
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
                    <a href="{{ $loginUrl }}" class="btn btn-outline">Log in</a>
                    <a href="{{ $registerUrl }}" class="btn btn-dark">Get started</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <span class="pill reveal"><span class="dot"></span> Personal · Family · Business · Wealth</span>
            <h1 class="reveal hero-copy" id="hero-heading">Your money, <span class="grad" id="hero-highlight">simple and clear.</span></h1>
            <p class="lede reveal delay-1 hero-copy" id="hero-caption">
                {{ config('app.name', 'Life Finance OS') }} brings every part of your financial life into one calm,
                modern dashboard — so you always know where you stand.
            </p>
            <div class="hero-actions reveal delay-1">
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-primary">Open your dashboard</a>
                @else
                    <a href="{{ $registerUrl }}" class="btn btn-primary">Create free account</a>
                    <a href="{{ $loginUrl }}" class="btn btn-outline">Log in</a>
                @endauth
            </div>
            <p class="hero-note reveal delay-1">No credit card required · Set up in minutes</p>

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
        </section>

        <section class="logos">
            <div class="container">
                <p>One place for every part of your financial life</p>
                <div class="logo-row">
                    <span>Personal</span>
                    <span>Family</span>
                    <span>Business</span>
                    <span>Wealth</span>
                    <span>Reports</span>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <div class="section-head reveal">
                    <span class="eyebrow">Everything you need</span>
                    <h2>One platform for every part of your financial life</h2>
                    <p>From daily spending to long-term wealth, every module works together.</p>
                </div>

                <div class="grid reveal delay-1">
                    <article class="card">
                        <div class="card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0v.75H4.5v-.75Z"/></svg>
                        </div>
                        <h3>Personal Finance</h3>
                        <p>Track income, expenses, debts, and savings goals with budgets that keep you on target.</p>
                    </article>

                    <article class="card">
                        <div class="card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 12 3l9.75 9M4.5 10.5V21h15V10.5"/></svg>
                        </div>
                        <h3>Family</h3>
                        <p>Manage a shared household budget, plan for children, and align finances with your spouse.</p>
                    </article>

                    <article class="card">
                        <div class="card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15v18h-15V3Zm3 4.5h3m-3 4h3m-3 4h3m4.5-8h.75m-.75 4h.75m-.75 4h.75"/></svg>
                        </div>
                        <h3>Business Finance</h3>
                        <p>Invoices, inventory, suppliers, customers, and payroll — keep your business books in order.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section" id="workflow" style="padding-top:0;">
            <div class="container">
                <div class="section-head reveal">
                    <span class="eyebrow">How it works</span>
                    <h2>From messy numbers to a clear picture</h2>
                    <p>Three simple steps to get your whole financial life in order.</p>
                </div>

                <div class="grid reveal delay-1">
                    <article class="card">
                        <div class="card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </div>
                        <h3>1. Add your accounts</h3>
                        <p>Bring in income, expenses, debts, and business records in one organised place.</p>
                    </article>
                    <article class="card">
                        <div class="card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        </div>
                        <h3>2. Stay in control</h3>
                        <p>Set budgets and goals, then let automatic tracking keep everything up to date.</p>
                    </article>
                    <article class="card">
                        <div class="card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5 8.25 8.25l3.75 3.75 6.75-6.75M21 9V4.5h-4.5"/></svg>
                        </div>
                        <h3>3. Grow with clarity</h3>
                        <p>Watch clear reports and insights turn everyday decisions into lasting wealth.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section" id="faq" style="padding-top:0;">
            <div class="container">
                <div class="section-head reveal">
                    <span class="eyebrow">FAQ</span>
                    <h2>Frequently asked questions</h2>
                    <p>Everything you need to know before getting started.</p>
                </div>

                <div class="faq-wrap reveal delay-1">
                    <details class="faq-item" open>
                        <summary>Can I manage personal, family, and business finances together?</summary>
                        <div class="faq-content">Yes. Life Finance OS is designed to unify personal, family, and business finance workflows in one place while keeping your data organized by context.</div>
                    </details>

                    <details class="faq-item">
                        <summary>How do budget purchases update the system?</summary>
                        <div class="faq-content">When an item is marked as bought, actual spending is synced to your budget totals and can create linked expense and account transaction records.</div>
                    </details>

                    <details class="faq-item">
                        <summary>Is there dark mode support?</summary>
                        <div class="faq-content">Yes. Use the theme toggle in the top navigation on the landing page to switch between light and dark mode.</div>
                    </details>

                    <details class="faq-item">
                        <summary>Can I start without entering all my data at once?</summary>
                        <div class="faq-content">Absolutely. You can begin with your key accounts and budgets, then progressively add expenses, debts, savings goals, and business records.</div>
                    </details>
                </div>
            </div>
        </section>

        <section class="section" id="contact" style="padding-top:0;">
            <div class="container">
                <div class="section-head reveal">
                    <span class="eyebrow">Contact</span>
                    <h2>Contact us</h2>
                    <p>Have questions, feedback, or need help with setup? Send us a message.</p>
                </div>

                <div class="contact-grid reveal delay-1">
                    <div class="contact-card">
                        <h3>Send a message</h3>
                        <p>Tell us a bit about what you need and our team will get back to you.</p>

                        <form class="contact-form" action="javascript:void(0)">
                            <div class="contact-row">
                                <div class="field">
                                    <label for="contact-name">Full name</label>
                                    <input id="contact-name" name="name" type="text" placeholder="Your name" required>
                                </div>
                                <div class="field">
                                    <label for="contact-email">Email</label>
                                    <input id="contact-email" name="email" type="email" placeholder="you@example.com" required>
                                </div>
                            </div>

                            <div class="contact-row">
                                <div class="field">
                                    <label for="contact-topic">Topic</label>
                                    <select id="contact-topic" name="topic">
                                        <option value="general">General inquiry</option>
                                        <option value="support">Product support</option>
                                        <option value="demo">Request a demo</option>
                                        <option value="feedback">Feedback</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="contact-phone">Phone (optional)</label>
                                    <input id="contact-phone" name="phone" type="tel" placeholder="+260 ...">
                                </div>
                            </div>

                            <div class="field">
                                <label for="contact-message">Message</label>
                                <textarea id="contact-message" name="message" placeholder="How can we help?" required></textarea>
                            </div>

                            <div class="hero-actions" style="justify-content:flex-start; margin-top:4px;">
                                <button type="submit" class="btn btn-primary">Send message</button>
                            </div>
                        </form>
                    </div>

                    <aside class="contact-info">
                        <h3>Reach us directly</h3>
                        <p>Prefer direct channels? Use any of the options below.</p>

                        <div class="contact-list">
                            <div class="contact-pill">
                                <b>Email</b>
                                <span>support@lifefinanceos.com</span>
                            </div>
                            <div class="contact-pill">
                                <b>Phone</b>
                                <span>+260 97 000 0000</span>
                            </div>
                            <div class="contact-pill">
                                <b>Business hours</b>
                                <span>Mon - Fri, 08:00 - 17:00 CAT</span>
                            </div>
                            <div class="contact-pill">
                                <b>Location</b>
                                <span>Lusaka, Zambia</span>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <section class="section" style="padding-top:0;">
            <div class="container">
                <div class="cta reveal">
                    <span class="eyebrow" style="color:#6ee7b7;">Start today</span>
                    <h2>Take control of your finances</h2>
                    <p>Join {{ config('app.name', 'Life Finance OS') }} and bring calm and clarity to your money.</p>
                    <div class="hero-actions">
                        @auth
                            <a href="{{ url('/admin') }}" class="btn btn-light">Go to your dashboard</a>
                        @else
                            <a href="{{ $registerUrl }}" class="btn btn-light">Create your account</a>
                            <a href="{{ $loginUrl }}" class="btn btn-clear">Log in</a>
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

            const savedTheme = localStorage.getItem('lf-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const initialDark = savedTheme ? savedTheme === 'dark' : prefersDark;

            if (initialDark) {
                root.setAttribute('data-theme', 'dark');
            }

            themeToggle?.addEventListener('click', () => {
                const isDark = root.getAttribute('data-theme') === 'dark';
                if (isDark) {
                    root.removeAttribute('data-theme');
                    localStorage.setItem('lf-theme', 'light');
                } else {
                    root.setAttribute('data-theme', 'dark');
                    localStorage.setItem('lf-theme', 'dark');
                }
            });

            menuToggle?.addEventListener('click', () => {
                const open = mobileMenu?.classList.toggle('open');
                menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            mobileMenu?.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.remove('open');
                    menuToggle?.setAttribute('aria-expanded', 'false');
                });
            });

            const heroHeading = document.getElementById('hero-heading');
            const heroHighlight = document.getElementById('hero-highlight');
            const heroCaption = document.getElementById('hero-caption');

            const heroSlides = [
                {
                    lead: 'Your money,',
                    highlight: 'simple and clear.',
                    caption: "{{ config('app.name', 'Life Finance OS') }} brings every part of your financial life into one calm, modern dashboard — so you always know where you stand.",
                },
                {
                    lead: 'Plan better,',
                    highlight: 'spend with confidence.',
                    caption: 'Build budgets, track real spending, and stay on top of every kwacha in real time.',
                },
                {
                    lead: 'Start now,',
                    highlight: 'free for all.',
                    caption: 'Enjoy full access at no cost for everyone through Fall 2027 — launch now while the offer is live.',
                },
                {
                    lead: 'One dashboard,',
                    highlight: 'total financial control.',
                    caption: 'Personal, family, and business finances all connected in one place.',
                },
                {
                    lead: 'Track progress,',
                    highlight: 'grow with clarity.',
                    caption: 'See what is budgeted versus what is spent, then act faster with real-time insights.',
                },
                {
                    lead: 'Built for Zambia,',
                    highlight: 'ready for your future.',
                    caption: 'From daily cash flow to long-term goals, manage everything in ZMW with confidence.',
                },
            ];

            const promoSlideIndex = 2;
            const heroSlideSequence = [0, 1, promoSlideIndex, 3, promoSlideIndex, 4, 5, promoSlideIndex];
            let heroSlidePointer = 0;
            const canRotateHero = heroHeading && heroHighlight && heroCaption && heroSlideSequence.length > 1;

            const renderHeroSlide = (index) => {
                const slide = heroSlides[index];
                if (!slide) {
                    return;
                }

                heroHeading.classList.add('is-changing');
                heroCaption.classList.add('is-changing');

                window.setTimeout(() => {
                    heroHeading.innerHTML = `${slide.lead} <span class="grad" id="hero-highlight">${slide.highlight}</span>`;
                    heroCaption.textContent = slide.caption;
                    heroHeading.classList.remove('is-changing');
                    heroCaption.classList.remove('is-changing');
                }, 170);
            };

            if (canRotateHero) {
                window.setInterval(() => {
                    heroSlidePointer = (heroSlidePointer + 1) % heroSlideSequence.length;
                    renderHeroSlide(heroSlideSequence[heroSlidePointer]);
                }, 4600);
            }
        })();
    </script>
</body>
</html>
