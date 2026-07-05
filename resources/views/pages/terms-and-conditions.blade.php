@extends('layouts.marketing')

@section('title', 'Terms & Conditions - ' . config('app.name', 'Life Finance OS'))
@section('hero_pill', 'Policy')
@section('hero_title_prefix', 'Terms of use,')
@section('hero_title_highlight', 'made simple.')
@section('hero_caption', 'Know the responsibilities and expectations when using the platform.')
@section('hero_show_preview', '0')

@section('content')
    <section class="section" style="padding-top: 8px;">
        <div class="section-head reveal">
            <span class="eyebrow">Usage Terms</span>
            <h2>Terms that govern platform use</h2>
            <p>These terms define responsibilities for secure and fair use.</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card">
            <h3>1. Use of Service</h3>
            <p>You agree to use the platform lawfully and not misuse system functionality or access controls.</p>
            </article>
            <article class="card">
            <h3>2. Account Responsibility</h3>
            <p>You are responsible for maintaining the confidentiality of your login credentials and account activity.</p>
            </article>
            <article class="card">
            <h3>3. Data Accuracy</h3>
            <p>You are responsible for the accuracy of information entered into the platform.</p>
            </article>
            <article class="card">
            <h3>4. Availability</h3>
            <p>We aim to provide reliable access but do not guarantee uninterrupted availability at all times.</p>
            </article>
            <article class="card">
            <h3>5. Contact</h3>
            <p>For questions regarding these terms, contact <a href="mailto:support@oristudio.com">support@oristudio.com</a>.</p>
            </article>
        </div>
    </section>
@endsection
