@extends('layouts.marketing')

@section('title', 'Data Deletion Instructions - ' . config('app.name', 'Life Finance OS'))
@section('hero_pill', 'Policy')
@section('hero_title_prefix', 'Data deletion,')
@section('hero_title_highlight', 'step by step.')
@section('hero_caption', 'If you want your account and related records removed, follow this process.')
@section('hero_show_preview', '0')

@section('content')
    <section class="section" style="padding-top: 8px;">
        <div class="section-head reveal">
            <span class="eyebrow">Request Process</span>
            <h2>How to delete your account data</h2>
            <p>Follow these steps and we will process your request securely.</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card">
            <h3>1. Email Request</h3>
            <p>Send a deletion request to <a href="mailto:support@oristudio.com">support@oristudio.com</a> from your registered email address.</p>
            </article>
            <article class="card">
            <h3>2. Verification</h3>
            <p>We may ask you to verify ownership of the account before deletion is processed.</p>
            </article>
            <article class="card">
            <h3>3. Processing Timeline</h3>
            <p>Requests are typically processed within 7 to 14 business days, subject to legal retention requirements.</p>
            </article>
            <article class="card">
            <h3>4. What Gets Deleted</h3>
            <p>Profile information and in-app financial records linked to your account are removed, except records we must retain by law.</p>
            </article>
        </div>
    </section>
@endsection
