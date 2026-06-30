<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Life Finance OS — one place to manage your personal, family, and business finances and build lasting wealth.">

    <title>{{ config('app.name', 'Life Finance OS') }} — Your money, all in one place</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <style>
        :root {
            --bg: #f7faf9;
            --surface: #ffffff;
            --ink: #0f1f1a;
            --muted: #5b6b64;
            --line: #e3ece8;
            --brand: #059669;
            --brand-dark: #047857;
            --brand-soft: #ecfdf5;
            --accent: #0ea5e9;
            --shadow: 0 10px 30px -12px rgba(4, 120, 87, .25);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }

        .container { width: 100%; max-width: 1120px; margin-inline: auto; padding-inline: 24px; }

        /* Nav */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(247, 250, 249, .8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--line);
        }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 68px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.05rem; letter-spacing: -.01em; }
        .brand-mark {
            width: 34px; height: 34px; border-radius: 9px;
            background: linear-gradient(135deg, var(--brand), var(--accent));
            display: grid; place-items: center; color: #fff; font-weight: 700;
            box-shadow: var(--shadow);
        }
        .nav-actions { display: flex; align-items: center; gap: 8px; }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 18px; border-radius: 10px;
            font-weight: 600; font-size: .92rem; cursor: pointer;
            border: 1px solid transparent; transition: .18s ease;
            white-space: nowrap;
        }
        .btn-ghost { color: var(--ink); }
        .btn-ghost:hover { background: var(--brand-soft); color: var(--brand-dark); }
        .btn-primary { background: var(--brand); color: #fff; box-shadow: var(--shadow); }
        .btn-primary:hover { background: var(--brand-dark); transform: translateY(-1px); }
        .btn-outline { border-color: var(--line); color: var(--ink); background: var(--surface); }
        .btn-outline:hover { border-color: var(--brand); color: var(--brand-dark); }

        /* Hero */
        .hero { padding: 84px 0 64px; }
        .pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--brand-soft); color: var(--brand-dark);
            border: 1px solid #c9efe0; border-radius: 999px;
            padding: 6px 14px; font-size: .82rem; font-weight: 600; margin-bottom: 22px;
        }
        .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--brand); }
        h1 {
            font-size: clamp(2.2rem, 5vw, 3.6rem); line-height: 1.05;
            letter-spacing: -.03em; font-weight: 700; max-width: 14ch;
        }
        h1 .grad {
            background: linear-gradient(120deg, var(--brand), var(--accent));
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .lede { margin-top: 20px; font-size: 1.12rem; color: var(--muted); max-width: 52ch; }
        .hero-actions { margin-top: 30px; display: flex; flex-wrap: wrap; gap: 12px; }
        .hero-note { margin-top: 16px; font-size: .85rem; color: var(--muted); }

        .stats { margin-top: 56px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 560px; }
        .stat-num { font-size: 1.7rem; font-weight: 700; letter-spacing: -.02em; }
        .stat-lbl { font-size: .85rem; color: var(--muted); }

        /* Features */
        .section { padding: 64px 0; }
        .section-head { max-width: 60ch; margin-bottom: 40px; }
        .eyebrow { color: var(--brand); font-weight: 700; font-size: .82rem; letter-spacing: .08em; text-transform: uppercase; }
        h2 { font-size: clamp(1.7rem, 3.5vw, 2.4rem); letter-spacing: -.02em; margin-top: 10px; }
        .section-head p { margin-top: 12px; color: var(--muted); font-size: 1.05rem; }

        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .card {
            background: var(--surface); border: 1px solid var(--line);
            border-radius: 16px; padding: 26px 22px; transition: .2s ease;
        }
        .card:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: #c9efe0; }
        .card-icon {
            width: 46px; height: 46px; border-radius: 12px; background: var(--brand-soft);
            display: grid; place-items: center; margin-bottom: 16px;
        }
        .card-icon svg { width: 24px; height: 24px; stroke: var(--brand-dark); }
        .card h3 { font-size: 1.08rem; font-weight: 600; }
        .card p { margin-top: 8px; color: var(--muted); font-size: .92rem; }

        /* CTA */
        .cta {
            background: linear-gradient(135deg, var(--brand-dark), #065f46);
            border-radius: 24px; padding: 56px 40px; text-align: center; color: #fff;
            box-shadow: var(--shadow);
        }
        .cta h2 { color: #fff; }
        .cta p { margin-top: 12px; color: #d1fae5; font-size: 1.05rem; }
        .cta .hero-actions { justify-content: center; margin-top: 26px; }
        .btn-light { background: #fff; color: var(--brand-dark); }
        .btn-light:hover { background: #ecfdf5; transform: translateY(-1px); }
        .btn-clear { border-color: rgba(255,255,255,.4); color: #fff; }
        .btn-clear:hover { background: rgba(255,255,255,.12); }

        /* Footer */
        footer { border-top: 1px solid var(--line); padding: 32px 0; margin-top: 64px; }
        .foot-inner { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; color: var(--muted); font-size: .88rem; }

        @media (max-width: 760px) {
            .grid { grid-template-columns: repeat(2, 1fr); }
            .stats { grid-template-columns: repeat(3, 1fr); gap: 14px; }
            .nav .btn-ghost { display: none; }
            .cta { padding: 40px 22px; }
        }
        @media (max-width: 440px) {
            .grid { grid-template-columns: 1fr; }
        }
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
    @endphp

    <header class="nav">
        <div class="container nav-inner">
            <a href="/" class="brand">
                <span class="brand-mark">₵</span>
                {{ config('app.name', 'Life Finance OS') }}
            </a>
            <nav class="nav-actions">
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-primary">Go to dashboard</a>
                @else
                    <a href="{{ $loginUrl }}" class="btn btn-ghost">Log in</a>
                    <a href="{{ $registerUrl }}" class="btn btn-primary">Get started</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <span class="pill"><span class="dot"></span> Personal · Family · Business · Wealth</span>
                <h1>Your money, <span class="grad">all in one place</span>.</h1>
                <p class="lede">
                    {{ config('app.name', 'Life Finance OS') }} is the operating system for your financial life —
                    track income and expenses, manage your family budget, run your business books, and
                    grow your investments from a single, clear dashboard.
                </p>
                <div class="hero-actions">
                    @auth
                        <a href="{{ url('/admin') }}" class="btn btn-primary">Open your dashboard</a>
                    @else
                        <a href="{{ $registerUrl }}" class="btn btn-primary">Create free account</a>
                        <a href="{{ $loginUrl }}" class="btn btn-outline">I already have an account</a>
                    @endauth
                </div>
                <p class="hero-note">No credit card required · Set up in minutes</p>

                <div class="stats">
                    <div>
                        <div class="stat-num">4-in-1</div>
                        <div class="stat-lbl">Finance modules</div>
                    </div>
                    <div>
                        <div class="stat-num">100%</div>
                        <div class="stat-lbl">Your data, private</div>
                    </div>
                    <div>
                        <div class="stat-num">Real-time</div>
                        <div class="stat-lbl">Insights &amp; reports</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <div class="section-head">
                    <span class="eyebrow">Everything you need</span>
                    <h2>One platform for every part of your financial life</h2>
                    <p>From your daily spending to long-term wealth, every module works together so nothing slips through the cracks.</p>
                </div>

                <div class="grid">
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

                    <article class="card">
                        <div class="card-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5 8.25 8.25l3.75 3.75 6.75-6.75M21 9V4.5h-4.5M3.75 19.5h16.5"/></svg>
                        </div>
                        <h3>Wealth Building</h3>
                        <p>Monitor investments and assets, and watch your net worth grow over time with clear reports.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="cta">
                    <span class="eyebrow" style="color:#a7f3d0;">Start today</span>
                    <h2>Take control of your finances</h2>
                    <p>Join {{ config('app.name', 'Life Finance OS') }} and bring clarity to your money — personal, family, and business.</p>
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
            <span>Built with Laravel &amp; Filament.</span>
        </div>
    </footer>
</body>
</html>
