<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: NEA tariffs, AC tonnage, and home energy math.
 */
class ElectricityBillAndAcSizeBasics
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Electricity bills in Nepal are issued by the Nepal Electricity Authority (NEA) for most households and businesses, with consumption measured in kilowatt-hours (kWh)—commonly called "units" on your bill. Summer bills spike when air conditioners run for hours, yet many buyers choose AC tonnage by showroom advice or neighbour's recommendation rather than room physics. Understanding how wattage, runtime, and tariff slabs combine—and how cooling capacity relates to room size—helps you estimate monthly cost before you buy an appliance and avoid the double penalty of an oversized unit that short-cycles or an undersized one that never stops. This guide covers the energy math, NEA tariff concepts, AC sizing rules of thumb, worked examples, and practical checks you can run with free calculators.</p>

<h2>How NEA bills translate energy to rupees</h2>
<p>Your meter records cumulative energy use in kWh. Each billing period, NEA applies a tariff structure that may include a fixed minimum charge, energy charges that vary by consumption slab, and sometimes time-of-use or demand-related components for larger connections. Domestic low-voltage consumers typically see tiered rates: the first block of monthly units costs less per kWh, and additional blocks cost more. That means running a 1.5-ton AC an extra two hours per day can push you into a higher slab—not just add linear cost.</p>

<p>NEA publishes tariff schedules and occasional revisions through official notices. Always read the effective date on your bill or NEA's website rather than relying on outdated blog figures. For planning purposes, use the current domestic energy rate shown on your last bill or NEA's published schedule, plus any fixed monthly charge printed in the "minimum charge" line.</p>

<h3>Key terms on a typical bill</h3>
<ul>
<li><strong>kWh (unit):</strong> Energy consumed—power (kW) × time (hours).</li>
<li><strong>Connected load / sanctioned load:</strong> Maximum capacity your connection is approved for; exceeding it can incur penalties on some tariff classes.</li>
<li><strong>Fixed charge:</strong> A flat fee tied to your meter category or minimum billing, payable even if consumption is low.</li>
<li><strong>Energy charge:</strong> Variable cost = kWh × rate (possibly slab-wise).</li>
</ul>

<h2>Core energy formulas</h2>

<h3>Daily and monthly kWh</h3>
<p><strong>Energy (kWh) = Power (Watts) × Hours used ÷ 1,000</strong></p>
<p>For one appliance running the same hours every day for 30 days:</p>
<p><strong>Monthly kWh = (Watts × Hours per day × Days) ÷ 1,000</strong></p>

<h3>Cost estimate (single-rate simplification)</h3>
<p><strong>Monthly cost ≈ (Monthly kWh × Rate per kWh) + Fixed charges</strong></p>
<p>When your consumption crosses into a higher NEA slab, split the calculation: first slab kWh × lower rate + remaining kWh × higher rate. A single blended rate is acceptable for rough budgeting but understates cost if you are near a slab boundary.</p>

<h3>AC electrical draw vs cooling capacity</h3>
<p>AC "tonnage" describes cooling capacity (heat removed per hour), not electrical watts directly. A 1-ton unit roughly equals 3.5 kW of cooling. Electrical input depends on the Energy Efficiency Ratio (EER) or star rating: better-rated inverter models draw fewer watts for the same cooling. Rule of thumb for budgeting when nameplate wattage is unknown:</p>
<p><strong>Approximate running watts ≈ Tonnage × 1,000 to 1,200 W</strong> (non-inverter often higher; modern inverter lower at steady state, higher at startup).</p>

<h2>AC tonnage rules of thumb</h2>
<p>Tonnage must match heat gain: floor area, ceiling height, insulation, sun-facing windows, floor level, occupancy, and appliances in the room. Generic guides for residential rooms with standard 9–10 ft ceilings and moderate insulation:</p>

<ul>
<li><strong>Up to ~100 sq ft:</strong> 0.75–1 ton</li>
<li><strong>~100–150 sq ft:</strong> 1–1.5 ton</li>
<li><strong>~150–250 sq ft:</strong> 1.5–2 ton</li>
<li><strong>~250–400 sq ft:</strong> 2–2.5 ton (open layouts may need zoning or multiple units)</li>
</ul>

<p>Adjust upward for top-floor flats, large west-facing glass, kitchen-adjacent spaces, or server/computer heat load. Adjust downward for well-insulated new build with low-E windows. Split AC sizing charts from manufacturers are starting points; NEA's climate zones—from hot Terai to temperate Kathmandu—change the sensible load more than a generic chart shows.</p>

<h3>Why wrong sizing hurts your bill and comfort</h3>
<p><strong>Undersized:</strong> Compressor runs continuously at high power, struggling to reach setpoint on hot afternoons. Units consumed climb; equipment wears faster.</p>
<p><strong>Oversized:</strong> Cools air quickly then shuts off (short cycling). Room feels clammy because dehumidification requires longer run times. Frequent starts increase peak power draw and reduce efficiency.</p>

<h2>Worked example: bedroom AC monthly cost</h2>
<p>A 12×14 ft bedroom ≈ 168 sq ft in Kathmandu—candidate for <strong>1.5 ton inverter split</strong>. Nameplate cooling draw averages <strong>1,400 W</strong> when compressor runs; duty cycle in summer averages <strong>60%</strong> over 8 hours nightly use (compressor not always on at full watts).</p>

<p><strong>Average power ≈ 1,400 × 0.60 = 840 W</strong></p>
<p><strong>Daily kWh = 840 × 8 ÷ 1,000 = 6.72 kWh</strong></p>
<p><strong>Monthly kWh (30 days) = 6.72 × 30 = 201.6 kWh</strong></p>

<p>If your marginal slab rate is <strong>NPR 11.50 per kWh</strong> and fixed charge is NPR 30:</p>
<p><strong>Variable ≈ 201.6 × 11.50 = NPR 2,318</strong></p>
<p><strong>Total AC increment ≈ NPR 2,348</strong> (excluding other appliances and slab interaction).</p>

<p>Add refrigerator (≈1.2 kWh/day), lights, TV, and water pump and you see why total bills exceed AC alone. Use the <a href="/calculator/electricity-bill-calculator">Electricity Bill Calculator</a> to stack multiple loads.</p>

<h3>Worked example: comparing 1 ton vs 1.5 ton purchase</h3>
<p>Room is 120 sq ft—1 ton is sufficient. A salesman pushes 1.5 ton "for faster cooling." The 1.5 ton unit draws ~1,600 W vs ~1,100 W for 1 ton at similar efficiency class. Over 6 hours daily at 55% duty:</p>
<ul>
<li>1 ton: 1,100 × 0.55 × 6 ÷ 1000 × 30 ≈ <strong>108.9 kWh/month</strong></li>
<li>1.5 ton: 1,600 × 0.55 × 6 ÷ 1000 × 30 ≈ <strong>158.4 kWh/month</strong></li>
</ul>
<p>Difference ≈ 50 kWh—roughly NPR 575/month at NPR 11.50/kWh, plus higher purchase price. Right-sizing pays back over years.</p>

<h2>Whole-home bill estimation checklist</h2>
<ol>
<li>List every appliance with wattage (sticker or manual).</li>
<li>Estimate hours per day each runs.</li>
<li>Convert to monthly kWh and sum.</li>
<li>Apply NEA slab rates from your current schedule.</li>
<li>Add fixed and minimum charges.</li>
<li>Compare to last year's same-month bill for sanity.</li>
</ol>

<p>Seasonal loads—electric heaters in winter, AC in summer—should be modelled in separate scenarios rather than averaged across the year.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Using rated watts as continuous draw:</strong> Refrigerators and AC compressors cycle; use average duty cycle.</li>
<li><strong>Ignoring slab escalation:</strong> Extra 50 kWh may trigger disproportionate cost at tier boundaries.</li>
<li><strong>Buying tonnage by room count alone:</strong> Open-plan living-dining-kitchen behaves as one thermal zone.</li>
<li><strong>Skipping voltage stabiliser losses:</strong> Old stabilisers add standby consumption; factor modestly.</li>
<li><strong>Comparing bills across different meter types:</strong> Single-phase domestic vs three-phase commercial tariffs differ.</li>
</ul>

<h2>References &amp; further reading</h2>
<ul>
<li>Nepal Electricity Authority (NEA) — official tariff schedules, consumer guidelines, and meter reading explanations at nea.org.np.</li>
<li>Bureau of Energy Efficiency (India) — star-label methodology for split ACs (often referenced by brands sold in Nepal).</li>
<li>ISO 13253 / regional AC testing standards — cooling capacity measurement definitions underlying "ton" ratings.</li>
<li>International Energy Agency — residential cooling demand trends and efficiency policy summaries.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>What is one "unit" on my NEA bill?</h3>
<p>One unit equals one kilowatt-hour (kWh)—using 1,000 watts for one hour, or 100 watts for ten hours.</p>

<h3>Do inverter ACs always save money?</h3>
<p>Usually at partial load and long runtimes. Extremely short daily use may not repay the premium before you move house. Compare star labels and warranted efficiency at your expected hours.</p>

<h3>How do I size AC for a top-floor room in the Terai?</h3>
<p>Add 0.5 ton or equivalent capacity versus a mid-floor Kathmandu room of the same area, or run heat-load software if available. Sun load dominates.</p>

<h3>Can solar reduce my NEA bill for AC?</h3>
<p>Grid-tied or hybrid systems can offset daytime cooling if generation matches load. Battery-backed night cooling requires larger storage—model kWh separately.</p>

<h3>Where can I estimate room tonnage quickly?</h3>
<p>Use our <a href="/calculator/ac-size-calculator">AC Size Calculator</a> with length, width, height, sun exposure, and occupancy inputs for a structured recommendation before you shop.</p>

<h2>Take the guesswork out of home energy</h2>
<p>Stack appliances and tariff assumptions in the <a href="/calculator/electricity-bill-calculator">Electricity Bill Calculator</a>, then cross-check cooling capacity with the <a href="/calculator/ac-size-calculator">AC Size Calculator</a>. You will enter the showroom—or the next NEA billing cycle—with numbers instead of hope.</p>

<p><em>Disclaimer: NEA tariffs change by government notification. Examples use illustrative rates. Confirm current schedules on official NEA publications. AC installation must follow manufacturer clearance and qualified technician guidelines.</em></p>
HTML;
    }
}
