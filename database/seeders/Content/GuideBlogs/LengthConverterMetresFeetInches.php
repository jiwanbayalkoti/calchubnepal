<?php

namespace Database\Seeders\Content\GuideBlogs;

class LengthConverterMetresFeetInches
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Length conversion between metres and feet/inches appears in construction drawings, sports, travel, and science homework. The United States and some legacy documents still use imperial feet, while Nepal, India, and most countries work in SI metres. Converting incorrectly—especially treating feet as metres or confusing decimal feet with feet-plus-inches—causes expensive field mistakes and failed exam answers.</p>

<p>This guide gives exact conversion factors from the International System of Units (SI), step-by-step methods for metres ↔ feet ↔ inches, compound imperial notation (5 ft 8 in), and worked examples you can reverse-check. All factors trace to the definition of the inch in terms of the metre.</p>

<p>Skip manual arithmetic with the <a href="/calculator/length-converter">CalchubNepal length converter</a>, then use the formulas below to understand how the tool arrives at each value.</p>

<h2>Official definition: inch and metre</h2>

<p>Since 1959, the international inch is defined exactly as:</p>

<p><strong>1 inch = 25.4 mm = 0.0254 m</strong></p>

<p>Therefore:</p>

<p><strong>1 foot = 12 inches = 12 × 0.0254 m = 0.3048 m (exact)</strong></p>

<p>And conversely:</p>

<p><strong>1 metre = 1 ÷ 0.3048 ft ≈ 3.280839895 ft (exact rational 5000/1524 ft)</strong></p>

<p>These are not approximations for commerce or engineering—they are definitional. The BIPM SI Brochure and NIST SP 330 publish this relationship.</p>

<h2>Metres to feet</h2>

<p><strong>Feet = Metres ÷ 0.3048</strong></p>

<p>Or multiply by 3.280839895.</p>

<h3>Example: 10 metres to feet</h3>
<ul>
<li>10 ÷ 0.3048 = <strong>32.8084 ft</strong></li>
<li>Or 10 × 3.280839895 = 32.8084 ft</li>
</ul>

<h3>Example: 1.75 m (typical height)</h3>
<ul>
<li>1.75 ÷ 0.3048 = <strong>5.74147 ft</strong></li>
</ul>

<h2>Feet to metres</h2>

<p><strong>Metres = Feet × 0.3048</strong></p>

<h3>Example: 6 ft to metres</h3>
<ul>
<li>6 × 0.3048 = <strong>1.8288 m</strong></li>
</ul>

<h2>Feet and inches to metres (compound imperial)</h2>

<p>Human heights and building dimensions often appear as 5 ft 8 in—not as decimal feet.</p>

<p><strong>Step 1:</strong> Total inches = (feet × 12) + inches<br>
<strong>Step 2:</strong> Metres = Total inches × 0.0254</p>

<h3>Example: 5 ft 8 in to metres</h3>
<ul>
<li>Total inches = (5 × 12) + 8 = 68 in</li>
<li>Metres = 68 × 0.0254 = <strong>1.7272 m</strong></li>
</ul>

<h3>Example: 12 ft 6 in to metres</h3>
<ul>
<li>Total inches = 150 + 6 = 156 in</li>
<li>Metres = 156 × 0.0254 = <strong>3.9624 m</strong></li>
</ul>

<h2>Metres to feet and inches</h2>

<p><strong>Step 1:</strong> Total feet decimal = metres ÷ 0.3048<br>
<strong>Step 2:</strong> Whole feet = floor(total feet)<br>
<strong>Step 3:</strong> Remaining inches = (total feet − whole feet) × 12, rounded sensibly</p>

<h3>Example: 1.80 m to ft/in</h3>
<ul>
<li>Total ft = 1.80 ÷ 0.3048 = 5.905512 ft</li>
<li>Whole feet = 5</li>
<li>Inches = 0.905512 × 12 = 10.866 → <strong>5 ft 10.9 in</strong> (≈ 5 ft 11 in for human height)</li>
</ul>

<h2>Inches to centimetres and metres</h2>

<p><strong>Centimetres = Inches × 2.54</strong> (exact)</p>

<p><strong>Metres = Inches × 0.0254</strong></p>

<h3>Example: 48 inches to metres</h3>
<ul>
<li>48 × 0.0254 = <strong>1.2192 m</strong></li>
</ul>

<h2>Millimetres and construction drawings</h2>

<p>Architectural plans in Nepal and India dimension in millimetres (e.g., 2750 mm door height). Convert:</p>

<p><strong>Metres = Millimetres ÷ 1000</strong></p>

<p>2750 mm = 2.750 m = 9 ft 0.21 in approximately.</p>

<h2>Quick reference table</h2>

<table>
<thead>
<tr><th>Length</th><th>Metres</th><th>Feet (decimal)</th><th>Feet + inches</th></tr>
</thead>
<tbody>
<tr><td>1 m</td><td>1.000</td><td>3.281</td><td>3 ft 3.37 in</td></tr>
<tr><td>1 ft</td><td>0.305</td><td>1.000</td><td>1 ft 0 in</td></tr>
<tr><td>1 in</td><td>0.0254</td><td>0.0833</td><td>—</td></tr>
<tr><td>100 m</td><td>100</td><td>328.084</td><td>328 ft 1.0 in</td></tr>
<tr><td>1 km</td><td>1000</td><td>3280.84</td><td>—</td></tr>
</tbody>
</table>

<h2>Yards and miles (brief)</h2>

<p><strong>1 yard = 3 ft = 0.9144 m (exact)</strong></p>

<p><strong>1 mile = 5280 ft = 1609.344 m (exact international mile)</strong></p>

<p>Road signs in the US use miles; Nepal uses kilometres. Convert km via metres: 1 km = 1000 m.</p>

<h2>Significant figures and rounding</h2>

<p>Engineering tolerances dictate rounding. For surveying, keep four or more decimal places in metres. For verbal height, round inches to the nearest whole inch. Never round intermediate factors (use 0.3048 exactly, not 0.3).</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Using 3.28 as exact:</strong> Acceptable for rough estimates; errors accumulate on long distances.</li>
<li><strong>Treating 5.8 ft as 5 ft 8 in:</strong> 5.8 ft = 5 ft 9.6 in—decimal feet ≠ feet.inches notation.</li>
<li><strong>Double conversion:</strong> Converting m→ft→m with rounded ft loses precision.</li>
<li><strong>Mixing US survey foot:</strong> Rare legacy US survey foot = 1200/3937 m differs slightly; modern international foot is 0.3048 m—use international unless old cadastral maps say otherwise.</li>
<li><strong>Forgetting stack height in inches:</strong> Adding feet and inches as 58 instead of 68 total inches.</li>
<li><strong>Wrong direction in BMI or speed follow-ons:</strong> Height must land in metres consistently after conversion.</li>
</ul>

<h2>Chains of conversion in real tasks</h2>

<p><strong>Room perimeter:</strong> 4.2 m × 3.6 m → convert to feet for US tile quotes: 4.2 ÷ 0.3048 = 13.78 ft. <strong>Running track lane:</strong> 400 m = 1312.34 ft. <strong>Aviation:</strong> Feet remain common for altitude; pilots use charts with exact factors.</p>

<h2>Area and volume conversions (related pitfalls)</h2>

<p>Length conversion factors square and cube for area and volume. <strong>1 m² = 10.7639 ft²</strong> (not 3.28² approximated loosely). <strong>1 m³ = 35.3147 ft³</strong>. A common error is converting 10 m × 10 m room to feet by multiplying 10 × 3.28 twice correctly but then reporting 328 ft² instead of 1076 ft² because a single edge was converted twice. Always convert each dimension or convert area with squared factors.</p>

<h3>Example: floor area 4.5 m × 3.0 m in square feet</h3>
<ul>
<li>Area in m² = 13.5 m²</li>
<li>Area in ft² = 13.5 × 10.7639 = <strong>145.3 ft²</strong></li>
<li>Verify via edges: 4.5 m = 14.764 ft; 3.0 m = 9.843 ft; product ≈ 145.3 ft²</li>
</ul>

<h2>Nautical miles and aviation feet</h2>

<p>Mariners use the international nautical mile = 1852 m exactly. Aviation altitudes in feet above sea level still convert to metres with × 0.3048 for METAR comparisons. Do not confuse ground distance (metres/km) with elevation (feet)—different domains, same inch definition.</p>

<h2>Historical units in Nepal and India</h2>

<p>Traditional land units (ropani, aana, bigha, katha) persist in informal property talk. Official surveys use metric. When a deed mentions ropani, use government conversion tables for that district rather than guessing. Construction bills of quantities are metric-first under standard building codes.</p>

<h2>Teaching conversion without rounding drift</h2>

<p>In classrooms, require students to keep full calculator precision until the final answer, then round to three significant figures. Ask them to reverse-convert: metres → ft/in → metres and confirm return within 0.01 m. This catches inverted factors (multiplying where they should divide).</p>

<h2>Additional worked examples</h2>

<h3>Marathon distance</h3>
<p>42.195 km = 42,195 m ÷ 0.3048 = 138,435 ft ≈ 26.219 miles (statute).</p>

<h3>Door height 2100 mm</h3>
<p>2100 mm = 2.1 m = 6.8898 ft = <strong>6 ft 10.7 in</strong> — verify rough opening specs against this before ordering frames.</p>

<h3>Swimming pool length 25 m</h3>
<p>25 ÷ 0.3048 = 82.021 ft — short-course yards pools in the US are 25 yards (22.86 m), not the same as 25 metres; sports planners must not interchange them.</p>

<h2>Centimetres in body height entry</h2>

<p>Medical forms often ask height in cm while gym equipment lists rails in inches. A person 165 cm tall equals 65.0 inches total = 5 ft 5.0 in. Double-entry on both units catches transposition errors before BMI or BMR calculators run downstream.</p>

<h2>Precision for surveyors vs everyday use</h2>

<p>Land surveyors work in metres to millimetre precision with total stations; carpenters on imperial job sites snap lines to the nearest 1/16 inch. Know your audience: quoting 3.962 m is appropriate for structural specs; saying “about 13 feet” suffices for informal furniture placement. The length converter on CalchubNepal keeps full precision internally so you choose display rounding at the end.</p>

<h2>Building a personal conversion cheat sheet</h2>

<p>Print a wallet card with: 1 in = 2.54 cm; 1 ft = 0.3048 m; 1 m = 3.281 ft; 1 km = 0.621 mi. Tape it inside your site notebook. Even experienced estimators occasionally invert divide and multiply when tired—physical reminders beat memory under deadline pressure on noisy construction floors. Review the card once when switching between metric drawings and imperial supplier catalogues so every order arrives in the right unit system.</p>

<h2>References &amp; further reading</h2>

<ul>
<li>Bureau International des Poids et Mesures (BIPM). <em>The International System of Units (SI Brochure)</em> — definition of the metre and derived units.</li>
<li>National Institute of Standards and Technology (NIST). SP 330 / SP 811 — Guide for SI units and conversion factors (inch = 25.4 mm exact).</li>
<li>International Organization for Standardization. ISO 80000-3 — Quantities and units: space and time.</li>
<li>India Bureau of Indian Standards. IS 786 — Methods of measurement of building works (metric practice on site).</li>
<li>General Conference on Weights and Measures (CGPM) — 1959 international agreement on the inch-foot-metre relationship.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>Is 1 inch exactly 2.54 cm?</h3>
<p>Yes, by international definition since 1959. This makes the conversion factor exact, not measured.</p>

<h3>How many feet are in a metre?</h3>
<p>Approximately 3.28084 feet per metre. Use ÷ 0.3048 for precision.</p>

<h3>How do I convert 5 feet 8 inches to cm?</h3>
<p>68 inches × 2.54 = 172.72 cm.</p>

<h3>Why do building plans use mm instead of cm?</h3>
<p>Millimetres avoid decimal commas on drawings and align with modular construction tolerances in metric countries.</p>

<h3>Are US and UK feet the same?</h3>
<p>Both use the international foot of 0.3048 m today. Historical UK imperial measures were harmonized to the same standard.</p>

<h2>Convert lengths instantly</h2>

<p>Enter metres, feet, inches, or millimetres in the <a href="/calculator/length-converter"><strong>CalchubNepal length converter</strong></a> for exact SI-based results—ideal for homework, site measurements, and fitness height fields that require metres.</p>
HTML;
    }
}
