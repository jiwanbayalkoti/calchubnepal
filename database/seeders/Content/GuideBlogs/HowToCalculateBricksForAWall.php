<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: brick quantity estimation for walls.
 */
class HowToCalculateBricksForAWall
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Whether you are building a boundary wall in Kathmandu Valley, a load-bearing partition in Pokhara, or a compound wall in Biratnagar, ordering the correct number of bricks is one of the first material decisions that affects both cost and schedule. In Nepal and across South Asia, fired clay bricks remain the dominant masonry unit for residential construction, and suppliers typically sell by the thousand (often quoted as "per 1000 bricks" or "per load"). Underestimating leaves masons idle while you chase another truck; overestimating ties up cash and storage space on a cramped urban plot.</p>

<p>Brick estimation is not guesswork. It follows a clear geometric relationship between wall volume, the effective size of each brick including mortar joints, and a practical wastage allowance for cuts, breakage, and uneven courses. This guide walks through the formulas used on site and in engineering handbooks, with a worked example using dimensions common in Nepal. You will also learn how NBC Nepal (Nepal National Building Code) thinking about masonry thickness aligns with everyday practice, and where nominal brick sizes differ from what you actually measure on the pallet.</p>

<p>After reading, plug your wall dimensions into the free <a href="/calculator/brick-calculator">Brick Calculator</a> on CalchubNepal to cross-check your manual estimate before you place an order with your local kiln or hardware dealer.</p>

<h2>Understanding brick sizes in Nepal and South Asia</h2>

<p>There is no single "standard" brick worldwide. In Nepal, the most commonly encountered modular brick for residential work is approximately <strong>230 mm × 110 mm × 70 mm</strong> (sometimes listed as 9″ × 4.25″ × 2.75″ in imperial units). Older or regional variants may be slightly smaller or thicker. Indian IS codes and Nepal site practice often reference similar nominal dimensions, but always measure a sample from your supplier because kiln shrinkage and chamfering change the count per cubic metre.</p>

<p>Mortar joints typically add <strong>10 mm</strong> (about 3/8″) on bed and perpends in face-brickwork. The "effective" brick footprint in the wall plane therefore becomes roughly 240 mm × 120 mm when joints are included horizontally, and the height per course becomes brick height plus one bed joint. Masons in the Terai and hills use the same arithmetic even when they speak in feet and inches on site—convert everything to one unit system before calculating.</p>

<h3>Nominal versus actual dimensions</h3>

<p>Catalogues quote nominal sizes; pallets deliver actual sizes. A 5 mm difference in length across thousands of bricks changes your order by several hundred units on a large wall. Before estimating, stack ten bricks, measure the overall length including nine joints, and divide by ten to get the true module. This single check prevents the most common ordering error in South Asian masonry jobs.</p>

<h2>Step 1: Measure net wall volume</h2>

<p>Start with the gross envelope of the wall, then subtract openings. All dimensions must use the same unit—metres are preferred for volume in cubic metres (m³), which maps cleanly to material take-offs used in NBC Nepal documentation and IS 1905 (Code of Practice for Brick Masonry) style calculations.</p>

<p><strong>Net wall area (one face)</strong> = (Wall length × Wall height) − Σ(Opening width × Opening height)</p>

<p><strong>Wall volume</strong> = Net wall area × Wall thickness</p>

<p>For a single-leaf 230 mm wall, thickness is 0.23 m. For a 230 mm + 230 mm cavity or composite wall, sum both leaves if you are ordering bricks for both sides. Half-brick partitions (115 mm nominal) use half the thickness in the volume formula.</p>

<h2>Step 2: Bricks per cubic metre (with mortar joints)</h2>

<p>The classic approach divides wall volume by the volume occupied by one brick <em>including its share of mortar</em>. For modular bricks with 10 mm joints:</p>

<p><strong>Effective length</strong> = Brick length + Mortar thickness = 0.23 + 0.01 = 0.24 m (example)<br>
<strong>Effective height</strong> = Brick height + Mortar thickness = 0.07 + 0.01 = 0.08 m<br>
<strong>Effective depth</strong> = Wall thickness (one brick length in stretcher bond for 230 mm wall)</p>

<p><strong>Volume per brick in wall</strong> = Effective length × Effective height × Wall thickness</p>

<p><strong>Raw brick count</strong> = Wall volume ÷ Volume per brick in wall</p>

<p>Alternatively, many site engineers use a <strong>bricks per m²</strong> table for a given thickness. For 230 mm thick walls with standard modular units and 10 mm joints, a widely cited figure is approximately <strong>50–55 bricks per m²</strong> of wall surface—always verify against your measured module.</p>

<h2>Step 3: Add wastage and contingency</h2>

<p>Wastage covers cutting at openings, chipping during transport over rough roads, and odd corners around chajjas (overhangs) and stair landings common in Nepali townhouses. Typical allowances:</p>

<table class="table">
<thead>
<tr><th>Wall type</th><th>Suggested wastage</th></tr>
</thead>
<tbody>
<tr><td>Straight boundary wall, skilled crew</td><td>5%</td></tr>
<tr><td>Multiple openings, reveals, sills</td><td>8–10%</td></tr>
<tr><td>Arches, circular elements, site handling risk</td><td>10–15%</td></tr>
</tbody>
</table>

<p><strong>Order quantity</strong> = Raw brick count × (1 + Wastage fraction)</p>

<h2>Worked example: compound wall in Lalitpur</h2>

<p>Suppose you are building a front boundary wall:</p>
<ul>
<li>Length = 12 m, height = 2.4 m, thickness = 0.23 m</li>
<li>One gate opening: 3 m × 2.1 m = 6.3 m²</li>
<li>Brick module (measured): 230 × 110 × 70 mm with 10 mm joints</li>
</ul>

<p><strong>Gross area</strong> = 12 × 2.4 = 28.8 m²<br>
<strong>Net area</strong> = 28.8 − 6.3 = 22.5 m²<br>
<strong>Wall volume</strong> = 22.5 × 0.23 = 5.175 m³</p>

<p>Using effective dimensions 0.24 m × 0.08 m × 0.23 m per brick slot:</p>
<p>Volume per brick = 0.24 × 0.08 × 0.23 = 0.004416 m³<br>
<strong>Raw count</strong> = 5.175 ÷ 0.004416 ≈ <strong>1,172 bricks</strong></p>

<p>With 8% wastage: 1,172 × 1.08 ≈ <strong>1,266 bricks</strong> → round up to <strong>1,300</strong> or 1.3 thousand for ordering.</p>

<p>Cross-check via bricks/m²: 22.5 m² × 52 bricks/m² ≈ 1,170 raw—consistent with the volume method. Run the same inputs in the <a href="/calculator/brick-calculator">Brick Calculator</a> to confirm before payment.</p>

<h2>Mortar quantity (brief)</h2>

<p>Brick orders often pair with cement and sand for mortar. A rule used on many South Asian sites is roughly <strong>0.02–0.025 m³ mortar per m²</strong> of 230 mm wall for 1:6 cement-sand mix, but mortar volume depends on joint thickness and brick perforations. IS 2250 and NBC Nepal masonry sections give more precise mix proportions; for budgeting, estimate mortar separately once brick count is fixed.</p>

<h2>Common mistakes when estimating bricks</h2>

<ul>
<li><strong>Using catalogue brick size without measuring delivered stock.</strong> Kiln variation is normal; measure ten bricks and average.</li>
<li><strong>Forgetting to subtract openings.</strong> Doors, windows, and vent blocks add up—especially on street-facing façades with multiple grills.</li>
<li><strong>Mixing units.</strong> Converting 9 feet to metres incorrectly is a frequent source of 10–15% error. Stick to one system.</li>
<li><strong>Ignoring half-bricks at ends and reveals.</strong> They still consume material even if the wall area formula treats thickness uniformly.</li>
<li><strong>Skipping wastage on "simple" walls.</strong> Transport losses on hilly roads in Nepal can exceed urban assumptions.</li>
<li><strong>Ordering header and stretcher bonds without adjusting.</strong> Decorative bonds increase cuts; add extra wastage.</li>
</ul>

<h2>References &amp; further reading</h2>

<ul>
<li>Nepal National Building Code (NBC Nepal) — masonry and structural provisions for brickwork in Nepal (Department of Urban Development and Building Construction, Government of Nepal).</li>
<li>IS 1905:1987 — Code of Practice for Brick Masonry (Bureau of Indian Standards) — joint thickness, bonding, and workmanship.</li>
<li>IS 1077 — Common Burnt Clay Building Bricks — dimensional and strength classification.</li>
<li>IS 2250 — Code of Practice for Preparation and Use of Masonry Mortars.</li>
<li>Wikipedia — <a href="https://en.wikipedia.org/wiki/Brick">Brick</a> — historical context and international size standards.</li>
<li>CPWD Handbook on Brick Masonry (Government of India) — practical quantity examples used across South Asia.</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>How many bricks are in 1 cubic metre of wall?</h3>
<p>For standard ~230 mm thick masonry with modular bricks and 10 mm joints, expect roughly 400–450 bricks per m³ of wall volume, depending on exact module. Always calculate from your measured brick, not a generic table.</p>

<h3>Should I count broken bricks in the order?</h3>
<p>Yes. Reputable suppliers allow a small percentage of breakage in transit; still add 5–8% wastage in your estimate so the mason is not short on the final course.</p>

<h3>Does single brick (115 mm) half-width partition use half the bricks?</h3>
<p>Approximately, for the same plan area, a half-brick wall uses about half the volume of a 230 mm wall—but verify bond pattern and pier requirements at corners and junctions with main walls.</p>

<h3>What if my bricks are 9″ × 4″ × 3″ instead of metric modular?</h3>
<p>Convert all dimensions to metres (or all to inches), recompute effective size with mortar, and rerun the volume formula. Do not substitute someone else's "bricks per m²" figure from a different size class.</p>

<h3>Can I use this method for block masonry (AAC or concrete blocks)?</h3>
<p>The geometry is identical; only module dimensions and wastage rates change. Use block length × height × wall thickness with the appropriate joint gap, or switch to a dedicated block calculator if available.</p>

<h2>Next step: verify with CalchubNepal</h2>

<p>Manual take-offs build intuition; calculators reduce arithmetic slips. Enter wall length, height, thickness, brick dimensions, joint size, and wastage percentage in the free <a href="/calculator/brick-calculator">Brick Calculator</a> on CalchubNepal. Compare the result with your spreadsheet, adjust for site-specific openings, and only then confirm quantity with your supplier. Accurate brick counts keep masons working, reduce costly mid-project deliveries, and leave budget room for finishes that actually show—plaster, paint, and weatherproofing on your new wall.</p>
HTML;
    }
}
