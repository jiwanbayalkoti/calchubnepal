<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: GST/VAT inclusive vs exclusive pricing.
 */
class GstVatHowTaxIsAdded
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Value-added tax (VAT) and goods and services tax (GST) are consumption taxes charged as a percentage of taxable value. Shopkeepers, freelancers, and finance teams stumble at the same point: is the sticker price tax-inclusive or tax-exclusive? Add tax on top incorrectly and your invoice total is wrong; extract tax from an inclusive price with the wrong formula and your books misstate revenue. The math is small; the compliance impact is not.</p>

<p>This guide covers exclusive versus inclusive pricing, forward and reverse formulas, rounding discipline, and Nepal's 13% VAT context administered by the Inland Revenue Department (IRD). Use our <a href="/calculator/sales-tax-calculator">Sales Tax Calculator</a>, <a href="/calculator/vat-calculator">VAT Calculator</a>, or <a href="/calculator/gst-calculator">GST Calculator</a> to switch views instantly—then document the basis you used on every quote and receipt.</p>

<h2>Tax-exclusive pricing (add tax on top)</h2>

<p>When a price is quoted <em>exclusive</em> of tax, the net amount is the commercial base; tax is calculated on that base and added to reach the gross total the customer pays.</p>

<p><strong>Tax amount = Net price × (Rate ÷ 100)</strong></p>

<p><strong>Gross price = Net price + Tax amount</strong></p>

<p>Equivalently: <strong>Gross = Net × (1 + Rate/100)</strong></p>

<h3>Worked example: Nepal VAT at 13%</h3>

<p>A contractor quotes NPR 100,000 exclusive of VAT for services.</p>

<ol>
<li>Tax = 100,000 × 0.13 = NPR 13,000</li>
<li>Gross invoice = 100,000 + 13,000 = NPR 113,000</li>
</ol>

<p>The customer pays 113,000; the seller remits 13,000 to the tax authority (subject to input credit rules for registered businesses).</p>

<h2>Tax-inclusive pricing (tax already inside)</h2>

<p>Retail shelves and restaurant menus often show what you pay—the gross figure. To split net and tax for accounting:</p>

<p><strong>Net price = Gross price ÷ (1 + Rate/100)</strong></p>

<p><strong>Tax amount = Gross price − Net price</strong></p>

<h3>Worked example: back-calculate from NPR 1,130 inclusive</h3>

<p>A receipt total is NPR 1,130 including 13% VAT.</p>

<ol>
<li>Net = 1,130 ÷ 1.13 = NPR 1,000</li>
<li>Tax = 1,130 − 1,000 = NPR 130</li>
</ol>

<p>Common mistake: calculating 13% of 1,130 (147.70) — that overstates tax because 13% of a tax-inclusive total is not the embedded VAT.</p>

<h2>Why the divisor formula works</h2>

<p>If net is N, gross G = N × (1 + r). Rearranging: N = G ÷ (1 + r). This is the same structure worldwide whether the rate is Nepal's 13% VAT, India's multi-slab GST, or a generic sales tax in another jurisdiction—only the rate and exemption rules change.</p>

<h2>Multiple line items and mixed rates</h2>

<p>Invoices with several products may carry different rates or zero-rated lines. Compute tax per line on its net amount, sum taxes, then add to net subtotal—or use your accounting system's line-level flags. Do not apply one blended rate to a basket containing exempt education materials and standard-rated goods unless your jurisdiction explicitly allows a single composite rate (rare).</p>

<h3>Worked example: two-line invoice</h3>

<ul>
<li>Line A net NPR 2,000 @ 13% → tax 260</li>
<li>Line B net NPR 500 @ exempt → tax 0</li>
<li>Total net 2,500; total tax 260; gross 2,760</li>
</ul>

<h2>Discounts before tax</h2>

<p>Trade discount reduces the taxable base before tax is applied.</p>

<p>List net 10,000, discount 10% → adjusted net 9,000 → tax @13% = 1,170 → gross 10,170.</p>

<p>If discount is applied after tax in a promotional campaign, the tax treatment depends on local rules and whether the discount is supplier-funded—confirm with IRD guidance for registered VAT businesses in Nepal.</p>

<h2>Nepal VAT context (high level)</h2>

<p>Nepal levies VAT at 13% on most taxable supplies of goods and services, with exemptions and zero-ratings for specified categories (education, health in defined cases, basic food items per schedules, exports, etc.). Registered taxpayers charge VAT on outputs, claim credit for VAT on qualifying inputs, and file periodic returns with IRD.</p>

<p>Key practical points for small businesses:</p>

<ul>
<li>Display on invoices whether prices are inclusive or exclusive where required by regulation.</li>
<li>Maintain separate columns for taxable value, VAT amount, and total in your books.</li>
<li>Exempt supplies do not entitle input credit the same way standard-rated purchases do—accounting gets asymmetric quickly; use professional support when scaling.</li>
<li>IRD publishes notices and taxpayer service guides at <a href="https://ird.gov.np" rel="noopener noreferrer" target="_blank">ird.gov.np</a>.</li>
</ul>

<p>This article does not replace IRD notices or the Value Added Tax Act and regulations.</p>

<h2>GST elsewhere vs VAT in Nepal</h2>

<p>GST (Goods and Services Tax) branding appears in India, Canada, Australia, and others with different slabs, registration thresholds, and return formats. The inclusive/exclusive math is identical; compliance labels differ. Our GST calculator presets help travellers and cross-border sellers reason about generic rates; Nepal-specific retail planning should anchor on 13% VAT unless a different statutory rate applies to your supply.</p>

<h2>Rounding rules</h2>

<p>Real invoices round to the nearest paisa or rupee at line level or invoice total depending on policy. Micro-differences appear when you round each line versus round once at the bottom. Pick one method consistent with your tax software and IRD expectations; do not mix methods month to month.</p>

<h3>Worked example: rounding at line level</h3>

<p>Net NPR 333.33 @ 13%: tax = 43.3329 → rounded 43.33; gross 376.66. Two such lines summed may differ slightly from taxing the subtotal first—document the approach.</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Applying tax rate to inclusive price</strong> without dividing by (1 + rate) first.</li>
<li><strong>Confusing "13% on top" with "13% of total bill"</strong> — different bases.</li>
<li><strong>Ignoring exempt lines</strong> when estimating quick totals.</li>
<li><strong>Using outdated rates</strong> after finance-act changes.</li>
<li><strong>Mark-up percentage on cost confused with VAT</strong> — commercial margin is not tax.</li>
<li><strong>Foreign currency invoices</strong> without consistent conversion date for VAT base.</li>
</ul>

<h2>Margin after VAT: seller perspective</h2>

<p>A retailer buys stock at NPR 100 net, adds 30% mark-up → selling net 130. VAT 13% on 130 = 16.90; shelf gross 146.90. Profit before other costs is 30 net, not 30% of gross. VAT collected is pass-through to the state—not revenue. Small shops sometimes treat gross as income and overestimate profit; separating VAT control accounts prevents this.</p>

<h3>Worked example: extract margin from VAT-inclusive shelf price</h3>

<p>Shelf price 1,130 inclusive; cost net 900. Net selling price = 1,130 ÷ 1.13 = 1,000. Gross margin before tax = 1,000 − 900 = 100. VAT remitted on sale = 130. Margin percent on cost ≈ 11.1%, not 22.2% of shelf price. Training counter staff to quote exclusive prices to B2B buyers and inclusive prices to retail walk-ins reduces checkout disputes.</p>

<h2>Business checklist before issuing a quote</h2>

<ol>
<li>State clearly: "Prices exclusive of 13% VAT" or "Prices inclusive of VAT."</li>
<li>Break out net, VAT, and gross on formal invoices.</li>
<li>Verify customer's VAT registration if charging B2B reverse-charge scenarios do not apply in your case.</li>
<li>Store calculation method (exclusive add vs inclusive extract) in quote templates.</li>
<li>Reconcile POS totals to general ledger VAT control account monthly.</li>
</ol>

<h2>References &amp; further reading</h2>

<ul>
<li>Inland Revenue Department, Nepal — VAT registration, returns, and public notices (<a href="https://ird.gov.np" rel="noopener noreferrer" target="_blank">ird.gov.np</a>).</li>
<li><em>Value Added Tax Act, 2052 (1996)</em> and amendments — statutory framework (consult consolidated texts).</li>
<li>OECD, <em>Consumption Tax Trends</em> — international VAT/GST design comparison (oecd.org).</li>
<li>PwC / KPMG Nepal tax summaries — professional overviews updated per fiscal year (verify currency).</li>
<li>James Mirrlees et al., <em>Tax by Design</em> (Oxford University Press) — economic principles of consumption taxes.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>What is the VAT rate in Nepal?</h3>
<p>The standard rate is 13% on taxable supplies unless a schedule provides a different rate or exemption.</p>

<h3>How do I remove VAT from a tax-inclusive price?</h3>
<p>Divide the gross price by 1.13 (for 13% VAT) to get net; subtract net from gross to get VAT amount.</p>

<h3>Is GST the same as VAT?</h3>
<p>Conceptually similar consumption taxes; naming and compliance differ by country. Math for inclusive/exclusive pricing is the same.</p>

<h3>Do I pay VAT on discounts?</h3>
<p>Tax applies to the consideration received—discounts that reduce taxable value generally reduce VAT if applied before tax calculation per rules.</p>

<h3>Can I use one calculator for Nepal and other countries?</h3>
<p>Yes for arithmetic; set the rate to 13% for Nepal standard VAT or choose another preset for cross-border examples.</p>

<h2>Calculate tax both ways</h2>

<p>Switch between tax-exclusive and tax-inclusive pricing with our <a href="/calculator/sales-tax-calculator">Sales Tax Calculator</a>, <a href="/calculator/vat-calculator">VAT Calculator</a>, and <a href="/calculator/gst-calculator">GST Calculator</a>. Enter net or gross, pick your rate, and copy the breakdown onto quotes and invoices with confidence.</p>
HTML;
    }
}
