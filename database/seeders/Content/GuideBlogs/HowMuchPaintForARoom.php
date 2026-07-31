<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: paint coverage estimation for rooms.
 */
class HowMuchPaintForARoom
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Walk into any paint shop in Kathmandu, Lalitpur, or Delhi and the seller will ask: "Kitna area?"—how much area? If you answer with room length and width but forget ceiling height, deduct the wrong number of doors, or ignore a second coat, you either haul home too many tins or run out halfway through the second wall. Paint is sold by the litre (or gallon in some markets), yet homeowners think in "one room" or "one flat." Bridging that gap with a simple area-and-coverage calculation saves money and weekend time.</p>

<p>In Nepal and South Asia, interior finishes often use acrylic emulsion on cement plaster (with putty/skimming on newer urban flats), distemper or lime wash in budget rural renovations, and enamel for doors and windows. Coverage varies by product porosity, application method (roller vs brush), and surface texture. This guide gives formulas, Nepal-relevant surface notes, worked examples in square metres, and the wastage and coat assumptions painters actually use on site.</p>

<p>After estimating manually, confirm litres needed with the free <a href="/calculator/paint-calculator">Paint Calculator</a> on CalchubNepal—especially when multiple rooms share one purchase order.</p>

<h2>Core formula: from room to litres</h2>

<p>For rectangular rooms with four walls of equal height:</p>

<p><strong>Total wall area</strong> = 2 × (Length + Width) × Height</p>

<p>Subtract openings (doors, windows, built-in cupboards counted as unpainted if you will not coat them):</p>

<p><strong>Net wall area</strong> = Total wall area − Σ(Opening width × Opening height)</p>

<p>Add ceiling if you paint it:</p>

<p><strong>Ceiling area</strong> = Length × Width</p>

<p><strong>Paintable area</strong> = Net wall area + Ceiling area (if included)</p>

<p>Coverage depends on the product label, usually expressed as m² per litre <em>per coat</em>:</p>

<p><strong>Litres required</strong> = (Paintable area × Number of coats) ÷ Coverage per litre per coat</p>

<p>Round up to the next standard tin size (1 L, 4 L, 10 L, 20 L are common in Nepal and India).</p>

<h2>Typical coverage rates (verify on your tin)</h2>

<table class="table">
<thead>
<tr><th>Surface / product type</th><th>Indicative coverage (smooth surface, per coat)</th></tr>
</thead>
<tbody>
<tr><td>Quality interior emulsion on puttied plaster</td><td>10–12 m²/L</td></tr>
<tr><td>Standard emulsion on average plaster</td><td>8–10 m²/L</td></tr>
<tr><td>Textured or rough plaster</td><td>6–8 m²/L</td></tr>
<tr><td>Primer / sealer on new plaster</td><td>10–14 m²/L (product-specific)</td></tr>
<tr><td>Exterior weather-coat</td><td>8–11 m²/L</td></tr>
</tbody>
</table>

<p>Manufacturers such as Asian Paints, Berger, Kansai Nerolac, and local Nepali distributors print coverage on the label—always prefer that number over generic tables when estimating a specific SKU.</p>

<h2>How many coats?</h2>

<p>Coats dominate litre count. Common South Asian interior practice:</p>

<ul>
<li><strong>New plaster after putty:</strong> 1 primer coat + 2 finish coats of emulsion</li>
<li><strong>Repaint same colour, good surface:</strong> 1–2 finish coats after light sanding</li>
<li><strong>Dark to light colour change:</strong> 1 primer + 2–3 finish coats, or tinted undercoat</li>
<li><strong>Moisture-prone bathrooms:</strong> specialized primer + mould-resistant topcoats—coverage may differ</li>
</ul>

<p>Multiply wall area by the total number of finish coats; count primer separately if its coverage differs.</p>

<h2>Worked example: bedroom in a Kathmandu apartment</h2>

<p>Room dimensions: 4.0 m × 3.5 m, height 2.8 m. One door 0.9 m × 2.1 m, one window 1.2 m × 1.2 m. Paint walls and ceiling with interior emulsion; assume label coverage 11 m²/L per coat; 2 finish coats after primer (primer calculated separately).</p>

<p><strong>Wall perimeter run</strong> = 2 × (4.0 + 3.5) = 15 m<br>
<strong>Gross wall area</strong> = 15 × 2.8 = 42.0 m²<br>
<strong>Openings</strong> = (0.9 × 2.1) + (1.2 × 1.2) = 1.89 + 1.44 = 3.33 m²<br>
<strong>Net walls</strong> = 42.0 − 3.33 = 38.67 m²<br>
<strong>Ceiling</strong> = 4.0 × 3.5 = 14.0 m²<br>
<strong>Total emulsion area</strong> = 38.67 + 14.0 = 52.67 m²</p>

<p><strong>Finish emulsion</strong> = (52.67 × 2 coats) ÷ 11 = 105.34 ÷ 11 ≈ <strong>9.58 L</strong> → buy <strong>10 L</strong> or one 10 L tin</p>

<p>If primer covers 12 m²/L in one coat: 52.67 ÷ 12 ≈ 4.4 L → one 4 L primer tin. Doors and windows in enamel are estimated separately (see below).</p>

<h2>Doors, windows, and trim</h2>

<p>Do not use wall emulsion coverage for teak doors or metal grills. Estimate each item's surface area or use rules of thumb:</p>

<ul>
<li>Standard flush door (both sides + edges): roughly 3.5–4.5 m² total paintable depending on size</li>
<li>Window woodwork: measure frame perimeter × width of face + sill</li>
<li>Skirting (dado): run length × height of board</li>
</ul>

<p>Enamel often covers fewer m² per litre than emulsion but uses thinner films—read the enamel label separately.</p>

<h2>Nepal and South Asia surface considerations</h2>

<p>Many valley homes use cement-sand plaster with acrylic putty before emulsion; hill homes may have older lime plaster or direct distemper. Highly porous new plaster can "drink" first coats—primer is not optional if you want uniform colour. Monsoon humidity lengthens drying time between coats; rushing causes runs and poor adhesion. Exterior faces in Bharatpur or Janakpur need UV- and rain-resistant exterior emulsions or elastomeric coatings; coverage and dilution ratios differ from interior products. Budget projects using white cement wash or chuna have entirely different math—often quoted per sack, not per litre.</p>

<h2>Wastage and practical buying</h2>

<p>Add <strong>5–10%</strong> for roller tray residue, touch-ups, and future repairs. If one 10 L tin leaves you slightly short, opening a second large tin for half a wall is wasteful—sometimes two 4 L + one 1 L beats one 10 L depending on store pricing. Keep a labelled half-litre for chip repairs after furniture moves.</p>

<h3>Multi-room projects and batch consistency</h3>

<p>When painting an entire flat in Baneshwor or a row house in Butwal, calculate each room separately, then sum litre totals before rounding to tin sizes. Paint colour can vary slightly between manufacturing batches even under the same shade code—buy enough from one batch for all connected visible areas (especially open-plan living-dining). If your total is 38 L across four rooms, two 20 L tins from the same batch beat five separate 4 L purchases made weeks apart. Note batch numbers on your receipt for future touch-ups after tenant turnover or wall damage.</p>

<h2>Tools and application method affect usage</h2>

<p>Rollers with thick nap hold more paint on textured surfaces, increasing consumption compared to smooth foam rollers on puttied walls. Spraying (uncommon in residential Nepal but used on commercial ceilings) changes coverage radically and adds overspray waste—factor 15–20% extra if spraying. Cutting in edges with brushes before rolling is standard; the brush work does not change area math but affects time and whether you need helper labour on high stairwell walls common in multi-storey RCC homes.</p>

<p><strong>Adjusted litres</strong> = Calculated litres × 1.05 (minimum)</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Using floor area instead of wall area</strong> — a 12 m² floor room can have 40+ m² of walls.</li>
<li><strong>Forgetting the ceiling</strong> — especially in rooms with tall 3 m ceilings common in newer Nepali concrete frames.</li>
<li><strong>Applying one-coat coverage math when you need two</strong> — doubles error instantly.</li>
<li><strong>Ignoring texture</strong> — popcorn or roughcast exteriors can halve effective coverage.</li>
<li><strong>Thinning beyond label instructions</strong> — stretches litres but reduces hide and durability.</li>
<li><strong>Mixing brands in the same coat</strong> — coverage and colour match suffer; estimate each system separately.</li>
</ul>

<h2>References &amp; further reading</h2>

<ul>
<li>IS 5410 / relevant BIS guides for paint and varnish terminology (Bureau of Indian Standards).</li>
<li>NBC Nepal — finishing and maintenance sections for interior/exterior treatment of buildings.</li>
<li>Manufacturer technical data sheets (Asian Paints, Berger, etc.) — coverage and dilution on real products sold in Nepal and India.</li>
<li>Wikipedia — <a href="https://en.wikipedia.org/wiki/Paint">Paint</a> — components, types, and application basics.</li>
<li>CPWD specifications for painting work — area measurement methods in public works contracts (Government of India reference used across South Asia).</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>How much paint for a 10×12 ft room?</h3>
<p>Convert to metres (≈ 3.05 m × 3.66 m), apply wall and ceiling formulas with your height and openings, then multiply by coats and divide by label coverage. A typical 3 m ceiling room often needs 8–12 L of emulsion for two wall coats alone.</p>

<h3>Should I deduct doors and windows completely?</h3>
<p>Yes for flat walls if frames are painted with another product. If you emulsion the reveals lightly, add those narrow strips back or keep a small wastage buffer.</p>

<h3>Does dark paint need more litres?</h3>
<p>Deep colours often need extra coats for opacity—model 3 finish coats or a grey primer undercoat instead of 2.</p>

<h3>Can I use the same calculator for exterior walls?</h3>
<p>Same area math; change coverage to the exterior product rate and account for texture and scaffolding access waste on multi-storey buildings.</p>

<h3>How long between coats in humid weather?</h3>
<p>Follow tin instructions; in monsoon, 24–48 hours between emulsion coats is common. Under-drying causes blistering—schedule accordingly.</p>

<h2>Estimate before you buy on CalchubNepal</h2>

<p>Enter room length, width, height, door and window sizes, number of coats, and coverage rate in the free <a href="/calculator/paint-calculator">Paint Calculator</a> on CalchubNepal. You will get litre totals for walls (and ceiling if selected) so your shop list matches the maths—not a vague "two buckets." Accurate paint estimates reduce waste, keep colours consistent within one batch, and leave your renovation budget for hardware and fixtures that matter.</p>
HTML;
    }
}
