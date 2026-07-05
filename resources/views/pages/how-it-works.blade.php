@extends('layouts.marketing')

@section('title', 'How It Works - ' . config('app.name', 'Life Finance OS'))
@section('hero_pill', 'Platform Workflow')
@section('hero_title_prefix', 'Simple process,')
@section('hero_title_highlight', 'clear outcomes.')
@section('hero_caption', 'Set up once, track consistently, and make better financial decisions with confidence.')
@section('hero_show_preview', '0')

@section('content')
    <section class="section" style="padding-top: 8px;">
        <div class="section-head reveal">
            <span class="eyebrow">How It Works</span>
            <h2>From setup to confident money decisions</h2>
            <p>Follow this workflow to move from scattered data to clear control.</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>
            <h3>Step 1: Set up your profile</h3>
            <p>Create your account and configure the finance modules relevant to you.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            <h3>Step 2: Add your financial data</h3>
            <p>Track income, expenses, accounts, debts, assets, investments, and business documents.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5 8.25 8.25l3.75 3.75 6.75-6.75M21 9V4.5h-4.5"/></svg>
                </div>
            <h3>Step 3: Monitor dashboards</h3>
            <p>Use real-time dashboards and smart filters to review KPIs by period and horizon.</p>
            </article>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-head reveal">
            <span class="eyebrow">Execution</span>
            <h2>Keep momentum after setup</h2>
            <p>Consistency is what turns tracking into better outcomes.</p>
        </div>

        <div class="faq-wrap reveal delay-1">
            <details class="faq-item" open>
                <summary>Step 4: Take action</summary>
                <div class="faq-content">Use insights to improve budgeting, collections, debt management, and investment decisions.</div>
            </details>
            <details class="faq-item">
                <summary>How often should I update records?</summary>
                <div class="faq-content">A quick daily update and a deeper weekly review keeps your numbers accurate and decisions timely.</div>
            </details>
            <details class="faq-item">
                <summary>What should I check first each week?</summary>
                <div class="faq-content">Start with cash flow and budget variance, then check debt, receivables, and savings/investment progress.</div>
            </details>
        </div>
    </section>
@endsection
