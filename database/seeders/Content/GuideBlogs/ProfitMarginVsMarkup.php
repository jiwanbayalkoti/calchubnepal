<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: profit margin vs markup for product pricing.
 */
class ProfitMarginVsMarkup
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Ask a shop owner in Kathmandu or a manufacturer in Birgunj what margin they target, and many will answer "25%." Ask whether that means 25% of cost or 25% of selling price, and the room goes quiet. Margin and markup both describe profit relative to money invested or money received—but they use different denominators, so the same transaction yields two different percentages. Confusing them is one of the fastest ways to underprice inventory, miss break-even on overhead, or reject a wholesale order that looked "low margin" on the wrong formula. This guide defines both metrics, shows the algebra to convert between them, walks through retail and service examples in NPR, and explains how to price toward a target margin using the profit calculator.</p>

<h2>Definitions that must stay separate</h2>
<p><strong>Profit (absolute)</strong> = Selling price − Cost price</p>
<p>Both figures should use the same basis: product cost might include purchase price, freight inward, packaging, and allocated import duty—define your COGS consistently.</p>

<h3>Markup percentage</h3>
<p>Markup answers: <em>How much profit did I add on top of what I paid?</em></p>
<p><strong>Markup % = (Profit ÷ Cost price) × 100</strong></p>

<h3>Profit margin (gross margin) percentage</h3>
<p>Margin answers: <em>What fraction of the selling price is profit?</em></p>
<p><strong>Margin % = (Profit ÷ Selling price) × 100</strong></p>

<p>Because selling price is always larger than cost for a profitable sale, <strong>markup % is always higher than margin %</strong> for the same transaction. Saying "25% margin" while calculating "cost plus 25%" is a classic pricing error—you actually priced at 20% margin.</p>

<h2>Conversion formulas</h2>
<p>Given target margin M (as decimal):</p>
<p><strong>Selling price = Cost ÷ (1 − M)</strong></p>
<p>Given target markup U (as decimal):</p>
<p><strong>Selling price = Cost × (1 + U)</strong></p>
<p>Convert markup to margin:</p>
<p><strong>Margin = Markup ÷ (1 + Markup)</strong> (using decimals, e.g. 0.25 markup → 0.25/1.25 = 0.20 margin)</p>
<p>Convert margin to markup:</p>
<p><strong>Markup = Margin ÷ (1 − Margin)</strong></p>

<h2>Worked example: grocery item</h2>
<p>You import rice at <strong>NPR 800 per bag</strong> landed cost and want a <strong>25% markup</strong>:</p>
<p>Profit = 800 × 0.25 = <strong>NPR 200</strong></p>
<p>Selling price = 800 + 200 = <strong>NPR 1,000</strong></p>
<p><strong>Markup = 200 ÷ 800 = 25%</strong></p>
<p><strong>Margin = 200 ÷ 1,000 = 20%</strong></p>
<p>If your business plan requires <strong>25% margin</strong> (not markup), you must price differently:</p>
<p>Selling price = 800 ÷ (1 − 0.25) = 800 ÷ 0.75 = <strong>NPR 1,067</strong></p>
<p>Profit = NPR 267; markup on cost = 267 ÷ 800 ≈ <strong>33.4%</strong></p>
<p>That NPR 67 gap per bag compounds across thousands of units in a fiscal year.</p>

<h3>Worked example: batch of 500 units</h3>
<p>Unit cost NPR 120, unit selling price NPR 180, quantity 500:</p>
<p>Total cost = 60,000; total revenue = 90,000; total profit = <strong>30,000</strong></p>
<p>Unit profit = 60</p>
<p><strong>Margin % = 30,000 ÷ 90,000 × 100 = 33.33%</strong></p>
<p><strong>Markup % = 30,000 ÷ 60,000 × 100 = 50%</strong></p>
<p>Use the <a href="/calculator/profit-calculator">Profit Calculator</a> when quantity scales beyond mental math.</p>

<h3>Worked example: service business hourly rate</h3>
<p>A freelance designer targets <strong>40% gross margin</strong> on client projects. Fully loaded cost (salary allocation, software, taxes) is <strong>NPR 2,000 per billable hour</strong>.</p>
<p><strong>Hourly rate = 2,000 ÷ (1 − 0.40) = NPR 3,333</strong></p>
<p>If they mistakenly multiplied cost by 1.40, they would charge NPR 2,800—margin would be only 28.6%, leaving less room for non-billable admin time.</p>

<h2>Pricing workflow for retailers</h2>
<ol>
<li>Calculate true COGS per SKU including logistics and wastage allowance.</li>
<li>Set target <strong>margin</strong> from P&amp;L needs (rent, payroll, shrinkage)—not from habit.</li>
<li>Apply <strong>Price = COGS ÷ (1 − margin)</strong>.</li>
<li>Round to psychological price points (999, 1,450) without collapsing margin below minimum.</li>
<li>Record both margin and markup in your inventory system for supplier negotiations.</li>
</ol>

<p>Wholesale tiers often quote discounts "off retail." A 20% trade discount on NPR 1,000 retail leaves NPR 800—know whether that still clears your margin floor on COGS of NPR 650.</p>

<h2>Margin vs markup in negotiations</h2>
<p>Suppliers speak markup: "We offer 15% on MRP." Retailers think margin: "I need 35% margin to cover mall rent." Translate before signing:</p>
<p>If MRP is fixed at NPR 2,000 and trade price is NPR 1,700, retailer margin = 300 ÷ 2,000 = 15%—likely insufficient unless COGS is below 1,700.</p>
<p>Build a simple table mapping cost, target margin, required price, and resulting markup so sales staff do not improvise discounts that erase profit.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Cost-plus 30% when the plan says 30% margin:</strong> Underprices by several percentage points.</li>
<li><strong>Mixing gross and net margin:</strong> Gross excludes operating expenses; net includes them. Compare like with like.</li>
<li><strong>Ignoring VAT/GST in the wrong base:</strong> Decide whether selling price is tax-inclusive and keep COGS consistent.</li>
<li><strong>Using margin on loss leaders without basket analysis:</strong> Low-margin staples may pull traffic if high-margin attachments compensate.</li>
<li><strong>Discounting without recomputing margin:</strong> A 10% flash sale on NPR 1,000 (COGS 700) drops margin from 30% to 22.2%.</li>
<li><strong>Markup on selling price by mistake:</strong> Some spreadsheets divide profit by price and label it markup—wrong definition.</li>
</ul>

<h2>Beyond gross margin</h2>
<p>Gross margin covers direct product cost only. Operating margin subtracts rent, utilities, and salaries. Net margin subtracts interest and taxes. Pricing formulas here address gross level; your target margin must leave enough to fund overhead. If gross margin is 25% but overhead consumes 22% of revenue, net is razor thin.</p>

<h2>Break-even and margin targets</h2>
<p>Break-even revenue = Fixed costs ÷ Gross margin (as decimal). If monthly fixed costs are NPR 150,000 and you maintain 30% gross margin, you need NPR 500,000 in monthly sales before profit turns positive. That back-solve is how experienced retailers set minimum margin floors—they refuse SKUs that cannot contribute to fixed cost recovery at expected turnover. Markup thinking skips this link because it never references selling price in the denominator.</p>

<h3>Worked example: break-even with margin</h3>
<p>Fixed costs NPR 200,000/month; target gross margin 35%:</p>
<p><strong>Break-even sales = 200,000 ÷ 0.35 ≈ NPR 571,429/month</strong></p>
<p>At average selling price NPR 500 per unit and COGS NPR 325 (35% margin), you must sell roughly 1,143 units monthly. If foot traffic supports only 900 units, you either raise prices (increase margin), cut COGS, or reduce fixed costs—markup alone will not reveal the gap.</p>

<h2>Discounting and promotions</h2>
<p>Seasonal sales (Dashain, Tihar) often advertise "20% off." Applied to selling price, margin collapses non-linearly. Item with COGS 700, regular price 1,000 (30% margin): 20% off → sale price 800 → profit 100 → margin 12.5%. Plan promotions by calculating forward margin, not backward from habit. Buy-one-get-one offers halve effective selling price on half the units—model blended margin across the basket before approving signage.</p>

<h2>Multi-SKU portfolios</h2>
<p>Shops rarely sell one product. Weighted average margin = Total gross profit ÷ Total revenue across SKUs. High-markup accessories can subsidise low-margin staples that drive footfall. Review category margins monthly: dairy may run 8% while kitchenware runs 38%; blended store margin must still clear break-even. Export both margin and markup columns from your POS so buyers and accountants speak the same language with suppliers.</p>

<h2>References &amp; further reading</h2>
<ul>
<li>Horngren, Datar &amp; Rajan — <em>Cost Accounting</em> — standard treatment of markup vs margin in managerial accounting.</li>
<li>U.S. Small Business Administration pricing guides — accessible introduction to cost-plus and value-based pricing (concepts apply globally).</li>
<li>Retail industry benchmarks — category-specific gross margin ranges (FMCG vs apparel vs electronics).</li>
<li>IFRS / Nepal Financial Reporting Standards — revenue recognition and inventory costing methods affecting COGS.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>Which is better, margin or markup?</h3>
<p>Neither is "better"—they answer different questions. Use margin for financial statements and break-even analysis; use markup for quick cost-plus pricing if you convert correctly.</p>

<h3>Can margin exceed 100%?</h3>
<p>Gross margin cannot exceed 100% (profit cannot exceed revenue). Markup can exceed 100% when profit exceeds cost—common in luxury goods.</p>

<h3>What margin do small shops in Nepal target?</h3>
<p>Varies by category: FMCG may run 8–15% gross; fashion or specialty hardware may target 25–40%. Your rent-to-sales ratio matters more than industry averages.</p>

<h3>How do I price for a target net profit?</h3>
<p>Start from overhead budget and desired net, work backward to required gross margin, then apply price formula per SKU or average basket.</p>

<h3>Does the profit calculator show both?</h3>
<p>Yes—enter cost, selling price, and quantity in the <a href="/calculator/profit-calculator">Profit Calculator</a> to see total profit, margin %, and markup % together.</p>

<h2>Price with the right formula</h2>
<p>Stop guessing "plus twenty-five percent." Open the <a href="/calculator/profit-calculator">Profit Calculator</a>, enter your costs and either selling price or desired margin, and read markup and margin side by side before you print labels or send quotations.</p>

<p><em>Disclaimer: Examples use simplified COGS. Tax, rebates, and accounting standards may affect reported margins. Consult a qualified accountant for statutory reporting.</em></p>
HTML;
    }
}
