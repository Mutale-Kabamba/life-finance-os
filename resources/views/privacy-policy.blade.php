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
            <span class="eyebrow">Privacy Terms</span>
            <h2>Your data and your rights</h2>
            <p>Last updated: {{ now()->format('F j, Y') }}</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card">
            <h3>1. Information We Collect</h3>
            <ul>
                <li>Account details such as your name, email address, and login provider information.</li>
                <li>Financial and profile data that you enter while using the app.</li>
                <li>Technical data such as browser information and usage logs for security and diagnostics.</li>
            </ul>
            </article>

            <article class="card">
            <h3>2. How We Use Information</h3>
            <ul>
                <li>To provide and improve app features.</li>
                <li>To secure accounts and prevent abuse.</li>
                <li>To communicate important account and service updates.</li>
            </ul>
            </article>

            <article class="card">
            <h3>3. Data Sharing</h3>
            <p>We do not sell your personal data. Data may be shared with service providers only when necessary to operate the app (for example, authentication or infrastructure services).</p>
            </article>

            <article class="card">
            <h3>4. Data Retention</h3>
            <p>We retain your data for as long as your account is active or as required by law. You can request deletion of your account data.</p>
            </article>

            <article class="card">
            <h3>5. Your Rights</h3>
            <p>You may request access, correction, or deletion of your personal data by contacting us.</p>
            </article>

            <article class="card">
            <h3>6. Contact</h3>
            <p>For privacy questions, contact us at <a href="mailto:support@oristudio.com">support@oristudio.com</a>.</p>
            </article>
        </div>
    </section>
@endsection
