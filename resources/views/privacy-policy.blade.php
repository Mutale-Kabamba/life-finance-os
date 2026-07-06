@extends('layouts.marketing')

@section('title', 'Privacy Policy - ' . config('app.name', 'Life Finance OS'))
@section('hero_pill', 'Policy')
@section('hero_title_prefix', 'Privacy,')
@section('hero_title_highlight', 'explained clearly.')
@section('hero_caption', 'Understand what we collect, why we collect it, and how we protect your information.')
@section('hero_show_preview', '0')

@section('content')
    <section class="section" style="padding-top: 8px;">
        <div class="section-head reveal">
            <span class="eyebrow">Privacy Overview</span>
            <h2>Your data, your rights, and our responsibility</h2>
            <p>Last updated: {{ now()->format('F j, Y') }}</p>
        </div>

        <div class="faq-wrap reveal delay-1">
            <details class="faq-item" open>
                <summary>Our commitment to your privacy</summary>
                <div class="faq-content">We design Life Finance OS to help you manage sensitive financial information with clarity and care. Privacy is a core part of product decisions, not an afterthought.</div>
            </details>

            <details class="faq-item">
                <summary>Information we collect</summary>
                <div class="faq-content">We collect account details such as your name, email, and authentication provider, plus the financial records you choose to enter. We also collect limited technical data for security, reliability, and diagnostics.</div>
            </details>

            <details class="faq-item">
                <summary>Why we collect it</summary>
                <div class="faq-content">We use your information to provide core app functionality, maintain account security, improve product quality, and communicate important account or service updates.</div>
            </details>

            <details class="faq-item">
                <summary>How we protect your data</summary>
                <div class="faq-content">We apply practical safeguards such as secure authentication flows, account verification, and controlled access patterns to reduce unauthorized access risk.</div>
            </details>

            <details class="faq-item">
                <summary>Data sharing</summary>
                <div class="faq-content">We do not sell your personal data. We may share data only with service providers that help operate the platform, such as authentication, email, or infrastructure partners.</div>
            </details>

            <details class="faq-item">
                <summary>Your rights</summary>
                <div class="faq-content">You can request access, correction, or deletion of your personal data. We also provide policy pages that explain account and data deletion steps.</div>
            </details>

            <details class="faq-item">
                <summary>Data retention</summary>
                <div class="faq-content">We retain account data for as long as your account is active or as needed for legal and operational requirements. If you request deletion, we process it according to applicable retention obligations.</div>
            </details>

            <details class="faq-item">
                <summary>Contact us</summary>
                <div class="faq-content">For privacy-related questions, email <a href="mailto:support@oristudio.com">support@oristudio.com</a>.</div>
            </details>
        </div>
    </section>
@endsection
