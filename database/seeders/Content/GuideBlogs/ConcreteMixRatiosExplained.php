<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: concrete mix ratios M15, M20, M25 and volume estimates.
 */
class ConcreteMixRatiosExplained
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Concrete is the backbone of modern construction in Nepal—from raft foundations in the Kathmandu Valley's soft alluvial soils to column-beam frames in Pokhara and slab-on-grade homes in the Terai. Before the mixer truck arrives or your site crew starts hand-mixing, you need two aligned answers: what <em>grade</em> of concrete the structure requires, and how much cement, sand, and coarse aggregate to order. Mix ratios like M15, M20, and M25 are shorthand for strength and proportion, but beginners often confuse the label with bag counts.</p>

<p>In South Asia, IS 456 (Plain and Reinforced Concrete) and Nepal National Building Code (NBC Nepal) provisions guide structural concrete for buildings. Small residential sites still use <strong>nominal mix</strong> proportions by volume when a design mix from a batching plant is unavailable. This guide explains what M15, M20, and M25 mean, how to convert slab or footing dimensions into material quantities, and why the "dry volume" factor appears in every estimator's notebook.</p>

<p>Use the <a href="/calculator/concrete-calculator">Concrete Calculator</a>, <a href="/calculator/cement-calculator">Cement Calculator</a>, and <a href="/calculator/aggregate-calculator">Aggregate Calculator</a> on CalchubNepal after you understand the ratios below—so your bill of quantities matches what the drawing specifies.</p>

<h2>What does M20 (or M15, M25) actually mean?</h2>

<p>The letter <strong>M</strong> stands for mix, and the number is the specified <strong>characteristic compressive strength</strong> in N/mm² (MPa) at 28 days, tested on standard cubes. Thus:</p>

<ul>
<li><strong>M15</strong> → target mean strength class associated with 15 N/mm² characteristic strength</li>
<li><strong>M20</strong> → 20 N/mm² — very common for residential slabs, beams, and columns</li>
<li><strong>M25</strong> → 25 N/mm² — heavier loads, multi-storey work, or engineer-specified elements</li>
</ul>

<p>IS 456 Table 9 lists nominal mix proportions for grades up to M20 under certain conditions; higher grades typically require <strong>design mix</strong> from laboratory trial mixes accounting for local aggregate quality, water-cement ratio, and admixtures. On Nepali residential sites, M20 for slabs and M25 for columns is a frequent engineer instruction—but always read the structural drawing rather than assuming.</p>

<h2>Nominal mix ratios by volume</h2>

<p>When nominal mixing by volume (1 gauge box cement : n gauge boxes sand : n aggregate), these ratios are widely taught and used for site estimates:</p>

<table class="table">
<thead>
<tr><th>Grade</th><th>Cement : Sand : Coarse aggregate</th><th>Typical use (indicative)</th></tr>
</thead>
<tbody>
<tr><td>M15</td><td>1 : 2 : 4</td><td>Lean mixes, mass footings, non-structural fill where specified</td></tr>
<tr><td>M20</td><td>1 : 1.5 : 3</td><td>Residential slabs, beams, stairs, ground-floor columns</td></tr>
<tr><td>M25</td><td>Design mix (often ~1 : 1 : 2 nominal on small sites under supervision)</td><td>Higher load elements; confirm with engineer</td></tr>
</tbody>
</table>

<p>These are <em>volume</em> proportions of dry materials before water is added. Water quantity is controlled separately via the <strong>water-cement ratio (w/c)</strong>, which strongly influences strength and durability. IS 456 specifies maximum w/c for durability exposure classes; adding "a little extra water" for workability without adjusting cement is a leading cause of under-strength cubes on site.</p>

<h2>From dimensions to wet concrete volume</h2>

<p>Calculate the geometric volume of the element in cubic metres (m³):</p>

<p><strong>Wet volume</strong> = Length × Width × Depth (or Height) for prisms</p>

<p>For irregular footings, break into rectangles or use average dimensions. Deduct displaced volume only when another structural element occupies the same space (uncommon in simple estimates). Stairs and sloped ramps need segmented volume calculations—do not flatten them into a single rectangle without adjustment.</p>

<h3>Example: ground-floor slab in Bharatpur</h3>

<p>Slab plan 8 m × 5 m, thickness 0.125 m (125 mm):</p>
<p><strong>Wet volume</strong> = 8 × 5 × 0.125 = <strong>5.0 m³</strong></p>

<p>That is the finished concrete you expect in place before shrinkage—not the loose pile of sand and aggregate on the ground.</p>

<h2>The dry volume factor (why 1.54?)</h2>

<p>Loose sand and aggregate contain voids; when mixed with cement paste and water, they compact into a denser mass. Estimators therefore inflate wet volume to approximate dry material volume needed:</p>

<p><strong>Dry volume</strong> ≈ Wet volume × <strong>1.54</strong></p>

<p>The factor 1.52–1.57 appears in textbooks and CPWD estimates; <strong>1.54</strong> is the most common classroom value in India and Nepal. Some engineers use 1.50 for ready-mix where quality control is tight; hand-mix sites with wet sand may need adjustment. Document whichever factor you use on the estimate sheet.</p>

<p>For the Bharatpur slab: Dry volume ≈ 5.0 × 1.54 = <strong>7.70 m³</strong></p>

<h2>Splitting dry volume into cement, sand, and aggregate (M20 example)</h2>

<p>For M20 nominal ratio <strong>1 : 1.5 : 3</strong>:</p>

<p><strong>Total parts</strong> = 1 + 1.5 + 3 = 5.5</p>

<p><strong>Cement volume</strong> = Dry volume × (1 ÷ 5.5)<br>
<strong>Sand volume</strong> = Dry volume × (1.5 ÷ 5.5)<br>
<strong>Aggregate volume</strong> = Dry volume × (3 ÷ 5.5)</p>

<p>Applying to 7.70 m³ dry:</p>
<ul>
<li>Cement = 7.70 × (1/5.5) ≈ <strong>1.40 m³</strong></li>
<li>Sand = 7.70 × (1.5/5.5) ≈ <strong>2.10 m³</strong></li>
<li>Aggregate = 7.70 × (3/5.5) ≈ <strong>4.20 m³</strong></li>
</ul>

<h3>Converting cement volume to bags</h3>

<p>OPC cement is commonly sold in <strong>50 kg</strong> bags. One bag occupies roughly <strong>0.0347–0.035 m³</strong> (often rounded to 0.035 m³ in site math):</p>

<p><strong>Number of bags</strong> = Cement volume (m³) ÷ 0.035</p>

<p>1.40 ÷ 0.035 = <strong>40 bags</strong> (exact value depends on bulk density; order 42–43 with contingency)</p>

<h2>M15 and M25 quick comparison on 5 m³ wet slab</h2>

<p>Wet volume = 5 m³ → Dry ≈ 7.70 m³</p>

<p><strong>M15 (1:2:4), total parts = 7:</strong><br>
Cement = 7.70/7 = 1.10 m³ → ~31 bags; Sand = 2.20 m³; Aggregate = 4.40 m³</p>

<p><strong>M25 (illustrative nominal 1:1:2), total parts = 4:</strong><br>
Cement = 1.925 m³ → ~55 bags; Sand = 1.925 m³; Aggregate = 3.85 m³</p>

<p>M25 uses more cement per cubic metre—cost rises, but strength and durability under exposure often justify it for structural members. Never downgrade grade without engineer approval.</p>

<h2>Water-cement ratio and workability</h2>

<p>Strength is not only about ratio by volume; the w/c ratio governs paste strength. IS 456 recommends maximum w/c for durability (e.g., 0.55 for mild exposure in many cases, lower for severe). Approximate water per 50 kg bag is often <strong>28–30 litres</strong> for nominal mixes, but sand moisture must be subtracted—wet sand on monsoon sites in Nepal can carry surprising free water. Experienced supervisors "gauge" slump by cone or by feel; for formal work, insist on design mix and slump tests.</p>

<h2>Site practices in Nepal and South Asia</h2>

<p>Urban projects near Kathmandu may use ready-mix concrete (RMC) batched to design mix—a cleaner path for M25 columns. Rural and peri-urban builds often hand-mix on tarpaulins with gauge boxes (ghamela) counted per batch. Bag cement from major brands is ubiquitous; aggregate is commonly 20 mm downgraded stone chips from local quarries, with sand from rivers (check silt content—NBC and IS limits apply). Always align nominal site ratios with what the building permit and structural drawing require; municipal building officials in Nepal increasingly expect NBC-compliant documentation.</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Using wet volume directly as material volume</strong> without the ~1.54 dry factor—under-orders sand and aggregate by a third.</li>
<li><strong>Confusing M20 with "20 bags per m³"</strong> — bag count depends on ratio and element size, not the grade number alone.</li>
<li><strong>Measuring sand wet without adjustment</strong> — leads to high w/c and weak concrete.</li>
<li><strong>Substituting M15 on structural slabs</strong> to save cost — structural risk and code non-compliance.</li>
<li><strong>Ignoring entrapped air in hand-mix</strong> — consider 2–3% extra volume for footings poured without vibration.</li>
<li><strong>Ordering round numbers of bags without checking parts</strong> — always split dry volume by ratio first.</li>
</ul>

<h2>References &amp; further reading</h2>

<ul>
<li>IS 456:2000 — Plain and Reinforced Concrete — Code of Practice (Bureau of Indian Standards).</li>
<li>IS 10262 — Guidelines for Concrete Mix Design Proportioning.</li>
<li>IS 383 — Coarse and Fine Aggregate for Concrete — specifications.</li>
<li>Nepal National Building Code (NBC Nepal) — structural concrete and material standards (Government of Nepal).</li>
<li>CPWD Analysis of Rates / Handbook — nominal mix quantity examples used in public works estimating.</li>
<li>Wikipedia — <a href="https://en.wikipedia.org/wiki/Concrete">Concrete</a> and <a href="https://en.wikipedia.org/wiki/Water%E2%80%93cement_ratio">Water–cement ratio</a>.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>Can I use M20 for a two-storey house column?</h3>
<p>Only if the structural engineer specifies it. Many designs call for M25 or higher in columns and footings while slabs remain M20. The drawing governs, not habit.</p>

<h3>Why do estimators use 1.54 and not 1.50?</h3>
<p>Both appear in practice. 1.54 is a traditional voids allowance in Indian/Nepali estimating manuals; adjust if your employer or consultant standardizes another factor and stay consistent across the project.</p>

<h3>How many bags of cement per m³ of M20 concrete?</h3>
<p>After the dry-volume method, M20 often lands near <strong>7–8 bags per m³ of wet concrete</strong> (rough rule of thumb only). Always calculate from element volume and ratio for the specific pour.</p>

<h3>Is stone dust a substitute for sand?</h3>
<p>Partial replacement may be allowed in design mixes with testing; arbitrary substitution on nominal mixes changes workability and strength. Follow engineer guidance and IS aggregate grading limits.</p>

<h3>Should I add extra cement for "stronger" concrete?</h3>
<p>Ad-hoc cement increases without lowering w/c properly do not reliably improve strength and may cause shrinkage cracking. Use the specified grade and proper curing instead.</p>

<h2>Calculate your pour on CalchubNepal</h2>

<p>Translate slab, beam, or footing dimensions into cement bags, sand, and aggregate using the free <a href="/calculator/concrete-calculator">Concrete Calculator</a> on CalchubNepal. Pair it with the <a href="/calculator/cement-calculator">Cement Calculator</a> and <a href="/calculator/aggregate-calculator">Aggregate Calculator</a> for quick cross-checks. Understanding M15, M20, and M25 ratios puts you in control of the bill of quantities—so your structure meets NBC and IS expectations, and your budget reflects real material needs rather than guesswork.</p>
HTML;
    }
}
