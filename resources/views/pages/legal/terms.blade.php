<x-legal-page title="Terms & Conditions" updated="30 October 2026" description="Terms for using the H & T Integrated Farms Limited website.">
    <h2>About these terms</h2>
    <p>These terms apply to your use of this website, operated by {{ $site->get('company_name') }}. By using the site you accept them.</p>

    <h2>Product information and quotes</h2>
    <p>Products, availability and specifications shown on this website are for information only and may change with the season and production cycle. Submitting a quote request or order inquiry does not create a contract. Prices, quantities, delivery dates and terms are confirmed in writing by our sales team, and a sale is only agreed when we both accept those written terms.</p>

    <h2>Figures and descriptions</h2>
    <p>Production figures and descriptions of our operations are given in good faith and may be updated as the farm develops.</p>

    <h2>Using this website</h2>
    <p>You must not misuse the website, including by submitting false information, attempting to gain unauthorised access, or interfering with its operation. We may block submissions that appear automated or abusive.</p>

    <h2>Content</h2>
    <p>Text, photographs and the H &amp; T name and logo on this website belong to us or our licensors. You may not reuse them without permission, except to share links to our pages.</p>

    <h2>Liability</h2>
    <p>We work to keep the website accurate and available but cannot guarantee it will always be complete, current or uninterrupted. Nothing in these terms limits liability that cannot be limited by law.</p>

    <h2>Governing law</h2>
    <p>These terms are governed by the laws of the Federal Republic of Nigeria.</p>

    <h2>Contact</h2>
    <p>Questions about these terms: <a href="mailto:{{ $site->get('email_info') }}">{{ $site->get('email_info') }}</a> or {{ $site->get('phone_primary') }}.</p>
</x-legal-page>
