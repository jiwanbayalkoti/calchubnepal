<?php

namespace Database\Seeders\Content\GuideBlogs;

class GpaVsCgpaExplained
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Grade Point Average (GPA) and Cumulative Grade Point Average (CGPA) appear on almost every university transcript, yet students routinely confuse them—especially when converting between percentage marks, letter grades, and four-point scales used for scholarships or graduate applications abroad. Understanding the difference prevents reporting errors on CVs, visa forms, and employer questionnaires.</p>

<p>This guide defines semester GPA versus cumulative CGPA, explains weighted and unweighted systems, shows numerical conversion examples aligned with common South Asian university policies, and highlights mistakes that distort academic standing. Whether you study in Nepal, India, or elsewhere, the principles below apply wherever courses carry credit hours and grade points.</p>

<p>Calculate or verify your figures with the <a href="/calculator/gpa-calculator">CalchubNepal GPA calculator</a>, which supports semester and cumulative averaging with configurable grade scales.</p>

<h2>What is GPA?</h2>

<p><strong>GPA (Grade Point Average)</strong> summarizes academic performance over a defined period—usually one semester or academic year. Each course earns a letter grade or percentage that maps to a grade point (often 0.0–4.0 or 0.0–10.0 depending on institution). GPA is the credit-weighted mean of those grade points.</p>

<p><strong>Formula:</strong></p>

<p><strong>GPA = Σ (grade point × credit hours) ÷ Σ (credit hours)</strong></p>

<p>Only courses in the calculation window count. If you retake a course, institutional policy may replace the old grade or average both attempts—check your registrar’s rules.</p>

<h2>What is CGPA?</h2>

<p><strong>CGPA (Cumulative Grade Point Average)</strong> aggregates all completed semesters (or the entire programme) into one figure. It uses the same weighted formula but spans every credited course from enrolment to the reporting date.</p>

<p><strong>CGPA = Σ (grade point × credit hours for all terms) ÷ Σ (total credit hours earned)</strong></p>

<p>CGPA typically changes slowly because each new semester dilutes prior terms. A single strong semester moves CGPA less than it moves that semester’s GPA.</p>

<h2>GPA vs CGPA: key differences</h2>

<table>
<thead>
<tr><th>Aspect</th><th>GPA</th><th>CGPA</th></tr>
</thead>
<tbody>
<tr><td>Time span</td><td>One term or year</td><td>Entire programme to date</td></tr>
<tr><td>Updates</td><td>Resets each term</td><td>Carries forward all credits</td></tr>
<tr><td>Use cases</td><td>Dean’s list, probation, term honours</td><td>Degree classification, transcripts, employers</td></tr>
<tr><td>Sensitivity</td><td>High to one bad course</td><td>Lower; long-term average</td></tr>
</tbody>
</table>

<h2>Four-point scale example (semester GPA)</h2>

<p>Suppose a semester includes four courses:</p>

<ul>
<li>Mathematics (3 credits) — A (4.0)</li>
<li>Physics (4 credits) — B+ (3.3)</li>
<li>English (3 credits) — A− (3.7)</li>
<li>History (2 credits) — B (3.0)</li>
</ul>

<p>Quality points:</p>
<ul>
<li>Math: 3 × 4.0 = 12.0</li>
<li>Physics: 4 × 3.3 = 13.2</li>
<li>English: 3 × 3.7 = 11.1</li>
<li>History: 2 × 3.0 = 6.0</li>
<li>Total quality points = 42.3; total credits = 12</li>
</ul>

<p><strong>Semester GPA = 42.3 ÷ 12 = 3.525</strong></p>

<h2>Building CGPA across two semesters</h2>

<p>Semester 1: 15 credits, 52.5 quality points → GPA 3.50<br>
Semester 2: 18 credits, 64.8 quality points → GPA 3.60</p>

<p>Cumulative:</p>
<ul>
<li>Total quality points = 52.5 + 64.8 = 117.3</li>
<li>Total credits = 15 + 18 = 33</li>
<li><strong>CGPA = 117.3 ÷ 33 = 3.555</strong></li>
</ul>

<p>Note: CGPA is not the simple average of 3.50 and 3.60 (which would be 3.55 unweighted by credits). Credit weighting matters because semesters with more credits influence CGPA more.</p>

<h2>Ten-point scale (common in India and Nepal)</h2>

<p>Many universities use a 10.0 scale where:</p>

<p><strong>CGPA (10-point) = Σ (Ci × Gi) ÷ Σ Ci</strong></p>

<p>where Ci is credit for course i and Gi is grade point on the 10-point scale.</p>

<p>Example: three courses with credits 4, 3, 3 and grades 8, 9, 7:</p>
<ul>
<li>Points = (4×8) + (3×9) + (3×7) = 32 + 27 + 21 = 80</li>
<li>Credits = 10</li>
<li><strong>CGPA = 8.0</strong></li>
</ul>

<p>Percentage equivalence varies by institution. Some use <strong>Percentage ≈ CGPA × 9.5</strong> (UGC-inspired rule of thumb in India); others publish official conversion tables—never assume without your university’s document.</p>

<h2>Weighted vs unweighted GPA</h2>

<p><strong>Unweighted GPA</strong> caps grade points at standard values (A = 4.0) regardless of course difficulty. <strong>Weighted GPA</strong> adds extra points for honours, AP, or advanced courses (e.g., A in AP = 5.0 on a 5.0 scale). High schools in the United States often report both; universities in Nepal and India typically use unweighted institutional scales on official transcripts.</p>

<h2>Institutional policies that affect CGPA</h2>

<ul>
<li><strong>Grade forgiveness:</strong> Retake replaces prior grade in CGPA.</li>
<li><strong>Audit / pass-fail:</strong> Pass-fail courses may earn credits without grade points.</li>
<li><strong>Failed courses:</strong> Some schools include F (0.0) in CGPA; others exclude after successful retake.</li>
<li><strong>Transfer credits:</strong> Credits from other institutions may count toward graduation but not CGPA.</li>
</ul>

<p>Tribhuvan University, Kathmandu University, and Indian UGC-affiliated colleges each publish specific ordinances—consult the latest academic calendar.</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Averaging semester GPAs without credit weighting:</strong> Produces wrong CGPA when credit loads differ.</li>
<li><strong>Mixing scales:</strong> Combining 4.0 and 10.0 courses without conversion.</li>
<li><strong>Using percentage ÷ 10 as CGPA:</strong> Valid only where the institution defines that mapping.</li>
<li><strong>Ignoring minus grades:</strong> A− is not the same as A on most rubrics.</li>
<li><strong>Omitting failed credits:</strong> Failed courses still count in denominator at many universities until replaced.</li>
<li><strong>Reporting major GPA as CGPA:</strong> Major GPA includes only departmental courses; CGPA includes all.</li>
</ul>

<h2>When employers and graduate schools look at which number</h2>

<p>Recruiters and admissions committees prioritize <strong>CGPA</strong> for overall standing, but may examine <strong>recent semester GPA</strong> for upward trends. A student who struggled in year one but earned 3.8 in final semesters should highlight improvement. Some graduate programmes recalculate GPA using only relevant prerequisite courses.</p>

<h2>Letter-grade to grade-point tables (illustrative 4.0 scale)</h2>

<p>Universities publish official tables; a typical U.S.-style mapping looks like:</p>
<ul>
<li>A / A+ = 4.0</li>
<li>A− = 3.7</li>
<li>B+ = 3.3</li>
<li>B = 3.0</li>
<li>B− = 2.7</li>
<li>C+ = 2.3</li>
<li>C = 2.0</li>
<li>D = 1.0</li>
<li>F = 0.0</li>
</ul>
<p>Nepal and India often use percentage bands mapped to 10-point grades (e.g., 90–100 → 10, 80–89 → 9). Never copy a foreign table onto your transcript without registrar confirmation.</p>

<h2>Probation, honours, and graduation thresholds</h2>

<p>Many programmes define academic standing with CGPA cut-offs: Dean’s List might require semester GPA ≥ 3.5 with full load; probation may trigger below CGPA 2.0. Graduation honours (cum laude equivalents) use final CGPA at degree conferral—retakes in the final semester can still move you across a boundary. Planning retakes requires knowing whether the improved grade replaces the old one in the cumulative calculation.</p>

<h3>International applications and WES evaluation</h3>

<p>Credential evaluators convert local CGPA to a U.S. four-point equivalent using proprietary grade scales—not a single universal formula. Applicants should upload official transcripts and leave conversion to the evaluator rather than self-reporting a converted GPA that admissions offices may recalculate anyway.</p>

<h2>Sample spreadsheet layout for CGPA tracking</h2>

<p>Columns: Course code, title, credits, grade letter, grade point, quality points (credits × points), term. Sum quality points and credits at the bottom each semester, then maintain a running cumulative row. Conditional formatting can flag courses below passing. Exporting this sheet before registration helps students see how many credits at what minimum grade are needed to reach a target CGPA by graduation. Update the cumulative row after every grade release so surprises never appear on the final transcript.</p>

<h2>References &amp; further reading</h2>

<ul>
<li>University Grants Commission (India). Grading system and letter-grading guidelines for higher educational institutions (UGC notifications on 10-point scale).</li>
<li>Tribhuvan University (Nepal). Academic regulations and examination grading ordinances (official TU publications).</li>
<li>National Association of Colleges and Employers (NACE). Guidance on credential evaluation and GPA reporting for U.S. employers.</li>
<li>World Education Services (WES). International grade conversion methodologies for credential evaluation.</li>
<li>European Bologna Process documents on ECTS grading tables (for study-abroad context).</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>Is CGPA the same as overall GPA?</h3>
<p>In most universities yes—CGPA means cumulative GPA across all completed terms. Some schools label the final degree mark “overall GPA” on the diploma.</p>

<h3>Can CGPA exceed 4.0?</h3>
<p>On a standard unweighted 4.0 scale, no. Weighted high-school scales or 10-point university systems use different ceilings.</p>

<h3>How do I convert CGPA to percentage for forms?</h3>
<p>Use your institution’s official formula. Generic multipliers (like × 9.5) are approximations and may be rejected by credential evaluators.</p>

<h3>Do extracurricular activities affect CGPA?</h3>
<p>No. CGPA reflects credited academic courses only, unless a course is formally graded for field work with credits.</p>

<h3>What CGPA is considered good?</h3>
<p>Context matters: 3.5+ on 4.0 or 8.0+ on 10.0 is often competitive for scholarships, but programme norms vary. Compare against your department’s historical placement data.</p>

<h2>Compute your GPA and CGPA</h2>

<p>Add courses, credits, and grades term by term with the <a href="/calculator/gpa-calculator"><strong>CalchubNepal GPA calculator</strong></a>. Export-ready totals help you cross-check official transcripts before submitting applications.</p>
HTML;
    }
}
