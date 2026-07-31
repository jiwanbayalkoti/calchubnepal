<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: mortgage affordability basics.
 */
class MortgageAffordabilityBasics
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Buying a home in Kathmandu’s tightening property market, securing a flat in Lalitpur with a bank housing loan, or building on ancestral land in the Terai with finance from a local cooperative all converge on one question: how much mortgage can you actually afford—not how much the lender’s maximum approval letter says you can borrow? Banks in Nepal licensed by Nepal Rastra Bank (NRB) underwrite on income documents, credit history, and loan-to-value limits, but their incentive is to lend within policy, not to guarantee the payment fits your family budget, school fees, and emergency savings.</p>

<p>Mortgage affordability is the discipline of matching home loan size, down payment, interest rate, and tenure to stable income and total debt obligations. South Asian households often combine formal salary income with rental income, remittances, or business cash flow—document what the bank will accept, then stress-test what you can pay if rates rise or income dips. This guide explains rule-of-thumb ratios used globally and adapted locally, how down payment changes EMI, and how to use calculators without over-leveraging.</p>

<p>Map loan scenarios with the free <a href="/calculator/mortgage-calculator">Mortgage Calculator</a> and <a href="/calculator/emi-calculator">EMI Calculator</a> on CalchubNepal after you estimate a comfortable payment band.</p>

<h2>What lenders evaluate vs what you should evaluate</h2>

<p><strong>Lenders focus on:</strong> credit bureau report (where available), verified income, employment stability, property valuation, loan-to-value (LTV), and regulatory caps. NRB directives evolve on real-estate exposure and margin requirements; individual banks publish housing loan products with minimum down payment (often 20–40% of property value in Nepal depending on bank and property type—confirm current product sheets).</p>

<p><strong>You should additionally focus on:</strong> monthly cash flow after all family expenses, maintenance and society charges, property tax, insurance, rate-reset risk on floating products, and retained emergency fund (typically 3–6 months of essential expenses even after down payment).</p>

<h2>Affordability ratios (starting points, not laws)</h2>

<p>International personal finance practice and many bank credit manuals use housing burden ratios:</p>

<p><strong>Housing cost ratio</strong> = Total monthly housing costs ÷ Gross monthly income</p>

<p>Total housing costs include mortgage EMI, property tax, insurance, and average maintenance. A common planning band is keeping this ratio near or below <strong>28–35%</strong> of gross income, though dual-income urban households in South Asia sometimes stretch higher with eyes open to risk.</p>

<p><strong>Total debt service ratio</strong> = (Housing costs + other EMIs) ÷ Gross monthly income</p>

<p>Many underwriters target <strong>40–45%</strong> maximum for all debt combined. If you already carry education loans, vehicle finance, or personal loans, your room for housing EMI shrinks.</p>

<h3>Example: dual-income household in Pokhara</h3>

<p>Combined gross income NPR 120,000/month. Target housing ratio 30% → housing budget ≈ NPR 36,000/month. Existing car EMI NPR 8,000 → total debt service 36,000 + 8,000 = 44,000 → 36.7% of income—within a cautious 40% ceiling. If maintenance and tax add NPR 4,000, true housing cost is NPR 40,000 → 33.3% housing ratio still acceptable under 35% guideline.</p>

<h2>Translating affordable EMI into loan amount</h2>

<p>Once you set a target EMI, invert the standard loan payment formula. For monthly rate r and n months, maximum principal for payment EMI is:</p>

<pre>P = EMI × ((1 + r)^n − 1) ÷ (r × (1 + r)^n)</pre>

<p>Example: comfortable EMI NPR 40,000; rate 11% per annum; tenure 20 years (n = 240).</p>

<p>r = 0.11/12 ≈ 0.009167<br>
P ≈ 40,000 × ((1.009167)^240 − 1) ÷ (0.009167 × (1.009167)^240)<br>
≈ <strong>NPR 4.0–4.1 million</strong> (use calculator for exact figure)</p>

<p>Add your down payment to estimate purchasable property price:</p>

<p><strong>Max property price (indicative)</strong> ≈ Loan amount ÷ (1 − Down payment fraction)</p>

<p>If you put 25% down on NPR 4 million loan, property ≈ 4 ÷ 0.75 ≈ NPR 5.33 million—subject to bank valuation, not asking price alone.</p>

<h2>Down payment: why it matters beyond LTV</h2>

<ul>
<li>Reduces principal and therefore EMI from day one.</li>
<li>May improve rate tier or waive certain insurance requirements on some products.</li>
<li>Protects you from negative equity if market prices soften—relevant in volatile urban pockets.</li>
<li>Leaves liquidity question: draining every rupee for down payment leaves no buffer for registration, furniture, and move-in costs (often 5–10% of property value in fees and setup in Nepal).</li>
</ul>

<p>NRB and bank LTV limits exist partly to require borrower skin in the game—respect them, but do not interpret "minimum down payment" as "recommended down payment."</p>

<h2>Interest rate sensitivity</h2>

<p>Always stress-test +1 percentage point on annual rate. On NPR 5 million over 20 years at 10.5%, EMI ≈ NPR 49,919. At 11.5%, EMI ≈ NPR 53,400—roughly NPR 3,500 more per month, or about NPR 840,000 extra over 20 years if tenure unchanged. Floating-rate mortgages linked to bank base rate expose you to this risk—model higher rates even if today's quote is attractive. If +1% breaks your budget, reduce loan principal or increase down payment until the stressed EMI fits your housing ratio guideline.</p>

<table class="table">
<thead>
<tr><th>Planning check</th><th>Question to answer</th></tr>
</thead>
<tbody>
<tr><td>Rate shock</td><td>Can I pay EMI if rate rises 1–2%?</td></tr>
<tr><td>Income shock</td><td>Can one income cover essentials + EMI temporarily?</td></tr>
<tr><td>Upfront costs</td><td>Cash left after down payment for fees and emergencies?</td></tr>
<tr><td>Tenure trade-off</td><td>Is lower EMI worth extra total interest?</td></tr>
</tbody>
</table>

<h2>Nepal-specific considerations</h2>

<p>Property registration and transfer fees, local municipality charges, and advocate fees add to cash need beyond down payment. Remittance income may require specific documentation for NRB-compliant forex trail. Co-operative and finance company products may differ from commercial bank housing loans on rate, LTV, and prepayment terms—compare effective cost. Tax treatment of home loan interest deductions changes with finance acts—check ird.gov.np or a qualified tax adviser for the current year. Land plotting and unregistered transfer risks are legal matters outside this guide; affordability means little without clear title.</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Borrowing the maximum approval amount</strong> — approval is ceiling, not target.</li>
<li><strong>Counting bonus or overtime as permanent income</strong> — base salary is safer for underwriting your own budget.</li>
<li><strong>Forgetting non-EMI housing costs</strong> — society maintenance, water tank, generator fuel in load-shedding areas.</li>
<li><strong>Zero emergency fund after closing</strong> — one medical bill or job gap triggers default risk.</li>
<li><strong>Comparing properties by EMI alone on 30-year tenure</strong> — stretches debt into retirement years.</li>
<li><strong>Ignoring prepayment and foreclosure charges</strong> — affects true flexibility.</li>
</ul>

<h2>References &amp; further reading</h2>

<ul>
<li>Nepal Rastra Bank — banking supervision and consumer credit context (<a href="https://www.nrb.org.np">nrb.org.np</a>).</li>
<li>Inland Revenue Department, Nepal — income tax and allowable deductions (<a href="https://www.ird.gov.np">ird.gov.np</a>).</li>
<li>Nepal National Building Code — structural and habitability context for financed property.</li>
<li>Brealey, Myers, Allen — <em>Principles of Corporate Finance</em> — debt capacity and present value framing.</li>
<li>U.S. CFPB home affordability materials — ratio concepts (international educational parallel).</li>
<li>Wikipedia — <a href="https://en.wikipedia.org/wiki/Mortgage_loan">Mortgage loan</a> and <a href="https://en.wikipedia.org/wiki/Loan-to-value_ratio">Loan-to-value ratio</a>.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>What percentage of income should go to home loan EMI?</h3>
<p>Many planners suggest 28–35% of gross income for all housing costs, not EMI alone. Stricter budgets use 25–30% for EMI only if taxes and maintenance are small.</p>

<h3>Is longer tenure always better for affordability?</h3>
<p>Longer tenure lowers EMI but increases total interest and keeps you in debt longer. It helps cash flow only if you invest the difference wisely—or truly need breathing room.</p>

<h3>Should I use all remittance savings as down payment?</h3>
<p>Keep an emergency reserve. Larger down payment reduces EMI, but zero liquidity is risky for migrant-worker households with income disruption risk.</p>

<h3>How does co-borrower income help?</h3>
<p>Banks may combine incomes for eligibility; you should also assess whether combined budget supports the payment if one borrower stops earning.</p>

<h3>Fixed or floating rate for affordability planning?</h3>
<p>Fixed aids predictability; floating may start lower but requires rate-shock testing. Choose based on rate outlook and sleep-at-night threshold.</p>

<h2>Plan responsibly with CalchubNepal</h2>

<p>Enter property price or loan amount, down payment, rate, and tenure in the free <a href="/calculator/mortgage-calculator">Mortgage Calculator</a> on CalchubNepal, then cross-check EMI with the <a href="/calculator/emi-calculator">EMI Calculator</a>. Run at least three scenarios: base case, +1% rate, and shorter tenure with higher EMI. Affordable home ownership means stable payments, preserved savings, and room for life—not the biggest loan signboard the bank can print.</p>
HTML;
    }
}
