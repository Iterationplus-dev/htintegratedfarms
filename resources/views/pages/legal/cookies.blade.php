<x-legal-page title="Cookie Policy" updated="30 October 2026" description="The cookies the H & T Integrated Farms website uses and how to change your choice.">
    <h2>What cookies are</h2>
    <p>Cookies are small files a website stores in your browser. We use as few as possible and ask before using anything that is not essential.</p>

    <h2>Essential cookies (always on)</h2>
    <ul>
        <li><strong>Session and security cookies</strong> keep the site working and protect our forms against cross-site request forgery.</li>
        <li><strong>ht_consent</strong> remembers your cookie choice for 180 days.</li>
        <li>If form protection is enabled, <strong>Cloudflare Turnstile</strong> may set a cookie to distinguish people from bots when you submit a form.</li>
    </ul>

    <h2>Analytics cookies (only with your consent)</h2>
    <p>If you select “Accept all” or enable analytics in your preferences, we load an analytics service to count page visits and understand how the site is used. It is not loaded if you reject non-essential cookies.</p>

    <h2>Maps</h2>
    <p>The map on our Contact page is only loaded when you choose “Show map”. Google Maps may then set its own cookies.</p>

    <h2>Changing your choice</h2>
    <p>You can change your choice at any time:</p>
    <p><button type="button" x-data @click="$store.consent.openPreferences()" class="btn btn-outline">Open cookie settings</button></p>
    <p>You can also delete cookies in your browser settings.</p>
</x-legal-page>
