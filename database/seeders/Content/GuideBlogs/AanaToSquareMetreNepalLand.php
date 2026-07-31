<?php

namespace Database\Seeders\Content\GuideBlogs;

/**
 * Long-form SEO guide: aana to square metre — Nepal land units.
 */
class AanaToSquareMetreNepalLand
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Land in Nepal is still discussed in traditional units—ropani, aana, paisa, and daam in the hills and mountains; bigha, kattha, and dhur in much of the Terai—while engineers, architects, and municipal forms increasingly expect square metres. A single mis-converted aana can skew a purchase negotiation, a building footprint check, or a tax assessment worksheet. Understanding the standard conversion chain, where it comes from, and when to trust a survey instead of a rule-of-thumb is essential for buyers, sellers, and anyone planning construction.</p>

<p>This guide explains the hill-system hierarchy, the formulas linking aana to square metres and square feet, worked examples for typical plot sizes, and the caveats that Survey Department mapping and Lalpurja (land title) details introduce. Use the <a href="/calculator/aana-sqm-converter">Aana–Sqm Converter</a> for quick checks, then confirm legally significant measurements with licensed survey professionals.</p>

<h2>Why Nepal uses two measurement languages</h2>

<p>Historical land administration in Nepal developed local units long before metrication. Village records, inheritance partitions, and broker conversations still quote ropani-aana-paisa-daad (often written daam) because those numbers appear on older deeds and family knowledge. Modern building bylaws, bank valuations, and material calculators (concrete, tile, paint) work in metres. You are not choosing one system forever—you are translating between them accurately at the boundary where tradition meets construction math.</p>

<p>The Department of Survey (<a href="https://dos.gov.np" rel="noopener noreferrer" target="_blank">dos.gov.np</a>) maintains cadastral mapping and surveying standards. Published conversion tables used in education and industry align traditional units to metric equivalents so citizens can compare plots on a common scale.</p>

<h2>The hill-system hierarchy</h2>

<p>In the ropani system, area nests like currency denominations:</p>

<ul>
<li><strong>1 ropani</strong> = 16 aana</li>
<li><strong>1 aana</strong> = 4 paisa</li>
<li><strong>1 paisa</strong> = 4 daam (daam)</li>
</ul>

<p>Therefore <strong>1 ropani = 256 daam</strong> (16 × 4 × 4). Partial plots are quoted as composites: "3 ropani 5 aana 2 paisa" rather than a single decimal ropani, though brokers sometimes approximate.</p>

<h2>Standard metric conversions (textbook factors)</h2>

<p>Survey and education references commonly cite:</p>

<ul>
<li><strong>1 ropani ≈ 508.72 m²</strong> (often rounded in tables to 508.7 or 509)</li>
<li><strong>1 aana ≈ 31.80 m²</strong> (508.72 ÷ 16 ≈ 31.795, frequently rounded to 31.796)</li>
<li><strong>1 paisa ≈ 7.95 m²</strong></li>
<li><strong>1 daam ≈ 1.99 m²</strong></li>
</ul>

<p>In square feet, <strong>1 ropani = 5,476 sq ft</strong> is the widely used companion factor, giving <strong>1 aana ≈ 342.25 sq ft</strong> (5,476 ÷ 16).</p>

<h3>Core formulas</h3>

<p><strong>Square metres = Aana × 31.796</strong> (or your table's precise constant)</p>

<p><strong>Aana = Square metres ÷ 31.796</strong></p>

<p><strong>Square metres = Ropani × 508.72</strong></p>

<p>For composite plots, convert each level to a common sub-unit first—often total aana or total square metres—before adding.</p>

<h2>Worked example: 4 aana to square metres</h2>

<ol>
<li>Area in m² = 4 × 31.796 = 127.184 m²</li>
<li>In sq ft: 127.184 ÷ 0.092903 ≈ 1,369 sq ft (or 4 × 342.25 = 1,369 sq ft via the aana–sqft factor)</li>
</ol>

<p>A small urban house lot of "4 aana" therefore sits just under 128 square metres—roughly a 11.3 m × 11.3 m square if the plot were perfectly regular, which real parcels often are not.</p>

<h2>Worked example: 2 ropani 3 aana composite</h2>

<p>Convert everything to aana first:</p>

<ol>
<li>2 ropani = 2 × 16 = 32 aana</li>
<li>Plus 3 aana = 35 aana total</li>
<li>Area = 35 × 31.796 = 1,112.86 m²</li>
</ol>

<p>Alternatively: (2 × 508.72) + (3 × 31.796) = 1,017.44 + 95.388 = 1,112.828 m²—small differences from rounding constants; pick one constant set and stay consistent through a project.</p>

<h2>Worked example: reverse conversion — 500 m² to ropani-aana</h2>

<ol>
<li>Total aana = 500 ÷ 31.796 ≈ 15.72 aana</li>
<li>Whole ropani = 15.72 ÷ 16 = 0 ropani with 15.72 aana remaining—or 0 ropani 15 aana 2 paisa approx. after splitting fractional paisa</li>
<li>More precisely: 15.72 aana = 15 aana + 0.72 × 4 paisa ≈ 15 aana 2 paisa 2 daam</li>
</ol>

<p>Deed wording may round differently; legal descriptions trump back-of-envelope splits.</p>

<h2>Terai units: bigha, kattha, dhur</h2>

<p>In southern plains districts, bigha-kattha-dhur appears on titles. Standard textbook conversions (verify locally) often cite:</p>

<ul>
<li><strong>1 bigha ≈ 6,772.63 m²</strong> (Katmandu Valley vs Terai bigha definitions have historically differed—confirm district practice)</li>
<li><strong>1 kattha</strong> is a fraction of bigha (commonly 1/20 of bigha in many tables)</li>
<li><strong>1 dhur</strong> is a fraction of kattha</li>
</ul>

<p>When a broker mixes Terai and hill units, stop and ask which system the Lalpurja uses. Do not convert bigha with ropani factors.</p>

<h2>When conversion tables differ slightly</h2>

<p>You may see 1 aana quoted as 31.80 m² in one pamphlet and 31.796 in another. The spread is usually rounding, not a different plot. Problems arise when:</p>

<ul>
<li>Someone uses an obsolete local variant</li>
<li>Deed area was rounded decades ago and no longer matches GPS survey</li>
<li>Road widening, river erosion, or consolidation changed physical area without updating paperwork</li>
<li>Irregular quadrilaterals are treated as rectangles for quick mental math</li>
</ul>

<p>For high-value transactions, commission a licensed surveyor to measure on the ground and reconcile with cadastral maps held by the Survey Department.</p>

<h2>Comparing two plots on the market</h2>

<p>Suppose Plot A is listed as 3 ropani 10 aana and Plot B as 450 m². Normalise both to square metres before comparing price per m²:</p>

<ol>
<li>Plot A: (3 × 16 + 10) = 58 aana → 58 × 31.796 ≈ 1,844.17 m²</li>
<li>Plot B: 450 m² directly</li>
<li>If Plot A costs NPR 9 million, price per m² ≈ 4,881; if Plot B costs NPR 2.4 million, price per m² ≈ 5,333—Plot A is slightly cheaper per unit area on these numbers alone (location, road access, and title clarity still dominate).</li>
</ol>

<p>Brokers sometimes quote "4 aana" and "4.5 aana" in negotiation; converting to m² clarifies whether the difference is ~16 m² or negligible in the context of setbacks. Keep a single conversion constant saved in your phone notes so every party in a family land discussion works from the same factor.</p>

<h2>Building footprint and coverage ratio</h2>

<p>Municipal bylaws often cap built area as a percentage of plot area (floor area ratio or ground coverage). If your net plot is 200 m² after converting from 6.28 aana and coverage allows 60%, maximum ground footprint ≈ 120 m²—roughly 10 m × 12 m. Attempting this in aana without converting first leads to errors because coverage rules are published in metric.</p>

<h2>Practical workflow for construction planning</h2>

<ol>
<li><strong>Extract the deed figure</strong> in traditional units from Lalpurja or sale deed.</li>
<li><strong>Convert once</strong> to square metres using consistent constants; write that number at the top of your workbook.</li>
<li><strong>Subtract setbacks and easements</strong> per local municipality bylaws before sizing the building footprint.</li>
<li><strong>Feed net area</strong> into material calculators (brick, cement, tile) that expect metric dimensions.</li>
<li><strong>Do not reconvert back and forth</strong> at every step—rounding errors accumulate.</li>
</ol>

<h2>Common mistakes</h2>

<ul>
<li><strong>Treating aana and ropani as linear units</strong> — they are area units; doubling aana doubles area, not side length.</li>
<li><strong>Adding ropani and aana without normalising</strong> — convert to one sub-unit first.</li>
<li><strong>Using Terai bigha factors on hill ropani deeds</strong>.</li>
<li><strong>Assuming square plots</strong> — side length from area requires sqrt(area); a 4 aana plot is not "4 metres each side."</li>
<li><strong>Ignoring road-access deductions</strong> on regulatory net buildable area.</li>
</ul>

<h2>References &amp; further reading</h2>

<ul>
<li>Department of Survey, Nepal — cadastral standards and mapping services (<a href="https://dos.gov.np" rel="noopener noreferrer" target="_blank">dos.gov.np</a>).</li>
<li>Nepal Bureau of Standards &amp; Metrology — metrication and standard unit promotion.</li>
<li>Common conversion tables in civil engineering curricula (TU/IOM and CTEVT materials) for ropani–metric equivalents.</li>
<li>FAO, <em>Land Tenure and Administration</em> country notes — context on customary units in South Asia (fao.org).</li>
<li>Local municipality building bylaws — setback and floor-area-ratio rules applied to net m².</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>How many square metres is 1 aana?</h3>
<p>Standard references use approximately 31.80 m² (more precisely ~31.796 m² derived from 1 ropani = 508.72 m²).</p>

<h3>How many aana in 1 ropani?</h3>
<p>Sixteen aana equal one ropani in the hill system.</p>

<h3>Is 1 aana the same everywhere in Nepal?</h3>
<p>The textbook chain is nationwide for ropani-system deeds, but always match the unit printed on your title. Terai bigha-system plots use different factors.</p>

<h3>Can I rely on broker quotes alone?</h3>
<p>Use brokers for market colour; rely on Lalpurja, survey sketches, and licensed measurement for legal area.</p>

<h3>Why does my GPS area differ from the deed?</h3>
<p>Deeds may be older rounded figures; boundaries may be irregular; or physical occupation may have shifted. Resolve through survey and legal review.</p>

<h2>Convert with confidence</h2>

<p>Translate aana, ropani composites, and square metres instantly with our <a href="/calculator/aana-sqm-converter">Aana–Sqm Converter</a>, then continue planning in metric for construction and compliance. For purchases and disputes, follow conversion with professional survey and title verification.</p>
HTML;
    }
}
