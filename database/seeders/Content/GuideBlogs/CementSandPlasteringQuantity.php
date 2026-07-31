<?php

namespace Database\Seeders\Content\GuideBlogs;

class CementSandPlasteringQuantity
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Plastering protects masonry, levels uneven surfaces, and finishes walls and ceilings before paint or tile. On site, the most frequent planning question is simple: how much cement and sand do we need for this room? Underestimating delays work; overordering wastes money and clutters storage. Accurate quantity takeoff starts with wall area, plaster thickness, and the mix ratio your specification demands.</p>

<p>This guide explains dry-volume adjustment, standard 1:4 and 1:6 cement–sand mixes used in Nepal and India, step-by-step calculations for a sample room, and alignment with common Indian Standard (IS) plastering practice. Figures assume conventional site mixing with Portland pozzolana or ordinary Portland cement unless your bill of quantities states otherwise.</p>

<p>For quick estimates on active projects, pair these methods with construction calculators on CalchubNepal when available, and always confirm structural drawings and local building codes before procurement.</p>

<h2>Understanding plaster quantity basics</h2>

<p>Plaster volume equals surface area multiplied by thickness. Specifications give thickness in millimetres—typically 12 mm for internal walls, 15–20 mm for externals, and 6 mm for ceiling plaster (single coat). Always measure <strong>net plaster area</strong>: gross wall area minus openings (doors, windows) plus reveal areas if those faces are plastered.</p>

<p><strong>Wet volume = Plaster area (m²) × Thickness (m)</strong></p>

<p>Example conversion: 12 mm = 0.012 m. A wall 4 m × 3 m = 12 m² → wet volume = 12 × 0.012 = <strong>0.144 m³</strong>.</p>

<h2>Dry volume factor</h2>

<p>Sand in mortar contains moisture and voids. When mixed with cement and water, sand occupies less volume than when loose. Contractors therefore multiply wet volume by a <strong>dry volume factor</strong>, commonly <strong>1.33</strong> (equivalent to adding 33% for void filling). Some estimators use 1.30–1.35 depending on sand fineness.</p>

<p><strong>Dry volume = Wet volume × 1.33</strong></p>

<p>Continuing the example: dry volume = 0.144 × 1.33 = <strong>0.191 m³</strong>.</p>

<h2>Cement–sand mix ratios</h2>

<p>Volume proportions are stated by cement : sand (e.g., 1:4 means one part cement to four parts sand by volume). IS 1661 (Code of practice for application of cement and cement-lime plaster finishes) and IS 2250 (Code of practice for preparation and use of masonry mortars) guide mortar proportions for different exposure conditions.</p>

<h3>1:4 mix (richer, often internal or damp areas)</h3>
<p>Total parts = 1 + 4 = 5</p>
<ul>
<li>Cement volume = Dry volume × (1/5)</li>
<li>Sand volume = Dry volume × (4/5)</li>
</ul>

<h3>1:6 mix (economical, common internal plaster)</h3>
<p>Total parts = 7</p>
<ul>
<li>Cement volume = Dry volume × (1/7)</li>
<li>Sand volume = Dry volume × (6/7)</li>
</ul>

<h2>Converting cement volume to bags</h2>

<p>One bag of cement in Nepal and India is conventionally <strong>50 kg</strong>. The volume of one bag is taken as approximately <strong>0.0347 m³</strong> (derived from bulk density ~ 1440 kg/m³ for cement).</p>

<p><strong>Number of bags = Cement volume (m³) ÷ 0.0347</strong></p>

<p>Round up to whole bags; partial bags are not supplied on typical sites.</p>

<h2>Full worked example: one room (1:6 mix, 12 mm internal)</h2>

<h3>Step 1 — Areas</h3>
<p>Room internal dimensions 5 m × 4 m, height 3 m. One door 1 m × 2.1 m, one window 1.2 m × 1.2 m.</p>
<ul>
<li>Gross wall area = 2 × (5 + 4) × 3 = 54 m² (two long + two short walls simplified; adjust for actual perimeter)</li>
<li>Openings = (1 × 2.1) + (1.2 × 1.2) = 2.1 + 1.44 = 3.54 m²</li>
<li>Net plaster area ≈ 54 − 3.54 = <strong>50.46 m²</strong></li>
</ul>

<h3>Step 2 — Wet volume</h3>
<p>50.46 × 0.012 = <strong>0.606 m³</strong></p>

<h3>Step 3 — Dry volume</h3>
<p>0.606 × 1.33 = <strong>0.806 m³</strong></p>

<h3>Step 4 — Material split (1:6)</h3>
<ul>
<li>Cement = 0.806 × (1/7) = <strong>0.115 m³</strong></li>
<li>Sand = 0.806 × (6/7) = <strong>0.691 m³</strong></li>
</ul>

<h3>Step 5 — Bags of cement</h3>
<p>0.115 ÷ 0.0347 = <strong>3.32 bags → order 4 bags</strong></p>

<h3>Step 6 — Sand in cubic metres or trucks</h3>
<p>Order <strong>0.70 m³</strong> sand (allow 5% wastage → ~0.73 m³). Local suppliers may quote in tractor trolleys; know conversion (often ~2.5–3.5 ft³ or m³ per trolley—verify locally).</p>

<h2>External plaster and two-coat systems</h2>

<p>External walls may use 18 mm total in two coats (12 mm rough + 6 mm finish) with a richer mix (1:4) on the base coat. Calculate each coat separately because thickness and mix may differ. Damp-proof environments (bathrooms) sometimes specify 1:3 or admixtures—follow the structural engineer’s schedule, not generic rules alone.</p>

<h2>Water requirement (indicative)</h2>

<p>Water–cement ratio for plaster mortars typically stays near 0.4–0.5 by weight for workability without excessive shrinkage cracking. For site planning, water is rarely the limiting procurement item, but hot weather increases evaporation—IS guidance on curing (keeping plaster moist 7 days) affects labour scheduling more than tanker math.</p>

<h2>Common mistakes on site</h2>

<ul>
<li><strong>Using gross area without deducting openings:</strong> Overorders cement and sand by 5–15%.</li>
<li><strong>Confusing mm with m:</strong> 12 mm entered as 12 m inflates quantities by 1,000×.</li>
<li><strong>Skipping dry volume:</strong> Underorders sand especially.</li>
<li><strong>Wrong mix ratio:</strong> 1:4 vs 1:6 changes cement need by roughly 40%.</li>
<li><strong>Ignoring wastage and spillage:</strong> Add 5–10% on congested sites.</li>
<li><strong>Measuring broken sand volume loosely:</strong> Truck loose volume ≠ compacted mortar volume.</li>
<li><strong>Mixing by shovel count without gauge boxes:</strong> Ratio drift causes weak plaster and rework.</li>
</ul>

<h2>Quality checks aligned with IS practice</h2>

<p>IS 1661 specifies surface preparation, curing period, and thickness tolerances. Sand should be clean, well-graded, and free of silt (test with simple field checks or laboratory sieving per IS 383 for coarse/fine aggregate). Cement should conform to IS 1489 (PPC) or IS 269 (OPC) as specified. Plaster should be cured by sprinkling water to prevent crazing—quantity planning must include labour days for curing, not just bags.</p>

<h2>Ceiling plaster and soffit areas</h2>

<p>Ceiling area equals length × width of each room. A 5 m × 4 m room ceiling = 20 m². At 6 mm thickness (common single-coat ceiling plaster): wet volume = 20 × 0.006 = 0.12 m³; dry = 0.16 m³; cement (1:6) = 0.023 m³ ≈ 0.66 bags. Soffits of beams add strip area—measure each strip length × width and sum. Scaffolding labour affects cost more than marginal cement on ceilings.</p>

<h2>Estimating labour and productivity</h2>

<p>Mason productivity varies 8–15 m² per day per pair ( mason + helper ) for 12 mm internal plaster including mixing and leveling. A 50 m² wall package might need four to six site days before curing. Quantity takeoff without productivity planning leads to idle crews or rushed coats that fail adhesion tests.</p>

<h3>Sample BOQ line items</h3>

<p>A bill of quantities might read: “12 mm cement sand plaster 1:6 on internal brick masonry, finished smooth, including curing—50 m².” Your material estimate (≈4 bags cement, 0.7 m³ sand) attaches to that line; wastage and carriage are separate items. Match units (m² vs m³) when comparing contractor quotes.</p>

<h2>Weather and seasonal adjustments</h2>

<p>Monsoon plastering may require richer mixes, shorter working windows, and protected curing under tarpaulins. Hot dry seasons increase evaporation—mist spraying becomes critical. Neither changes the core volume formula, but wastage factors may rise to 10% on exposed external scaffolding where mortar falls from boards. Plan material accordingly on multi-storey externals.</p>

<h2>References &amp; further reading</h2>

<ul>
<li>Bureau of Indian Standards. IS 1661:1972 — Code of practice for application of cement and cement-lime plaster finishes.</li>
<li>Bureau of Indian Standards. IS 2250:1981 — Code of practice for preparation and use of masonry mortars.</li>
<li>Bureau of Indian Standards. IS 383:2016 — Coarse and fine aggregate for concrete (sand quality).</li>
<li>Bureau of Indian Standards. IS 1489 — Portland pozzolana cement specification (common in regional plaster work).</li>
<li>Nepal Bureau of Standards &amp; Metrology (NBSM) — National building code references and cement marking requirements for Nepal.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>What mix ratio is best for interior walls?</h3>
<p>1:6 cement–sand is widely used for ordinary internal plaster in South Asia. Wet areas or poor masonry may specify 1:4.</p>

<h3>How many bags of cement per 100 sq ft of 12 mm plaster?</h3>
<p>At 1:6, roughly 0.6–0.7 bags per 100 ft² (≈ 9.3 m²)—always recalculate in metric for accuracy because rounding differs.</p>

<h3>Does plaster thickness include multiple coats?</h3>
<p>Yes—total designed thickness. Multi-coat work should list each coat’s thickness in the estimate.</p>

<h3>Can I use crusher dust instead of river sand?</h3>
<p>Only if specifications allow and grading meets IS 383. Excessive fines weaken bond and increase shrinkage.</p>

<h3>Should I add lime?</h3>
<p>Cement-lime mortars improve workability and are referenced in IS 1661 for certain finishes. Follow the project specification; do not alter structural mixes without approval.</p>

<h2>Plan your next plaster package</h2>

<p>Measure net area, pick your mix, and run the arithmetic before the mason arrives. Explore related construction tools on <strong>CalchubNepal</strong> for area, volume, and material helpers so procurement matches the drawing—not guesswork.</p>
HTML;
    }
}
