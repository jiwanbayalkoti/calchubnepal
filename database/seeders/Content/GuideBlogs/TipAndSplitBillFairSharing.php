<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: tip and split bill fair sharing.
 */
class TipAndSplitBillFairSharing
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Group meals should end with good conversation—not a silent panic over mental arithmetic when the bill arrives. Tips, service charges, tax lines, and unequal orders all compete for attention on one piece of paper. A clear sequence (subtotal → tip → total → split) and an agreed fairness rule keep friendships intact and ensure staff are compensated appropriately.</p>

<p>This guide explains how to calculate tips on pre-tax or post-tax bases (depending on local custom), split bills evenly versus by item, handle shared plates and rounding remainders, and avoid the social mistakes that make bill splitting feel unfair. Use our <a href="/calculator/tip-calculator">Tip Calculator</a> and <a href="/calculator/split-bill-calculator">Split Bill Calculator</a> at the table so everyone sees the same numbers before anyone opens a wallet app.</p>

<h2>What tipping means in different contexts</h2>

<p>In the United States and Canada, restaurant tips of 15–20% on the pre-tax subtotal are customary for table service. In much of Europe, service is included; extra tipping is optional. In Nepal and many South Asian cities, restaurants may add a 10% service charge on the bill; an additional cash tip is discretionary and not always expected when service charge is present. Travel guides and the venue's own receipt wording are the best cues—do not assume US norms abroad.</p>

<p>When no service charge appears, a modest tip or rounding up for good service remains a personal choice. The calculators below work in any currency; substitute NPR, USD, or EUR as your bill denomination.</p>

<h2>Basic tip formulas</h2>

<p><strong>Tip amount = Bill base × (Tip percent ÷ 100)</strong></p>

<p><strong>Total with tip = Bill base + Tip amount</strong></p>

<p>The <em>bill base</em> might be:</p>

<ul>
<li>Pre-tax subtotal (common US practice)</li>
<li>Post-tax total (some payers tip on the final number for simplicity)</li>
<li>Subtotal excluding alcohol (occasionally agreed in corporate policies)</li>
</ul>

<p>Pick one base before calculating; mixing bases within the same table causes disagreement.</p>

<h3>Worked example: 18% tip on NPR 5,000 subtotal</h3>

<ol>
<li>Tip = 5,000 × 0.18 = NPR 900</li>
<li>Total = 5,000 + 900 = NPR 5,900 (excluding any tax lines not in subtotal)</li>
</ol>

<h3>Worked example: tip on post-tax total</h3>

<p>Subtotal NPR 5,000; tax NPR 650; total NPR 5,650. Tip 15% on post-tax total:</p>

<ol>
<li>Tip = 5,650 × 0.15 = NPR 847.50</li>
<li>Grand total = 5,650 + 847.50 = NPR 6,497.50</li>
</ol>

<p>Tip is higher than the pre-tax method would yield—acceptable if the group agrees that is the rule.</p>

<h2>Even split among diners</h2>

<p>When everyone ordered similarly or the group prefers simplicity:</p>

<p><strong>Per person = (Bill total + Tip) ÷ Number of people</strong></p>

<h3>Worked example: four friends, even split</h3>

<p>Bill total NPR 8,400; tip 10% on bill = 840; grand total 9,240.</p>

<ol>
<li>Per person = 9,240 ÷ 4 = NPR 2,310</li>
</ol>

<h2>Splitting when orders differ</h2>

<p>Fairness usually means each diner pays for their items plus a share of communal costs (shared appetizers, bottle of water, service charge) plus a proportional share of tip.</p>

<p><strong>Person share = Individual items + (Shared items ÷ Group size) + Tip share</strong></p>

<p>Tip share is often proportional to food subtotal:</p>

<p><strong>Person tip = Total tip × (Person subtotal ÷ Group subtotal)</strong></p>

<h3>Worked example: two diners, unequal orders</h3>

<p>Person A subtotal NPR 1,200; Person B subtotal NPR 2,800; shared appetizer NPR 600 split equally (300 each). Group subtotal 4,600. Tip 15% = 690.</p>

<ul>
<li>Person A tip share = 690 × (1,200 ÷ 4,600) ≈ 180</li>
<li>Person B tip share = 690 × (2,800 ÷ 4,600) ≈ 420</li>
<li>Person A total ≈ 1,200 + 300 + 180 = 1,680</li>
<li>Person B total ≈ 2,800 + 300 + 420 = 3,520</li>
</ul>

<p>Check sum ≈ 5,200 before tax lines; add tax per local receipt logic if billed separately.</p>

<h2>Service charge vs tip</h2>

<p>If the receipt shows "Service charge 10%," that money is typically retained by the house under local labour rules—it is not necessarily passed to your server as a discretionary gratuity. Read the fine print. You may still add a small extra tip for exceptional service where culturally appropriate. Do not double-count service charge as both a mandatory fee and a full voluntary tip unless the group consciously chooses to.</p>

<h2>Splitting with tax and mixed payment methods</h2>

<p>Sales tax or VAT may appear as one line at the bottom. Options:</p>

<ul>
<li><strong>Proportional tax:</strong> assign tax by each person's share of subtotal (fair when tax scales with food).</li>
<li><strong>Even tax split:</strong> divide tax equally (simple but slightly favors heavy eaters when tax is low).</li>
</ul>

<p>When one person pays card and others cash, reconcile to the penny on the spot—unresolved "IOUs" erode trust faster than rounding errors.</p>

<h2>Handling rounding and remainders</h2>

<p>Splitting NPR 9,241 among three people gives 3,080.33 recurring. Practical approaches:</p>

<ul>
<li>Round two payers down to 3,080 and one up to 3,081</li>
<li>Use mobile wallets that accept decimal paisa</li>
<li>Add remainder to tip rather than arguing over one rupee</li>
</ul>

<p>The <a href="/calculator/split-bill-calculator">Split Bill Calculator</a> reduces friction by showing per-person totals with remainder notes.</p>

<h2>Itemisation without itemised receipts</h2>

<p>When the restaurant gives one total, reconstruct from memory or order slips. Shared dishes need explicit agreement ("We split the pizza thirds, not halves"). Vegetarian-only diners should not subsidise a whole group's meat platter unless they chose to share.</p>

<h2>Corporate meals and receipts</h2>

<p>Company policies may cap tip percent, require itemised receipts, or disallow alcohol reimbursement. Calculate tip on the reimbursable subtotal only, and note the policy on the expense form. A photo of the split breakdown saves finance back-and-forth.</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Splitting pre-tip only</strong>, leaving one person to cover the entire gratuity on their card float.</li>
<li><strong>Double tipping</strong> when service charge already satisfies local norm.</li>
<li><strong>Ignoring tax line</strong> so totals do not match the receipt.</li>
<li><strong>Equal split when one diner ordered alcohol for the table alone</strong>.</li>
<li><strong>Tip on discounted subtotal confusion</strong> when coupons apply.</li>
<li><strong>Assuming US 20% abroad</strong> where service is included or tipping is modest.</li>
</ul>

<h2>Large groups and partial attendance</h2>

<p>Parties of eight or more often trigger automatic service charge abroad; in Nepal some banquet venues quote per-plate packages inclusive of service. When two people leave early, the remaining group should recalculate shared items excluding absent diners unless everyone agreed to subsidise upfront. For birthday dinners where one guest is treated, exclude their share explicitly rather than quietly inflating everyone else's split—transparency beats resentment.</p>

<h2>Digital wallets and split requests</h2>

<p>Apps like eSewa, Khalti, or international Splitwise equivalents let one payer front the bill and request exact amounts. Enter the calculator totals as memo notes so paybacks match the agreed split. Waiting days to settle creates awkwardness; same-evening transfer is the norm among colleagues.</p>

<h2>Etiquette for smoother splits</h2>

<ol>
<li>Discuss split method when ordering, not after dessert.</li>
<li>One person runs the calculator openly; secrecy breeds suspicion.</li>
<li>Pay promptly when someone fronts the card—interest-free loans are not part of dining out.</li>
<li>If someone joins late, exclude their share from shared starters ordered before they arrived unless offered.</li>
<li>Thank the server directly when tipping cash in jurisdictions where it reaches staff.</li>
</ol>

<h2>References &amp; further reading</h2>

<ul>
<li>Emily Post Institute — gratuity etiquette guides for United States dining (<a href="https://emilypost.com" rel="noopener noreferrer" target="_blank">emilypost.com</a>).</li>
<li>Conde Nast Traveler / Lonely Planet — tipping norms by country for travellers.</li>
<li>Michael Lynn, Cornell School of Hotel Administration research on tipping behaviour (scholarly articles on tip percentages and service quality).</li>
<li>Consumer Financial Protection Bureau — budgeting and shared expense tips for roommates and groups (consumerfinance.gov).</li>
<li>Local restaurant association guidance where service charge disclosure is regulated.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>Should I tip on tax?</h3>
<p>US custom often tips on pre-tax subtotal; some payers tip on post-tax total for simplicity. Agree within your group.</p>

<h3>What if service charge is already on the bill?</h3>
<p>Treat it as a mandatory fee set by the venue; additional tipping is optional and culture-dependent.</p>

<h3>How do we split a bottle of wine?</h3>
<p>Split among those who drank, or divide bottle price equally if the table shared glasses—decide when ordering.</p>

<h3>Is an even split ever fair with unequal orders?</h3>
<p>It is socially fair when the group explicitly chooses simplicity over precision—common among close friends who dine together often.</p>

<h3>Can apps replace mental math?</h3>
<p>Yes—calculators reduce error; the social step is agreeing on rules before paying.</p>

<h2>Split fairly, tip confidently</h2>

<p>Run tip scenarios with our <a href="/calculator/tip-calculator">Tip Calculator</a>, divide totals fairly with the <a href="/calculator/split-bill-calculator">Split Bill Calculator</a>, and spend the last minutes of the meal on conversation—not disputed arithmetic.</p>
HTML;
    }
}
