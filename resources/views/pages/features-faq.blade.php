@extends('layouts.marketing')

@section('title', 'Features & FAQ - ' . config('app.name', 'Life Finance OS'))
@section('hero_pill', 'Product Snapshot')
@section('hero_title_prefix', 'Powerful features,')
@section('hero_title_highlight', 'practical answers.')
@section('hero_caption', 'Everything you need to run personal and business finance with confidence.')

@section('content')
    <section class="section" style="padding-top: 8px;">
        <div class="section-head reveal">
            <span class="eyebrow">Everything You Need</span>
            <h2>Core capabilities in one platform</h2>
            <p>Use one connected workflow for personal, family, business, and wealth operations.</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0v.75H4.5v-.75Z"/></svg>
                </div>
            <h3>Unified Modules</h3>
            <p>Personal, family, business, and wealth modules in one application.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15v18h-15V3Zm3 4.5h3m-3 4h3m-3 4h3m4.5-8h.75m-.75 4h.75m-.75 4h.75"/></svg>
                </div>
            <h3>Live Dashboards</h3>
            <p>Period-based filters and compact KPI cards update in real time.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 12 3l9.75 9M4.5 10.5V21h15V10.5"/></svg>
                </div>
            <h3>Business Document Flow</h3>
            <p>Manage quotation to invoice to receipt workflows in one place.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5 8.25 8.25l3.75 3.75 6.75-6.75M21 9V4.5h-4.5"/></svg>
                </div>
            <h3>Wealth Tracking</h3>
            <p>Track assets and investments with practical horizon filters.</p>
            </article>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-head reveal">
            <span class="eyebrow">FAQ</span>
            <h2>Frequently asked questions</h2>
            <p>Everything you need to know before getting started.</p>
        </div>

        <div class="faq-wrap reveal delay-1">
            <details class="faq-item" open>
                <summary>Can I use only one module?</summary>
                <div class="faq-content">Yes. You can start with one module and enable more later.</div>
            </details>
            <details class="faq-item">
                <summary>Is my data private?</summary>
                <div class="faq-content">Yes. Access is restricted to your authenticated account and configured permissions.</div>
            </details>
            <details class="faq-item">
                <summary>Can I sign in with social accounts?</summary>
                <div class="faq-content">Yes. Supported providers include Google, Facebook, X, LinkedIn OpenID, and GitHub.</div>
            </details>
        </div>
    </section>
@endsection
