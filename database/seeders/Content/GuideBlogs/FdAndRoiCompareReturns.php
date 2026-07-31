<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: FD maturity, ROI comparison, Nepal banking context.
 */
class FdAndRoiCompareReturns
{
    public static function html(): string
    {
        return <<<'HTML'
<p>When you have savings to deploy, two questions appear on every comparison spreadsheet: <em>How much will a fixed deposit pay at maturity?</em> and <em>Was that investment worth it relative to what I put in?</em> The first belongs to FD mathematics—principal, tenure, quoted rate, compounding frequency, and tax. The second is return on investment (ROI)—a ratio that applies equally to a bank deposit, a small shop expansion, or a rental flat. In Nepal, Nepal Rastra Bank (NRB) sets policy rates that influence bank deposit offers, while commercial banks publish FD schemes in Nepali rupees from 7 days to several years. This guide explains both formulas clearly, shows worked examples in NPR, compares when each metric is appropriate, and highlights mistakes that make a "high ROI" side project look better than a boring FD when risk and liquidity differ.</p>

<h2>Fixed deposits in Nepal: context</h2>
<p>NRB regulates licensed banks and finance companies. Individual depositors typically choose among saving deposits, fixed deposits (FDs), and recurring products. FDs lock principal for an agreed term at a quoted annual interest rate; premature withdrawal usually pays a lower penal rate. Rates move with liquidity conditions, NRB policy signals, and bank competition—always read the offer circular for the effective date, minimum amount, and whether interest is payable monthly, quarterly, at maturity, or compounded into principal.</p>

<p>Interest income may be subject to withholding tax depending on account type and prevailing tax rules; banks often deduct tax at source on interest. For planning, net maturity value matters more than gross quoted rate. Consult a tax professional for your personal situation—this article focuses on calculation mechanics, not legal advice.</p>

<h2>FD maturity formulas</h2>

<h3>Simple interest (interest paid out, principal unchanged)</h3>
<p><strong>Interest = P × R × T</strong></p>
<p>Where P = principal, R = annual rate as decimal (9% → 0.09), T = time in years.</p>
<p><strong>Maturity amount = P + Interest = P × (1 + R × T)</strong></p>

<h3>Compound interest (reinvested each period)</h3>
<p><strong>A = P × (1 + r)<sup>n</sup></strong></p>
<p>Where r = rate per compounding period, n = number of periods.</p>
<p>If compounded quarterly: <strong>r = annual rate ÷ 4</strong>, <strong>n = years × 4</strong>.</p>

<h3>Effective annual yield</h3>
<p>When compounding is more frequent than once per year, nominal rate understates growth. Approximate comparison:</p>
<p><strong>Effective rate ≈ (1 + nominal/m)<sup>m</sup> − 1</strong></p>
<p>where m = compounding periods per year. A 9% nominal rate compounded quarterly yields slightly more than 9% simple over one year.</p>

<h2>ROI formula</h2>
<p><strong>ROI (%) = ((Final value − Initial cost) ÷ Initial cost) × 100</strong></p>
<p>For a pure FD with no additional cash flows mid-term, final value is maturity amount and initial cost is principal. ROI then aligns with total return over the holding period—but annualising is needed to compare a 6-month FD with a 3-year FD or a business project.</p>

<h3>Annualised ROI</h3>
<p><strong>Annualised ROI ≈ ((Final ÷ Initial)<sup>1/years</sup> − 1) × 100</strong></p>
<p>This smooths compounding into a per-year figure for ranking alternatives of different lengths.</p>

<h2>Worked example: NPR 500,000 FD for 2 years at 9% compounded quarterly</h2>
<p>P = 500,000; nominal annual rate = 9%; r = 0.09 ÷ 4 = 0.0225; n = 2 × 4 = 8 quarters.</p>
<p><strong>A = 500,000 × (1.0225)<sup>8</sup></strong></p>
<p>(1.0225)<sup>8</sup> ≈ 1.1877</p>
<p><strong>A ≈ 593,850 NPR</strong></p>
<p>Interest earned ≈ <strong>93,850 NPR</strong></p>
<p><strong>Total ROI</strong> = (93,850 ÷ 500,000) × 100 ≈ <strong>18.77% over 2 years</strong></p>
<p><strong>Annualised ROI</strong> ≈ ((593,850 ÷ 500,000)<sup>1/2</sup> − 1) × 100 ≈ <strong>8.95% per year</strong></p>

<p>Run the same inputs in the <a href="/calculator/fd-calculator">FD Calculator</a> to verify against your bank's exact day-count convention.</p>

<h3>Worked example: ROI on a small business purchase</h3>
<p>You invest NPR 200,000 in equipment and net NPR 260,000 after one year (after operating costs, before tax):</p>
<p><strong>ROI = ((260,000 − 200,000) ÷ 200,000) × 100 = 30%</strong></p>
<p>Compare to the FD annualised ~9%: the business shows higher ROI but carries inventory risk, downtime, and illiquidity. ROI alone does not capture that.</p>

<h3>Worked example: comparing two bank FD offers</h3>
<table>
<thead><tr><th>Offer</th><th>Rate</th><th>Term</th><th>Compounding</th></tr></thead>
<tbody>
<tr><td>Bank A</td><td>8.5% p.a.</td><td>1 year</td><td>Quarterly</td></tr>
<tr><td>Bank B</td><td>9.0% p.a.</td><td>1 year</td><td>At maturity (simple)</td></tr>
</tbody>
</table>
<p>Bank A: 100,000 × (1 + 0.085/4)<sup>4</sup> ≈ 108,775 → ROI ≈ 8.78%</p>
<p>Bank B: 100,000 × (1 + 0.09 × 1) = 109,000 → ROI = 9.00%</p>
<p>Bank B wins slightly on one-year simple math—but check minimum deposit, renewal terms, and DCS (deposit protection scheme) coverage limits published by NRB.</p>

<h2>When to use FD math vs ROI</h2>
<ul>
<li><strong>FD calculator:</strong> Known principal, bank-quoted rate, fixed tenure, compounding rules—project maturity cash.</li>
<li><strong>ROI calculator:</strong> Any investment where you know money in and money out—stocks sold, rental property, education, machinery.</li>
<li><strong>Together:</strong> Compute FD maturity, then feed gain and principal into ROI for apples-to-apples ranking against non-FD options on the same time horizon.</li>
</ul>

<h2>Common mistakes</h2>
<ul>
<li><strong>Comparing nominal rates without compounding frequency:</strong> 9% compounded monthly beats 9% simple over multi-year terms.</li>
<li><strong>Ignoring tax and fees:</strong> Net ROI after TDS differs from gross brochure rate.</li>
<li><strong>Using ROI on annual cash flows without IRR:</strong> SIPs or projects with multiple inflows need internal rate of return (IRR), not one-shot ROI.</li>
<li><strong>Chasing highest rate from unlicensed schemes:</strong> Verify institution is NRB-licensed; extraordinary returns imply extraordinary risk.</li>
<li><strong>Breaking FD early without recalculating:</strong> Penalty clauses can collapse expected ROI.</li>
<li><strong>Confusing CAGR with simple ROI:</strong> Multi-year growth should be annualised before comparing to a 1-year FD poster rate.</li>
</ul>

<h2>NRB and depositor safety notes</h2>
<p>NRB publishes licensed bank lists, interest rate guidelines in some periods, and consumer protection frameworks. Deposit insurance arrangements (and coverage caps) apply to participating institutions—confirm current limits on NRB or deposit insurance corporation publications before placing large balances. Splitting deposits across institutions is a structural choice some savers use to stay within coverage limits; that is a policy decision, not a formula issue.</p>

<h2>Recurring deposits and hybrid products</h2>
<p>Many Nepali banks offer recurring deposits (RD) where you invest a fixed sum monthly. Maturity value uses the same compound annuity logic as a SIP in mutual funds, but with a guaranteed rate from the bank. Comparing RD maturity to lump-sum FD plus ROI on a business requires aligning tenure and liquidity: RD suits salaried savers building toward a goal; lump-sum FD suits a windfall or sale proceeds. When a bank bundles "FD plus insurance" or linked credit cards, strip out the pure deposit component before comparing rates—marketing bundles obscure the effective yield.</p>

<h2>Inflation and real returns</h2>
<p>Nominal ROI on an FD can look positive while purchasing power falls if consumer price inflation exceeds your annualised return. Nepal Rastra Bank and Central Bureau of Statistics publish inflation indicators; subtract approximate inflation from annualised FD return for a rough "real return" estimate. A 9% FD during 7% inflation yields roughly 2% real—still positive, but far less exciting than the headline rate. Business investments with 30% nominal ROI must be judged against failure risk and inflation, not against FD alone.</p>

<h2>References &amp; further reading</h2>
<ul>
<li>Nepal Rastra Bank (NRB) — monetary policy, licensed bank directory, and financial stability reports at nrb.org.np.</li>
<li>Individual commercial bank FD rate sheets — always dated; supersede third-party aggregator listings.</li>
<li>Investopedia / CFA curriculum summaries — ROI, CAGR, and effective annual rate definitions.</li>
<li>NRB unified directives on interest rates and reporting — for finance professionals reconciling offer terms.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>Is FD ROI the same as the interest rate?</h3>
<p>Over one year with simple interest and no taxes, they align closely. With compounding, multi-year terms, or taxes, total ROI differs from the headline "X% per annum" quote.</p>

<h3>Should I take monthly interest or reinvest?</h3>
<p>Reinvestment compounds; monthly payout suits income needs but lowers terminal wealth. Model both in the FD calculator.</p>

<h3>Can ROI be negative?</h3>
<p>Yes—if final value is below cost. Guaranteed FDs in NPR from licensed banks are structured to avoid capital loss barring institutional failure; equities and business projects routinely show negative ROI.</p>

<h3>How do I compare FD to mutual funds?</h3>
<p>FD maturity is contractual (subject to bank credit risk). Mutual funds are mark-to-market. Compare annualised returns over the same calendar window and stress-test fund scenarios separately.</p>

<h3>What rate should I assume for 2025–2026 planning?</h3>
<p>Use the rate your bank offers today for your tenure and amount—not a generic blog figure. Re-run calculations when NRB policy shifts or your bank reprices.</p>

<h2>Compare returns with clear numbers</h2>
<p>Project FD maturity with the <a href="/calculator/fd-calculator">FD Calculator</a>, then express any alternative—side business, gold, rent—in the same language using the <a href="/calculator/roi-calculator">ROI Calculator</a>. Decisions improve when maturity value, annualised return, and liquidity risk sit on the same page.</p>

<p><em>Disclaimer: Examples are illustrative. This is educational content, not investment, tax, or legal advice. Verify rates and terms with your bank and qualified advisors before committing funds.</em></p>
HTML;
    }
}
