<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: percentage calculator — discounts, marks, growth.
 */
class PercentageCalculatorDiscountsMarksGrowth
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Percentages are one of the most useful ideas in everyday mathematics, yet they cause confusion because the same English phrase can mean different calculations. A shop sign that says "20% off" is not the same question as "what percent of my salary went to rent?" or "how much did sales grow compared to last year?" Once you learn three core formulas and when to apply each, most percentage problems become quick mental checks—or a single pass through a <a href="/calculator/percentage-calculator">percentage calculator</a>.</p>

<p>This guide walks through percent-of-a-number, reverse percentage (finding the whole from a part), percentage change, and the special cases that trip people up: stacked discounts, mark-up versus margin, exam marks, and growth rates. Every section includes a formula, a worked example, and common mistakes to avoid.</p>

<h2>What a percentage really means</h2>

<p>A percentage is a fraction expressed out of 100. The word comes from <em>per centum</em>—"by the hundred." So 25% means 25 parts per 100, or 25/100, or 0.25 as a decimal. Converting between percent, decimal, and fraction is the first skill to lock in:</p>

<ul>
<li><strong>Percent to decimal:</strong> divide by 100 (15% → 0.15)</li>
<li><strong>Decimal to percent:</strong> multiply by 100 (0.08 → 8%)</li>
<li><strong>Percent of a number:</strong> multiply the decimal form by the whole</li>
</ul>

<p>When someone says "find 15% of 2,000," they mean 0.15 × 2,000 = 300. The whole is 2,000; the part you want is 300.</p>

<h2>Formula 1: Percent of a number</h2>

<p><strong>Part = (Percent ÷ 100) × Whole</strong></p>

<p>Equivalently: <strong>Part = Whole × (Percent / 100)</strong></p>

<h3>Worked example: discount on a jacket</h3>

<p>A jacket priced at NPR 4,500 has a 20% discount during a sale.</p>

<ol>
<li>Convert: 20% = 0.20</li>
<li>Discount amount: 0.20 × 4,500 = NPR 900</li>
<li>Final price: 4,500 − 900 = NPR 3,600</li>
</ol>

<p>Shortcut: paying 80% of the original price means multiply by 0.80 directly: 4,500 × 0.80 = 3,600.</p>

<h2>Formula 2: "X is what percent of Y?"</h2>

<p><strong>Percent = (Part ÷ Whole) × 100</strong></p>

<p>This is the formula behind exam scores, budget shares, and KPI dashboards.</p>

<h3>Worked example: exam marks</h3>

<p>A student scores 42 out of 50 on a mathematics test.</p>

<ol>
<li>Percent = (42 ÷ 50) × 100 = 84%</li>
</ol>

<p>If the course uses weighted grading—say, homework 20%, midterm 30%, final 50%—you cannot average the three percentages directly unless each component carries equal weight. Multiply each score by its weight, add, and you get the course percentage. For example: 90% homework (weight 0.20) contributes 18 points; 75% midterm (0.30) contributes 22.5; 84% final (0.50) contributes 42. Total = 82.5%.</p>

<h2>Formula 3: Percentage change (increase or decrease)</h2>

<p><strong>Percentage change = ((New value − Old value) ÷ Old value) × 100</strong></p>

<p>A positive result is an increase; negative is a decrease. Always divide by the <em>original</em> (old) value, not the new one—this is the most common error in growth-rate problems.</p>

<h3>Worked example: salary raise</h3>

<p>Monthly salary rises from NPR 35,000 to NPR 38,500.</p>

<ol>
<li>Change = 38,500 − 35,000 = 3,500</li>
<li>Percentage change = (3,500 ÷ 35,000) × 100 = 10%</li>
</ol>

<h3>Worked example: price drop</h3>

<p>A phone was NPR 80,000 and is now NPR 68,000.</p>

<ol>
<li>Change = 68,000 − 80,000 = −12,000</li>
<li>Percentage change = (−12,000 ÷ 80,000) × 100 = −15% (a 15% decrease)</li>
</ol>

<h2>Reverse percentage: finding the original price</h2>

<p>When you know the final amount after a percentage change, you work backwards.</p>

<p><strong>Original = Final ÷ (1 ± Rate/100)</strong></p>

<p>Use plus in the denominator for an increase, minus for a decrease—or think in multipliers. After a 20% discount you pay 80% of original, so Original = Final ÷ 0.80.</p>

<h3>Worked example</h3>

<p>You paid NPR 3,600 after 20% off. What was the list price?</p>

<ol>
<li>Original = 3,600 ÷ 0.80 = NPR 4,500</li>
</ol>

<h2>Stacked discounts: why 20% + 10% is not 30%</h2>

<p>Retailers sometimes offer a second discount on an already reduced price. Each percentage applies to the <em>current</em> price, not the original.</p>

<p>Start with NPR 10,000. First discount 20%: 10,000 × 0.80 = 8,000. Second discount 10% on 8,000: 8,000 × 0.90 = 7,200. Combined multiplier = 0.80 × 0.90 = 0.72, which is a 28% total discount—not 30%.</p>

<p>Always multiply the remaining fractions rather than adding percentages.</p>

<h2>Mark-up, margin, and profit percentage</h2>

<p>Business contexts use similar language with different bases:</p>

<ul>
<li><strong>Mark-up on cost:</strong> (Selling price − Cost) ÷ Cost × 100</li>
<li><strong>Margin on selling price:</strong> (Selling price − Cost) ÷ Selling price × 100</li>
</ul>

<p>A item costing NPR 100 sold for NPR 125 has a 25% mark-up on cost but only a 20% margin on selling price (25/125). Confusing these two produces wrong pricing targets.</p>

<h2>Compound growth (brief introduction)</h2>

<p>When a percentage change repeats every period—population, investments, or annual sales—use compound growth:</p>

<p><strong>Final = Initial × (1 + r/100)<sup>n</sup></strong></p>

<p>where <em>r</em> is the rate per period and <em>n</em> is the number of periods. For simple one-step change, <em>n</em> = 1 and the formula reduces to a single percentage increase.</p>

<h2>Common mistakes to avoid</h2>

<ul>
<li><strong>Adding percentages with different bases</strong> — e.g. "I saved 30% on groceries and 10% on fuel" does not mean 40% total savings unless both apply to the same amount.</li>
<li><strong>Using the wrong denominator in percentage change</strong> — always the old value.</li>
<li><strong>Confusing "percent increase" with "times as much"</strong> — a 100% increase doubles the value; "twice as much" is the same, but "200% of" triples it.</li>
<li><strong>Rounding too early</strong> — keep extra digits through multi-step calculations, round at the end.</li>
<li><strong>Treating letter grades as linear</strong> — GPA scales are not uniform percentages; use your institution's conversion table.</li>
</ul>

<h2>Percentage points versus percent change</h2>

<p>News headlines often confuse "percentage points" with "percent change." If interest rates rise from 4% to 6%, that is a 2 <em>percentage point</em> increase—but the <em>relative</em> increase is (6 − 4) ÷ 4 × 100 = 50%. In exams and finance interviews, stating which meaning you intend prevents costly misinterpretation. When comparing election poll shifts or inflation targets, journalists usually mean points; when comparing year-on-year growth, they mean percent change from a base.</p>

<h2>Practical applications in Nepal everyday life</h2>

<p>Percentages surface constantly in Nepali contexts: shop discounts during festival sales, SEE/SLC-style mark sheets, VAT shown as 13% on invoices, loan interest quoted per annum, and remittance fee percentages on transfer apps. A shop offering "flat 25% off" on selected stock still requires you to verify whether the base is the MRP or already-reduced shelf price. School report cards may show subject percentages alongside GPA—convert using the official scale rather than assuming 80% always equals A.</p>

<h2>Quick mental math tricks</h2>

<ul>
<li><strong>10%:</strong> move decimal one place left (NPR 450 → 45)</li>
<li><strong>5%:</strong> half of 10% (22.5)</li>
<li><strong>15%:</strong> 10% + 5% (45 + 22.5 = 67.5)</li>
<li><strong>20%:</strong> divide by 5 (90)</li>
<li><strong>1%:</strong> divide by 100 for fine adjustments</li>
</ul>

<p>These shortcuts help sanity-check calculator output at a counter when you do not have time to reopen a spreadsheet. For weighted grades or multi-year growth, write the intermediate steps once in a notebook so you can spot a transposed digit before signing a contract or submitting an exam appeal.</p>

<h2>When to use a percentage calculator</h2>

<p>Manual calculation is fine for one-step problems. A calculator helps when you are comparing scenarios (10% vs 15% raise over five years), checking stacked discounts, or verifying weighted exam totals. Enter the known values, confirm the formula type, and treat the output as a check—not a substitute for understanding the math.</p>

<h2>References &amp; further reading</h2>

<ul>
<li>David Reimer, <em>Count Like an Egyptian</em> (Princeton University Press) — historical context for fractions and parts-of-a-whole thinking.</li>
<li>Khan Academy, "Percentages" module — free step-by-step lessons at <a href="https://www.khanacademy.org/math/pre-algebra/pre-algebra-ratios-rates/pre-algebra-percent-problems/v/taking-a-percentage-of-a-whole-number" rel="noopener noreferrer" target="_blank">khanacademy.org</a>.</li>
<li>NCERT Class VIII Mathematics, Chapter on Comparing Quantities — standard Indian/Nepali curriculum treatment of ratio, proportion, and percentage.</li>
<li>OpenStax, <em>Elementary Algebra 2e</em> — percentage application sections (open access at openstax.org).</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>How do I calculate percentage increase?</h3>
<p>Subtract the old value from the new value, divide by the old value, and multiply by 100. Example: from 200 to 250 gives (50 ÷ 200) × 100 = 25% increase.</p>

<h3>What is the difference between discount and final price?</h3>
<p>Discount is the amount removed; final price is original minus discount—or original × (1 − discount rate).</p>

<h3>Can I average two percentages?</h3>
<p>Only if they refer to equal-sized wholes or you weight them. The average of 80% on a small quiz and 60% on a large exam is not 70% unless both count equally.</p>

<h3>How do I convert fraction marks to percentage?</h3>
<p>Divide obtained marks by total marks and multiply by 100: (obtained ÷ total) × 100.</p>

<h3>What if the percentage is over 100%?</h3>
<p>That means the part is larger than the reference whole—common in growth ("sales up 150%") or error checks, not in standard exam scoring out of 100.</p>

<h2>Try it yourself</h2>

<p>Practice discounts, mark conversions, and growth rates with our free <a href="/calculator/percentage-calculator">Percentage Calculator</a>. Enter any two known values—percent, whole, or part—and verify your hand calculations in seconds.</p>
HTML;
    }
}
