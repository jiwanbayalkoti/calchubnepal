<?php

namespace Database\Seeders\Content\GuideBlogs;

class DailyCalorieNeedsBmrTdee
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Knowing how many calories your body needs each day is the foundation of weight management, athletic fuelling, and meal planning. Two numbers dominate the conversation: Basal Metabolic Rate (BMR)—energy burned at complete rest—and Total Daily Energy Expenditure (TDEE), which includes activity, digestion, and daily movement. Confusing these terms leads to eating too much or too little.</p>

<p>This guide walks through established BMR equations (Mifflin-St Jeor and Harris-Benedict), how to multiply BMR into TDEE using activity factors, and worked examples for maintenance, fat loss, and muscle gain. You will also learn common estimation errors that skew calorie targets by hundreds of kilocalories per day.</p>

<p>When you are ready to run your own numbers, the <a href="/calculator/calorie-calculator">CalchubNepal calorie calculator</a> applies these formulas automatically and shows BMR alongside estimated daily needs.</p>

<h2>BMR vs TDEE: definitions</h2>

<h3>Basal Metabolic Rate (BMR)</h3>

<p>BMR is the energy (in kilocalories, often written as “calories”) required to maintain vital functions—breathing, circulation, cell repair—while at rest, typically measured after an overnight fast in a thermoneutral environment. In everyday use, BMR equations estimate Resting Metabolic Rate (RMR), which is slightly higher because RMR measurement conditions are less strict. The terms are often used interchangeably in calculators.</p>

<h3>Total Daily Energy Expenditure (TDEE)</h3>

<p>TDEE is the total energy you burn in 24 hours:</p>

<p><strong>TDEE = BMR × Activity Factor</strong></p>

<p>Activity factors account for occupational movement, exercise, and non-exercise activity thermogenesis (NEAT)—fidgeting, walking to the bus, household chores. TDEE is the number you use to set maintenance calories; adjust below or above for loss or gain.</p>

<h2>Mifflin-St Jeor equation (recommended default)</h2>

<p>Published in 1990, the Mifflin-St Jeor equation is widely regarded as more accurate for modern populations than older formulas. It uses weight in kilograms, height in centimetres, and age in years.</p>

<h3>Men</h3>
<p><strong>BMR = (10 × weight kg) + (6.25 × height cm) − (5 × age) + 5</strong></p>

<h3>Women</h3>
<p><strong>BMR = (10 × weight kg) + (6.25 × height cm) − (5 × age) − 161</strong></p>

<h3>Worked example (male, 30 years, 75 kg, 178 cm)</h3>
<ul>
<li>BMR = (10 × 75) + (6.25 × 178) − (5 × 30) + 5</li>
<li>BMR = 750 + 1,112.5 − 150 + 5 = <strong>1,717.5 kcal/day</strong></li>
</ul>

<h3>Worked example (female, 28 years, 62 kg, 165 cm)</h3>
<ul>
<li>BMR = (10 × 62) + (6.25 × 165) − (5 × 28) − 161</li>
<li>BMR = 620 + 1,031.25 − 140 − 161 = <strong>1,350.25 kcal/day</strong></li>
</ul>

<h2>Harris-Benedict equation (historical alternative)</h2>

<p>The revised Harris-Benedict equations (1984) remain common in textbooks and some apps.</p>

<h3>Men</h3>
<p><strong>BMR = 88.362 + (13.397 × weight kg) + (4.799 × height cm) − (5.677 × age)</strong></p>

<h3>Women</h3>
<p><strong>BMR = 447.593 + (9.247 × weight kg) + (3.098 × height cm) − (4.330 × age)</strong></p>

<p>For the same 75 kg, 178 cm, 30-year-old male, Harris-Benedict yields roughly 1,781 kcal—about 64 kcal higher than Mifflin-St Jeor. Differences of 50–150 kcal between equations are normal; pick one method and stay consistent when tracking progress.</p>

<h2>Activity multipliers for TDEE</h2>

<p>After BMR, multiply by an activity factor. Values below follow common fitness-nutrition conventions (similar to those used by USDA and exercise physiology texts):</p>

<ul>
<li><strong>Sedentary</strong> (desk job, little exercise): BMR × 1.2</li>
<li><strong>Lightly active</strong> (light exercise 1–3 days/week): BMR × 1.375</li>
<li><strong>Moderately active</strong> (moderate exercise 3–5 days/week): BMR × 1.55</li>
<li><strong>Very active</strong> (hard exercise 6–7 days/week): BMR × 1.725</li>
<li><strong>Extra active</strong> (physical job + daily training): BMR × 1.9</li>
</ul>

<h3>TDEE example (male above, moderately active)</h3>
<ul>
<li>TDEE = 1,717.5 × 1.55 = <strong>2,662 kcal/day</strong> (maintenance estimate)</li>
</ul>

<h2>Setting calorie targets for goals</h2>

<h3>Weight maintenance</h3>
<p>Eat approximately TDEE. Weigh yourself weekly; if average weight drifts up or down over two to three weeks, adjust intake by 100–200 kcal.</p>

<h3>Fat loss</h3>
<p>A deficit of 500 kcal/day from TDEE targets roughly 0.5 kg (1 lb) loss per week, acknowledging that water and glycogen fluctuate. Aggressive deficits below BMR are generally unsustainable and risk muscle loss unless medically supervised.</p>

<p>Example: TDEE 2,662 → loss target ≈ 2,162 kcal/day.</p>

<h3>Muscle gain</h3>
<p>A surplus of 250–350 kcal/day supports lean gain while limiting excess fat. Monitor scale and strength trends over eight to twelve weeks.</p>

<h2>Thermic effect of food and NEAT</h2>

<p>Digesting protein, carbohydrates, and fats burns roughly 10% of ingested energy—the Thermic Effect of Food (TEF). High-protein diets slightly raise TEF. NEAT varies enormously between individuals with identical gym routines; two people labelled “moderately active” may differ by 200–400 kcal daily. That is why formulas estimate starting points, not immutable truths.</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Using TDEE as BMR:</strong> Eating “1,600 calories” because that was your calculated BMR while living an active life creates an accidental large deficit.</li>
<li><strong>Overstating activity level:</strong> One gym session per week does not justify “very active.” Honesty improves accuracy.</li>
<li><strong>Ignoring weekly averages:</strong> Single-day intake swings matter less than seven-day averages versus scale trend.</li>
<li><strong>Switching equations mid-plan:</strong> Changing from Harris-Benedict to Mifflin-St Jeor mid-diet confuses progress interpretation.</li>
<li><strong>Forgetting liquid calories:</strong> Drinks can add 300–600 kcal without satiety.</li>
<li><strong>Expecting linear weight change:</strong> Menstrual cycle, sodium, and glycogen alter short-term weight independent of fat.</li>
</ul>

<h2>Special populations</h2>

<p>Equations were validated mainly on adults aged 18–70 with typical body compositions. They are less reliable for competitive bodybuilders, pregnancy, clinical underweight, or certain endocrine disorders. Paediatric needs use growth-based charts, not adult BMR formulas. Older adults may lose lean mass; protein intake and resistance training influence whether a standard TDEE deficit preserves muscle.</p>

<h2>Macronutrient planning after you know TDEE</h2>

<p>Once TDEE is estimated, divide calories across protein, carbohydrates, and fats according to goals. General fitness guidance often suggests protein near 1.6–2.2 g per kg body weight during fat loss to protect lean tissue, with remaining calories split between carbs (fuel for training) and fats (hormone health). These ranges are starting points—not medical prescriptions. Endurance athletes may need higher carbohydrate fractions; sedentary adults may prefer moderate carbs and higher fibre.</p>

<h3>Tracking intake against TDEE</h3>

<p>Food diaries (paper or apps) work best when you log for at least seven consecutive days including weekends. Compare average daily intake to TDEE. If weight is stable, your real TDEE may differ from the formula—adjust empirically. Weighing food for one week calibrates portion estimates; most people underestimate oils, nuts, and beverages.</p>

<h2>Katch-McArdle and body-fat-based estimates</h2>

<p>If you know body fat percentage from DEXA or reliable bioimpedance, the Katch-McArdle equation uses lean body mass:</p>

<p><strong>BMR = 370 + (21.6 × lean mass in kg)</strong></p>

<p>Lean mass = weight × (1 − body fat fraction). Example: 80 kg at 20% fat → lean mass 64 kg → BMR ≈ 370 + 1,382 = 1,752 kcal. This can outperform age-height-weight equations for muscular or very lean individuals when body composition data is trustworthy.</p>

<h2>References &amp; further reading</h2>

<ul>
<li>Mifflin MD, St Jeor ST, et al. A new predictive equation for resting energy expenditure in healthy individuals. <em>American Journal of Clinical Nutrition</em>, 1990;51(2):241–247.</li>
<li>Roza AM, Shizgal HM. The Harris Benedict equation reevaluated. <em>American Journal of Clinical Nutrition</em>, 1984;40(1):168–182.</li>
<li>Food and Agriculture Organization / WHO / UNU. <em>Human energy requirements</em>, FAO Food and Nutrition Technical Report Series (principles of energy expenditure).</li>
<li>Academy of Nutrition and Dietetics. Position on very low-calorie diets and individualized medical nutrition therapy.</li>
<li>Institute of Medicine (National Academies). <em>Dietary Reference Intakes for Energy</em>, macronutrient guidance tied to estimated energy requirements.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>Which BMR formula is most accurate?</h3>
<p>Mifflin-St Jeor is generally preferred for healthy adults. Indirect calorimetry (lab measurement) is the gold standard but impractical for daily use.</p>

<h3>Should I eat below my BMR to lose weight faster?</h3>
<p>Usually no. Deficits are typically set relative to TDEE, not by dropping below BMR. Very low intake can impair recovery, hormones, and adherence.</p>

<h3>Do men and women use different TDEE multipliers?</h3>
<p>Activity multipliers are the same; sex differences are captured inside the BMR equations through different constants.</p>

<h3>How do I adjust if weight loss stalls?</h3>
<p>After two to three weeks of stable weight at a supposed deficit, reduce intake by 100–150 kcal or add modest NEAT (walking). Re-check activity classification honestly.</p>

<h3>Are smartwatch calorie burns accurate?</h3>
<p>Wearables estimate TDEE components with variable error. Use them for trend direction, not as exact balances against food logs.</p>

<h2>Estimate your daily calories</h2>

<p>Enter age, sex, weight, height, and activity level in the <a href="/calculator/calorie-calculator"><strong>CalchubNepal calorie calculator</strong></a> to see BMR and TDEE in seconds. Use the result as a starting budget, then refine with real-world scale data over a few weeks.</p>
HTML;
    }
}
