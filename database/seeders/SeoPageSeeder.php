<?php

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

class SeoPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            SeoPage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function pages(): array
    {
        return [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'meta_title' => 'Privacy Policy — AI Calculator Hub',
                'meta_description' => 'How AI Calculator Hub collects, uses and protects your data, including cookies and Google AdSense advertising.',
                'meta_keywords' => 'privacy policy, cookies, AdSense, data protection, AI Calculator Hub',
                'robots' => 'index,follow',
                'is_active' => true,
                'content' => $this->privacyContent(),
            ],
            [
                'slug' => 'terms-conditions',
                'title' => 'Terms & Conditions',
                'meta_title' => 'Terms & Conditions — AI Calculator Hub',
                'meta_description' => 'Terms that govern your use of AI Calculator Hub, including calculators, accounts, advertising and liability.',
                'meta_keywords' => 'terms and conditions, terms of use, AI Calculator Hub',
                'robots' => 'index,follow',
                'is_active' => true,
                'content' => $this->termsContent(),
            ],
            [
                'slug' => 'cookie-policy',
                'title' => 'Cookie Policy',
                'meta_title' => 'Cookie Policy — AI Calculator Hub',
                'meta_description' => 'Learn which cookies AI Calculator Hub uses, including essential, preference and advertising cookies such as Google AdSense.',
                'meta_keywords' => 'cookie policy, cookies, AdSense, consent, AI Calculator Hub',
                'robots' => 'index,follow',
                'is_active' => true,
                'content' => $this->cookieContent(),
            ],
            [
                'slug' => 'disclaimer',
                'title' => 'Disclaimer',
                'meta_title' => 'Disclaimer — AI Calculator Hub',
                'meta_description' => 'Important disclaimer: AI Calculator Hub tools are for education and estimation only — not professional financial, medical, legal or engineering advice.',
                'meta_keywords' => 'disclaimer, calculator accuracy, not professional advice, AI Calculator Hub',
                'robots' => 'index,follow',
                'is_active' => true,
                'content' => $this->disclaimerContent(),
            ],
        ];
    }

    protected function privacyContent(): string
    {
        $date = now()->format('F j, Y');

        return <<<HTML
<p class="text-muted-custom">Last updated: {$date}</p>

<p>AI Calculator Hub (“we”, “us”, “our”) operates the website and calculator tools at this domain (the “Service”). This Privacy Policy explains what information we collect, how we use it, and your choices — including how advertising partners such as Google may use cookies on our site.</p>

<h2>1. Information we collect</h2>
<ul>
    <li><strong>Account information</strong> — name, email address, optional phone number, and password when you register.</li>
    <li><strong>Usage data</strong> — calculator inputs/results linked to your account (history, favorites, saved calculations), pages visited, and approximate usage metrics.</li>
    <li><strong>Technical data</strong> — IP address, browser type, device information, referring URL, and cookie identifiers.</li>
    <li><strong>Communications</strong> — messages you send through contact forms or support.</li>
    <li><strong>Payment data</strong> — if you later subscribe to a paid plan, payment references are processed by payment providers; we do not store full card numbers on our servers.</li>
</ul>

<h2>2. How we use your information</h2>
<ul>
    <li>Provide, operate, secure and improve the Service.</li>
    <li>Authenticate accounts and prevent abuse.</li>
    <li>Respond to support requests and send important service notices.</li>
    <li>Generate AI explanations when you request them (inputs/results may be sent to configured AI providers solely to produce the explanation).</li>
    <li>Measure traffic and, where enabled, show relevant advertising.</li>
</ul>

<h2>3. Cookies and similar technologies</h2>
<p>We use cookies and local storage for essential site functions (session, CSRF protection), preferences (theme, language), and — when advertising is enabled — advertising and measurement cookies. See our <a href="/cookie-policy">Cookie Policy</a> for details and how to manage consent.</p>

<h2>4. Advertising (Google AdSense and partners)</h2>
<p>We may use Google AdSense and other advertising partners to display ads. Google and its partners may use cookies or similar technologies to:</p>
<ul>
    <li>Serve ads based on your prior visits to this site or other sites.</li>
    <li>Measure ad performance and prevent fraud.</li>
</ul>
<p>Google’s use of advertising cookies is described in Google’s policies, including how to control ad personalization: <a href="https://policies.google.com/technologies/ads" rel="noopener noreferrer" target="_blank">Google Advertising Technologies</a> and <a href="https://policies.google.com/privacy" rel="noopener noreferrer" target="_blank">Google Privacy Policy</a>. You can also visit <a href="https://adssettings.google.com" rel="noopener noreferrer" target="_blank">Google Ad Settings</a> to manage personalized ads.</p>
<p>Where required by law (for example in the EEA/UK), we request consent before enabling non-essential advertising cookies.</p>

<h2>5. Sharing of information</h2>
<p>We do not sell your personal information. We may share data with:</p>
<ul>
    <li><strong>Service providers</strong> (hosting, email, AI APIs, payments) only as needed to run the Service.</li>
    <li><strong>Advertising partners</strong> such as Google when ads are enabled, as described above.</li>
    <li><strong>Legal authorities</strong> when required by law or to protect our rights, users, or the public.</li>
</ul>

<h2>6. Data retention</h2>
<p>We retain account and saved data while your account is active. Logs and history may be kept for a limited period for security and improvement. You may delete saved items, clear history, or request account deletion from your profile settings.</p>

<h2>7. Your rights</h2>
<p>Depending on your location, you may have rights to access, correct, export, or delete personal data, and to withdraw cookie consent. Contact us via the <a href="/contact">Contact</a> page. You can update profile details and delete your account from account settings.</p>

<h2>8. Children’s privacy</h2>
<p>The Service is not directed to children under 13 (or the minimum age required in your country). We do not knowingly collect personal information from children.</p>

<h2>9. International transfers</h2>
<p>Your information may be processed in countries where we or our providers (including Google) operate. We take reasonable steps to protect data in accordance with this policy.</p>

<h2>10. Changes</h2>
<p>We may update this Privacy Policy from time to time. The “Last updated” date will change when we do. Continued use after changes means you accept the updated policy.</p>

<h2>11. Contact</h2>
<p>Questions about privacy? Reach us through our <a href="/contact">Contact</a> page.</p>
HTML;
    }

    protected function termsContent(): string
    {
        $date = now()->format('F j, Y');

        return <<<HTML
<p class="text-muted-custom">Last updated: {$date}</p>

<p>Welcome to AI Calculator Hub. By accessing or using our website and services (the “Service”), you agree to these Terms &amp; Conditions. If you do not agree, please do not use the Service.</p>

<h2>1. Description of the Service</h2>
<p>AI Calculator Hub provides online calculators, educational content, optional AI explanations, saved results, favorites, and related tools. Results are estimates based on formulas and the inputs you provide and are for informational and educational purposes only.</p>

<h2>2. Accounts</h2>
<ul>
    <li>You must provide accurate registration information and keep your credentials secure.</li>
    <li>You are responsible for activity under your account.</li>
    <li>We may suspend or terminate accounts that violate these Terms or pose a security risk.</li>
</ul>

<h2>3. Acceptable use</h2>
<p>You agree not to misuse the Service, attempt unauthorized access, scrape excessively, upload unlawful content, or disrupt the platform for others.</p>

<h2>4. Calculators, content and AI</h2>
<ul>
    <li>Outputs depend on your inputs and published methods; always verify critical engineering, financial, medical, tax or legal decisions with a qualified professional.</li>
    <li>AI explanations are automated and may be incomplete; they are not professional advice.</li>
    <li>Blog posts and guides are educational and may be updated as standards change.</li>
</ul>

<h2>5. Advertising</h2>
<p>The Service may display third-party advertisements (including Google AdSense). Ads are provided by third parties and may use cookies as described in our <a href="/privacy-policy">Privacy Policy</a> and <a href="/cookie-policy">Cookie Policy</a>. We are not responsible for third-party advertiser websites or offers.</p>

<h2>6. Free and premium plans</h2>
<p>Some features may require a paid subscription in the future. Plan features and pricing will be described on our <a href="/pricing">Pricing</a> page when available.</p>

<h2>7. Intellectual property</h2>
<p>The Service, branding, UI, and original content are owned by AI Calculator Hub or its licensors. You may use the Service for your own calculations; you may not copy or reverse-engineer the platform except as permitted by law.</p>

<h2>8. Disclaimer of warranties</h2>
<p>THE SERVICE IS PROVIDED “AS IS” AND “AS AVAILABLE” WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED. We do not guarantee uninterrupted or error-free operation.</p>

<h2>9. Limitation of liability</h2>
<p>To the maximum extent permitted by law, AI Calculator Hub is not liable for indirect, incidental, special, consequential, or punitive damages arising from your use of the Service. Our total liability is limited to the amount you paid us in the twelve (12) months before the claim, or zero if you use only free features.</p>

<h2>10. Changes</h2>
<p>We may modify these Terms by posting an updated version. Continued use after the effective date constitutes acceptance.</p>

<h2>11. Contact</h2>
<p>Questions? Contact us via the <a href="/contact">Contact</a> page.</p>
HTML;
    }

    protected function cookieContent(): string
    {
        $date = now()->format('F j, Y');

        return <<<HTML
<p class="text-muted-custom">Last updated: {$date}</p>

<p>This Cookie Policy explains how AI Calculator Hub uses cookies and similar technologies, and how you can control them.</p>

<h2>1. What are cookies?</h2>
<p>Cookies are small text files stored on your device. We also use local storage for preferences such as theme. Some cookies are essential; others help us improve the Service or show advertising.</p>

<h2>2. Types of cookies we use</h2>
<ul>
    <li><strong>Essential</strong> — session, authentication, CSRF security. Required for the site to work.</li>
    <li><strong>Preferences</strong> — language and theme choices.</li>
    <li><strong>Analytics</strong> — optional measurement of traffic and feature usage (if enabled).</li>
    <li><strong>Advertising</strong> — when Google AdSense or similar partners are enabled, they may set cookies to serve and measure ads, including personalized ads where allowed.</li>
</ul>

<h2>3. Google AdSense cookies</h2>
<p>If advertising is active on this site, Google may use cookies such as the DoubleClick / AdSense cookies to personalize ads and limit how often you see an ad. Learn more at <a href="https://policies.google.com/technologies/ads" rel="noopener noreferrer" target="_blank">Google Advertising</a>. Manage ad personalization at <a href="https://adssettings.google.com" rel="noopener noreferrer" target="_blank">adssettings.google.com</a>.</p>

<h2>4. Your choices</h2>
<ul>
    <li>Use our on-site cookie banner to accept or decline non-essential cookies (where shown).</li>
    <li>Control cookies in your browser settings (blocking essential cookies may break login).</li>
    <li>Use Google Ad Settings or industry opt-outs where available.</li>
</ul>

<h2>5. More information</h2>
<p>For personal data processing beyond cookies, see our <a href="/privacy-policy">Privacy Policy</a>. Questions? <a href="/contact">Contact us</a>.</p>
<p>Last updated: {$date}</p>
HTML;
    }

    protected function disclaimerContent(): string
    {
        $date = now()->format('F j, Y');

        return <<<HTML
<p class="text-muted-custom">Last updated: {$date}</p>

<p>The information and calculators on AI Calculator Hub are provided for general education, estimation and planning only. They are not a substitute for professional advice.</p>

<h2>1. No professional advice</h2>
<p>Results from our calculators — including finance, tax, health, fitness, construction, engineering and similar tools — do not constitute financial, investment, tax, legal, medical, architectural or engineering advice. Always consult a qualified professional before making decisions that affect money, health, safety or compliance.</p>

<h2>2. Accuracy of results</h2>
<p>Outputs depend on the formulas we publish and the values you enter. Rounding, unit conversion, regional rules and incomplete inputs can change real-world outcomes. Verify critical numbers independently.</p>

<h2>3. AI explanations</h2>
<p>Optional AI explanations are generated automatically and may contain errors or omissions. Treat them as educational summaries, not authoritative guidance.</p>

<h2>4. Third-party content and ads</h2>
<p>Blog posts, external links and advertisements (including Google AdSense when enabled) are provided for convenience. We do not endorse third-party products or guarantee their accuracy.</p>

<h2>5. Limitation</h2>
<p>To the fullest extent permitted by law, AI Calculator Hub and its operators are not liable for loss or damage arising from reliance on calculator outputs or site content. See also our <a href="/terms-conditions">Terms &amp; Conditions</a>.</p>

<p>Questions? <a href="/contact">Contact us</a>.</p>
HTML;
    }
}
