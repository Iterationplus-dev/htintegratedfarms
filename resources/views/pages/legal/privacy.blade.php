<x-legal-page title="Privacy Policy" updated="30 October 2026" description="How H & T Integrated Farms Limited collects, uses and protects personal data submitted through this website.">
    <h2>Who we are</h2>
    <p>This website is operated by {{ $site->get('company_name') }} (“H &amp; T”, “we”), an agricultural company based in Ogun State, Nigeria. We are the data controller for personal data collected through this website. You can contact us about privacy at <a href="mailto:{{ $site->get('email_info') }}">{{ $site->get('email_info') }}</a>.</p>

    <h2>What we collect</h2>
    <ul>
        <li><strong>Enquiries:</strong> when you send a message, request a quote or submit a partnership inquiry we collect the details you enter — name, organisation, phone, email, delivery location, required date, products and quantities, and your message.</li>
        <li><strong>Saved quote requests:</strong> if you choose “Save and continue later”, we store the form contents and the email address you give us so we can send you a link to resume.</li>
        <li><strong>Stock alerts:</strong> if you ask to be notified when a product is available, we store your email address against that product.</li>
        <li><strong>Technical data:</strong> to protect our forms from abuse we record a one-way hashed form of your IP address and your browser’s user-agent with each enquiry. Our servers keep standard access logs for security.</li>
        <li><strong>Analytics:</strong> only if you consent, we use an analytics service to understand which pages are visited. See our <a href="{{ route('cookies') }}">Cookie Policy</a>.</li>
    </ul>

    <h2>Why we use it</h2>
    <p>We use enquiry data to reply to you, prepare quotes, arrange supply or farm visits and evaluate partnership proposals. Our lawful basis is taking steps at your request before entering into a contract, and our legitimate interest in running and protecting our business. Analytics is based on your consent, which you can withdraw at any time.</p>

    <h2>Who we share it with</h2>
    <p>Enquiries are seen by our sales and management staff. We use service providers to host this website and send email; they process data only on our instructions. We do not sell personal data. If form protection is enabled, Cloudflare Turnstile processes limited technical data to tell people from bots.</p>

    <h2>How long we keep it</h2>
    <p>Saved quote drafts are deleted automatically after {{ config('enquiries.draft_lifetime_days') }} days. Enquiries are kept for as long as needed to deal with them and any resulting business relationship, and then reviewed and deleted. Stock alerts are kept until the alert is sent.</p>

    <h2>Your rights</h2>
    <p>Under the Nigeria Data Protection Act 2023 you may ask for access to, correction of, or deletion of your personal data, object to or restrict processing, and withdraw consent. Contact us at the address above. You may also complain to the Nigeria Data Protection Commission.</p>

    <h2>Changes</h2>
    <p>We may update this policy. The date at the top shows when it last changed.</p>
</x-legal-page>
