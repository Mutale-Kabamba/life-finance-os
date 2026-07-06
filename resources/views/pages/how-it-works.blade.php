@extends('layouts.marketing')

@section('title', 'How It Works - ' . config('app.name', 'Life Finance OS'))
@section('hero_pill', 'Platform Workflow')
@section('hero_title_prefix', 'Three simple steps,')
@section('hero_title_highlight', 'financial control.')
@section('hero_caption', 'Move from scattered records and reactive decisions to one clear, consistent financial system.')
@section('hero_show_preview', '0')

@section('content')
    <section class="section" style="padding-top: 8px;">
        <div class="section-head reveal">
            <span class="eyebrow">How It Works</span>
            <h2>From complexity to clarity</h2>
            <p>Follow this simple process to reduce stress, stay organized, and make confident money decisions.</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>
            <h3>Step 1: Bring everything into one place.</h3>
            <p>Import or record your income, expenses, accounts, debts, assets, and business activities.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            <h3>Step 2: Stay on top of your finances.</h3>
            <p>Track each transaction, obligation, and milestone with clear dashboards and reminders.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5 8.25 8.25l3.75 3.75 6.75-6.75M21 9V4.5h-4.5"/></svg>
                </div>
            <h3>Step 3: Make smarter decisions.</h3>
            <p>Use reports and insights to choose your next action with confidence and consistency.</p>
            </article>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-head reveal">
            <span class="eyebrow">Execution</span>
            <h2>Turn a routine into real progress</h2>
            <p>Small, regular updates create better planning, fewer surprises, and stronger long-term outcomes.</p>
        </div>

        <div class="faq-wrap reveal delay-1">
            <details class="faq-item" open>
                <summary>What should I do after setup?</summary>
                <div class="faq-content">Run a quick weekly review of cash flow, budgets, debts, receivables, and upcoming due dates.</div>
            </details>
            <details class="faq-item">
                <summary>How often should I update records?</summary>
                <div class="faq-content">A short daily update and one focused weekly review keeps your data accurate and decisions timely.</div>
            </details>
            <details class="faq-item">
                <summary>What should I check first each week?</summary>
                <div class="faq-content">Start with cash flow and budget variance, then review obligations, collections, and growth priorities.</div>
            </details>
        </div>
    </section>
@endsection
