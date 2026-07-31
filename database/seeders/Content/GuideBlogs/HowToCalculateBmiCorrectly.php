<?php

namespace Database\Seeders\Content\GuideBlogs;

class HowToCalculateBmiCorrectly
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Body Mass Index (BMI) is one of the most widely used screening tools for classifying weight status in adults. Public health agencies, clinics, and fitness professionals rely on BMI because it is simple, inexpensive, and correlates reasonably well with body fat at the population level. Yet many people calculate it incorrectly—mixing up units, rounding too early, or interpreting the number without understanding its limits.</p>

<p>This guide explains how to calculate BMI correctly using metric and imperial formulas, how the World Health Organization (WHO) defines weight categories, and where BMI falls short. Whether you are tracking your own health or validating results from an online tool, the steps below will help you produce a reliable figure and interpret it responsibly.</p>

<p>At the end, you can verify your work instantly with the free <a href="/calculator/bmi-calculator">BMI calculator on CalchubNepal</a>, which accepts both kilograms and pounds and handles unit conversion automatically.</p>

<h2>What BMI measures—and what it does not</h2>

<p>BMI is defined as body weight divided by the square of height. It does not directly measure body fat, muscle mass, bone density, or fat distribution. A rugby player and a sedentary person of the same height and weight can share an identical BMI despite very different body compositions. For population-level screening, that trade-off is acceptable; for individual diagnosis, BMI should be paired with waist circumference, clinical history, and other assessments.</p>

<h3>Why height is squared</h3>

<p>Weight typically scales with the cube of linear dimensions, but BMI uses height squared so the index stays relatively stable across adult heights. This empirical choice, adopted by epidemiologists decades ago, makes cross-population comparisons practical even though it is not a perfect physical model of adiposity.</p>

<h2>BMI formulas: metric and imperial</h2>

<h3>Metric formula (kg and metres)</h3>

<p>The standard WHO formula uses kilograms and metres:</p>

<p><strong>BMI = weight (kg) ÷ [height (m)]²</strong></p>

<p>Example: a person weighing 70 kg who is 1.75 m tall:</p>
<ul>
<li>Height squared = 1.75 × 1.75 = 3.0625 m²</li>
<li>BMI = 70 ÷ 3.0625 = <strong>22.9 kg/m²</strong></li>
</ul>

<h3>Imperial formula (pounds and inches)</h3>

<p>When height is in inches and weight in pounds, use:</p>

<p><strong>BMI = [weight (lb) ÷ height (in)²] × 703</strong></p>

<p>The constant 703 converts the result to kg/m². Example: 154 lb, 68 inches (5 ft 8 in):</p>
<ul>
<li>68² = 4,624 in²</li>
<li>154 ÷ 4,624 = 0.0333</li>
<li>0.0333 × 703 = <strong>23.4 kg/m²</strong></li>
</ul>

<h3>Using centimetres instead of metres</h3>

<p>If height is in centimetres, convert to metres first (divide by 100) or use:</p>

<p><strong>BMI = weight (kg) ÷ [height (cm) ÷ 100]²</strong></p>

<p>Common error: entering 175 cm as 175 m produces a BMI near zero. Always express height in metres or apply the centimetre conversion explicitly.</p>

<h2>WHO adult BMI categories</h2>

<p>For adults aged 18 and older, WHO classifies BMI as follows (same thresholds used in Nepal and most countries):</p>

<ul>
<li><strong>Underweight:</strong> BMI &lt; 18.5</li>
<li><strong>Normal weight:</strong> 18.5 – 24.9</li>
<li><strong>Overweight:</strong> 25.0 – 29.9</li>
<li><strong>Obesity Class I:</strong> 30.0 – 34.9</li>
<li><strong>Obesity Class II:</strong> 35.0 – 39.9</li>
<li><strong>Obesity Class III:</strong> ≥ 40.0</li>
</ul>

<p>Our 70 kg, 1.75 m example (BMI 22.9) falls in the normal range. WHO notes that BMI cut-offs may differ for Asian populations in some national guidelines; consult local health advisories if you are screening for metabolic risk in South Asia.</p>

<h2>Worked examples step by step</h2>

<h3>Example 1: Converting feet and inches</h3>

<p>A student is 5 ft 4 in (64 in) and weighs 132 lb:</p>
<ul>
<li>BMI = (132 ÷ 64²) × 703 = (132 ÷ 4,096) × 703 = 0.0322 × 703 = <strong>22.7</strong></li>
</ul>

<h3>Example 2: Metric with cm input</h3>

<p>Weight 58 kg, height 162 cm:</p>
<ul>
<li>Height in metres = 1.62 m; 1.62² = 2.6244</li>
<li>BMI = 58 ÷ 2.6244 = <strong>22.1</strong></li>
</ul>

<h3>Example 3: Borderline overweight</h3>

<p>90 kg at 1.78 m: 1.78² = 3.1684; BMI = 90 ÷ 3.1684 = <strong>28.4</strong> (overweight, not obese).</p>

<h2>BMI for children and adolescents</h2>

<p>BMI for people under 18 is not interpreted with fixed adult cut-offs. Instead, age- and sex-specific percentile charts (often from CDC or WHO growth references) compare a child’s BMI to peers. Paediatric assessment requires growth charts, not the adult calculator alone.</p>

<h2>Common mistakes when calculating BMI</h2>

<ul>
<li><strong>Wrong units:</strong> Using pounds with metres or kilograms with inches without converting.</li>
<li><strong>Forgetting to square height:</strong> Dividing weight by height once instead of height squared.</li>
<li><strong>Early rounding:</strong> Rounding height to 1.8 m instead of 1.78 m can shift BMI by several tenths.</li>
<li><strong>Shoes and clothing:</strong> Clinical BMI uses bare-foot height and light clothing weight; home measurements vary.</li>
<li><strong>Confusing BMI with body fat percentage:</strong> High muscle mass can yield “overweight” BMI without excess fat.</li>
<li><strong>Using BMI alone for athletes:</strong> Strength athletes may need skinfold tests, DEXA, or waist-to-height ratio.</li>
</ul>

<h2>When to use complementary measures</h2>

<p>WHO and national guidelines recommend waist circumference alongside BMI for adults, especially when BMI is 25–34.9. Waist measures central adiposity linked to type 2 diabetes and cardiovascular disease. For older adults, BMI in the “normal” range can still mask sarcopenia (low muscle mass); clinical judgement matters.</p>

<h2>Recording measurements consistently</h2>

<p>Reproducible BMI starts with reproducible inputs. Measure height without shoes, heels together, back against a flat wall, and eyes forward. Use a stadiometer or a tape measure dropped vertically from a right-angle book on the head. Weight should be taken on a calibrated scale, ideally morning after voiding, in minimal clothing. Even half a centimetre of height error matters: at 70 kg, changing height from 1.74 m to 1.75 m shifts BMI from 23.1 to 22.9. Document whether you used home or clinical measurements when comparing over time.</p>

<h3>BMI in public health and insurance screening</h3>

<p>Population surveys (DHS, STEPS, national NCD risk factor reports) aggregate BMI to track undernutrition and obesity trends. Insurance and workplace wellness programmes sometimes use BMI bands for premium incentives—always subject to local regulation. These applications rely on standardized measurement protocols; home calculations are useful for personal awareness but may not match clinic values used on official forms.</p>

<h2>Quick reference: BMI at common heights (70 kg example)</h2>

<p>At fixed weight, taller people have lower BMI. For 70 kg:</p>
<ul>
<li>1.60 m → BMI 27.3 (overweight)</li>
<li>1.65 m → BMI 25.7 (overweight)</li>
<li>1.70 m → BMI 24.2 (normal)</li>
<li>1.75 m → BMI 22.9 (normal)</li>
<li>1.80 m → BMI 21.6 (normal)</li>
</ul>
<p>This table illustrates why BMI cannot be judged from weight alone—you need both dimensions.</p>

<h2>Tracking BMI over time</h2>

<p>When monitoring weight change programmes, plot BMI monthly using the same scale and height assumption (adults rarely grow taller). A drop from BMI 28.4 to 26.1 over six months reflects meaningful progress even if the “normal” threshold at 24.9 has not yet been reached. Pair trend lines with waist measurement and strength metrics so you do not over-interpret BMI alone during resistance training phases where weight may stall while body composition improves.</p>

<h2>References &amp; further reading</h2>

<ul>
<li>World Health Organization. <em>Body mass index – BMI</em> (WHO fact sheet on BMI classification for adults). <a href="https://www.who.int/europe/news-room/fact-sheets/item/a-healthy-lifestyle---who-recommendations" rel="noopener noreferrer" target="_blank">who.int</a></li>
<li>WHO Expert Consultation. Appropriate body-mass index for Asian populations and its implications for policy and intervention strategies. <em>The Lancet</em>, 2004 (discusses alternative cut-offs in some Asian contexts).</li>
<li>Centers for Disease Control and Prevention (CDC). About Adult BMI. <a href="https://www.cdc.gov/bmi/about/index.html" rel="noopener noreferrer" target="_blank">cdc.gov/bmi</a></li>
<li>National Institutes of Health (NIH). Clinical Guidelines on the Identification, Evaluation, and Treatment of Overweight and Obesity in Adults, 1998 (foundational U.S. adult BMI framework).</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>Is BMI accurate for everyone?</h3>
<p>No. BMI is a screening tool, not a diagnostic test. It works reasonably for average adults but can misclassify muscular individuals, some older adults, and certain ethnic groups where metabolic risk rises at lower BMI values.</p>

<h3>Should I use kg/cm or lb/in?</h3>
<p>Either is fine if you apply the matching formula. Online calculators accept both and convert internally. Consistency matters more than which system you choose.</p>

<h3>What BMI is considered healthy for adults?</h3>
<p>WHO defines 18.5–24.9 as normal weight for adults 18+. Individual targets may differ based on muscle mass, pregnancy, or medical conditions—ask a healthcare provider.</p>

<h3>Can I calculate BMI during pregnancy?</h3>
<p>Standard adult BMI is not used to assess healthy weight gain in pregnancy. Obstetric charts based on pre-pregnancy BMI guide recommended gain instead.</p>

<h3>How often should I recalculate BMI?</h3>
<p>Recalculate when weight or height changes meaningfully. For routine monitoring, monthly or quarterly is enough for most people tracking lifestyle changes.</p>

<h2>Calculate your BMI now</h2>

<p>Skip manual arithmetic and unit headaches: use the <a href="/calculator/bmi-calculator"><strong>CalchubNepal BMI calculator</strong></a> to enter your weight and height in metric or imperial units and see your category instantly. Pair the result with waist measurement and professional advice for a complete picture of your health.</p>
HTML;
    }
}
