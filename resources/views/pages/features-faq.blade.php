@extends('layouts.marketing')

@section('title', 'Features & FAQ - ' . config('app.name', 'Life Finance OS'))
@section('hero_pill', 'Product Snapshot')
@section('hero_title_prefix', 'Clear outcomes,')
@section('hero_title_highlight', 'practical tools.')
@section('hero_caption', 'Everything connected so you can plan better, operate smarter, and grow with confidence.')

@section('content')
    <section class="section" style="padding-top: 8px;">
        <div class="section-head reveal">
            <span class="eyebrow">Features</span>
            <h2>Business Finance Software for Growing Businesses and Households</h2>
            <p>Each capability is designed to improve your financial life, not just add another dashboard.</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0v.75H4.5v-.75Z"/></svg>
                </div>
            <h3>Everything connected.</h3>
            <p>Personal finance, family planning, business operations, and wealth tracking work together instead of living in separate apps.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15v18h-15V3Zm3 4.5h3m-3 4h3m-3 4h3m4.5-8h.75m-.75 4h.75m-.75 4h.75"/></svg>
                </div>
            <h3>See your financial health instantly.</h3>
            <p>Live dashboards show cash flow, performance, and obligations so you can act quickly and confidently.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 12 3l9.75 9M4.5 10.5V21h15V10.5"/></svg>
                </div>
            <h3>From quotation to payment.</h3>
            <p>Run document workflows from quote to invoice to receipt without losing context or creating manual rework.</p>
            </article>
            <article class="card">
                <div class="card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5 8.25 8.25l3.75 3.75 6.75-6.75M21 9V4.5h-4.5"/></svg>
                </div>
            <h3>Watch your money grow over time.</h3>
            <p>Track assets and investments with a long-term view so you can measure progress and adjust strategy early.</p>
            </article>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-head reveal">
            <span class="eyebrow">Benefits</span>
            <h2>What you gain day to day</h2>
            <p>Built to reduce anxiety and increase confidence through practical financial routines.</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card"><h3>One financial picture</h3><p>See the full story across income, spending, operations, and growth.</p></article>
            <article class="card"><h3>Less manual work</h3><p>Use connected workflows instead of repeating tasks across multiple apps.</p></article>
            <article class="card"><h3>Better planning</h3><p>Make realistic plans based on live cash flow and obligations.</p></article>
            <article class="card"><h3>Clear dashboards</h3><p>Understand your position in seconds with practical KPI views.</p></article>
            <article class="card"><h3>Organized records</h3><p>Keep personal and business finance data structured and searchable.</p></article>
            <article class="card"><h3>Financial confidence</h3><p>Act intentionally with data you trust, not guesswork.</p></article>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-head reveal">
            <span class="eyebrow">Trust</span>
            <h2>Built with practical safeguards</h2>
            <p>Security and reliability features designed for real financial workflows.</p>
        </div>

        <div class="grid reveal delay-1">
            <article class="card"><h3>Secure authentication</h3><p>Login protection designed for financial accounts.</p></article>
            <article class="card"><h3>Encrypted data</h3><p>Sensitive account information is handled with modern encryption practices.</p></article>
            <article class="card"><h3>Email verification</h3><p>Identity checks help protect account ownership.</p></article>
            <article class="card"><h3>Role-based access</h3><p>Manage access by context for clearer control.</p></article>
            <article class="card"><h3>Automatic reminders</h3><p>Stay ahead of obligations and deadlines.</p></article>
            <article class="card"><h3>Export your data anytime</h3><p>Keep control of your records when you need them.</p></article>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="section-head reveal">
            <span class="eyebrow">FAQ</span>
            <h2>Frequently asked questions</h2>
            <p>Practical answers to common onboarding and day-to-day questions.</p>
        </div>

        <div class="faq-wrap reveal delay-1">
            <details class="faq-item" open>
                <summary>Can I manage personal and business finances separately?</summary>
                <div class="faq-content">Yes. You can keep them separated by context while still having one platform and one login.</div>
            </details>
            <details class="faq-item">
                <summary>Can multiple family members use one account?</summary>
                <div class="faq-content">Yes. Family workflows can be coordinated in one platform with clear responsibilities.</div>
            </details>
            <details class="faq-item">
                <summary>Is my financial data secure?</summary>
                <div class="faq-content">The platform uses secure authentication, verification controls, and privacy-focused architecture.</div>
            </details>
            <details class="faq-item">
                <summary>Can I import existing records?</summary>
                <div class="faq-content">You can start with your current records and continue building from there at your own pace.</div>
            </details>
            <details class="faq-item">
                <summary>Can I export reports?</summary>
                <div class="faq-content">Yes. You can export key records and reports when needed.</div>
            </details>
            <details class="faq-item">
                <summary>Do I need accounting knowledge?</summary>
                <div class="faq-content">No. The workflows are designed to be practical and understandable for everyday users and business owners.</div>
            </details>
            <details class="faq-item">
                <summary>Can I use only one module?</summary>
                <div class="faq-content">Yes. Start with one area, then activate additional modules as your needs grow.</div>
            </details>
            <details class="faq-item">
                <summary>Can I manage multiple businesses?</summary>
                <div class="faq-content">Yes. Business workflows are structured so you can track operations with clarity.</div>
            </details>
            <details class="faq-item">
                <summary>Can I use the platform on mobile?</summary>
                <div class="faq-content">Yes. The interface is responsive and designed to work across desktop and mobile screens.</div>
            </details>
            <details class="faq-item">
                <summary>How often should I update my finances?</summary>
                <div class="faq-content">A short daily update and one weekly review is enough for most users to stay in control.</div>
            </details>
            <details class="faq-item">
                <summary>Will I receive reminders?</summary>
                <div class="faq-content">Yes. Reminder workflows help you stay ahead of due dates and recurring obligations.</div>
            </details>
            <details class="faq-item">
                <summary>What happens if I forget my password?</summary>
                <div class="faq-content">Use the password reset flow on the login page to securely recover account access.</div>
            </details>
            <details class="faq-item">
                <summary>Is there a free plan?</summary>
                <div class="faq-content">Yes. You can create a free account and start organizing your finances right away.</div>
            </details>
            <details class="faq-item">
                <summary>Can I delete my data?</summary>
                <div class="faq-content">Yes. Data deletion instructions are available in the policy pages and support can help if needed.</div>
            </details>
            <details class="faq-item">
                <summary>How do I contact support?</summary>
                <div class="faq-content">Use the contact page or email support to get help from the team.</div>
            </details>
        </div>
    </section>
@endsection
