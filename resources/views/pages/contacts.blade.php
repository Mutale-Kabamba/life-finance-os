@extends('layouts.marketing')

@section('title', 'Contacts - ' . config('app.name', 'Life Finance OS'))
@section('hero_pill', 'Get In Touch')
@section('hero_title_prefix', 'Questions?')
@section('hero_title_highlight', 'We are here to help.')
@section('hero_caption', 'Whether you need help getting started, have product questions, or want to discuss partnerships, our team is ready to assist.')
@section('hero_show_preview', '0')

@section('content')
    <section class="section" style="padding-top: 8px;">
        <div class="section-head reveal">
            <span class="eyebrow">Contact</span>
            <h2>Questions? We are here to help.</h2>
            <p>Whether you need onboarding support, product guidance, or partnership information, we are ready to assist.</p>
        </div>

        <div class="contact-grid reveal delay-1">
            <div class="contact-card">
                <h3>Send a message</h3>
                <p>Tell us what you need and we will point you to the best next step.</p>

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

                    <button type="submit" class="btn btn-primary">Send message</button>
                </form>

                <p style="margin-top: 14px; color: var(--muted);">We typically respond within one business day.</p>
            </div>

            <div class="contact-info">
                <h3>Reach us directly</h3>
                <p>Prefer direct channels? Use any of the options below.</p>

                <div class="contact-pill">
                    <span>Email</span>
                    <strong><a href="mailto:support@oristudio.com">support@oristudio.com</a></strong>
                </div>
                <div class="contact-pill">
                    <span>Phone</span>
                    <strong>+260 97 000 0000</strong>
                </div>
                <div class="contact-pill">
                    <span>Business hours</span>
                    <strong>Mon - Fri, 08:00 - 17:00 CAT</strong>
                </div>
                <div class="contact-pill">
                    <span>Location</span>
                    <strong>Lusaka, Zambia</strong>
                </div>
            </div>
        </div>
    </section>
@endsection
