<?php

namespace Database\Seeders\Content\GuideBlogs;

class HowToCalculateAgeAccurately
{
    public static function html(): string
    {
        return <<<'HTML'
<p>Age seems straightforward until a form asks for “age as on 1 July 2026,” “completed years,” or “age in years, months, and days” for a school admission cutoff. Counting birthdays manually across leap years and month-end dates invites errors. Software handles the calendar rules—but only if you supply correct dates and understand what “age” means in your context.</p>

<p>This guide explains chronological age calculation, leap-year handling, completed vs running age, timezone pitfalls, and worked examples you can verify by hand. It also covers special cases such as visa applications, insurance premiums, and eligibility windows where off-by-one-day mistakes disqualify applicants.</p>

<p>Use the <a href="/calculator/age-calculator">CalchubNepal age calculator</a> to compute exact age between any two dates, then read below to interpret results for official forms.</p>

<h2>What “age” means</h2>

<p><strong>Chronological age</strong> is the elapsed time from date of birth to a reference date (today or a specified “as on” date). It is usually expressed as:</p>

<ul>
<li>Years only (completed years)</li>
<li>Years and months</li>
<li>Years, months, and days (full precision)</li>
</ul>

<p>Legal and institutional rules define which format matters. A cricket under-19 cutoff may require “under 19 on 1 September”—meaning you must be 18 years old on that date, not “19th birthday not yet reached” interpreted loosely.</p>

<h2>Core calculation method</h2>

<p>Age is computed by subtracting birth date from reference date with borrowing across day and month boundaries—similar to manual subtraction in base-12 months and variable month lengths.</p>

<p><strong>Algorithm (conceptual):</strong></p>
<ol>
<li>Start with reference year − birth year.</li>
<li>If reference month &lt; birth month, subtract one year.</li>
<li>Else if same month but reference day &lt; birth day, subtract one year.</li>
<li>Compute remaining months and days by adjusting borrowed months (28–31 days depending on month).</li>
</ol>

<p>Equivalently:</p>

<p><strong>Age = Reference date − Date of birth</strong> (calendar-aware interval)</p>

<h2>Worked example 1: simple case</h2>

<p>Born <strong>15 March 2010</strong>, reference <strong>20 July 2026</strong>.</p>
<ul>
<li>Years: 2026 − 2010 = 16, but July &gt; March and 20 &gt; 15 → no borrow.</li>
<li><strong>Age = 16 years, 4 months, 5 days</strong> (March 15 to July 20: Apr, May, Jun, Jul partial).</li>
</ul>

<h2>Worked example 2: birthday not yet reached this year</h2>

<p>Born <strong>25 November 2000</strong>, reference <strong>10 April 2026</strong>.</p>
<ul>
<li>2026 − 2000 = 26, but April &lt; November → subtract 1 year → 25 years.</li>
<li>Months from Nov 25 to Apr 25 next cycle: Nov→Dec→Jan→Feb→Mar→Apr = 4 months and 16 days (Apr 10 − Nov 25 spanning year boundary).</li>
<li>Precise tools yield <strong>25 years, 4 months, 16 days</strong>.</li>
</ul>

<h2>Worked example 3: end-of-month births</h2>

<p>Born <strong>31 January 2019</strong>, reference <strong>28 February 2026</strong> (non-leap year).</p>
<p>Many systems treat “last day of month” births so that in months without 31 days, the anniversary falls on the last day—28 Feb counts as anniversary in February. Age calculators may differ; institutions sometimes publish explicit rules. For strict forms, cite the government portal’s computed age.</p>

<h2>Leap years (29 February)</h2>

<p>Leap years occur when the year is divisible by 4, except centuries unless divisible by 400. Someone born on <strong>29 February 2012</strong> has ambiguous “birthday” in non-leap years:</p>

<ul>
<li>Some jurisdictions use <strong>28 February</strong>.</li>
<li>Others use <strong>1 March</strong>.</li>
<li>Software often picks 28 Feb or maps to March 1 consistently—check your form’s guidance.</li>
</ul>

<p>Example: reference <strong>1 March 2025</strong> (non-leap). Born 29 Feb 2012 → age commonly reported as <strong>13 years</strong> (anniversary observed 28 Feb 2025).</p>

<h2>Completed age vs running age</h2>

<p><strong>Running age</strong> (common in informal South Asian speech) counts the year you are living in—a person born in 2010 might be called “17th running” before the 17th birthday. <strong>Completed age</strong> (used in most legal/medical contexts) counts full elapsed years since birth date. Medical dosing and WHO growth charts use completed years (and fractional for infants).</p>

<p>Always match the definition on the form. School admission in many districts uses “completed years as on 1 Chaitra/Baisakh” with official calendars.</p>

<h2>Age “as on” a future or past date</h2>

<p>Visa and exam forms state: “Age as on 01-Jan-2027.” Calculate using that exact reference, not today’s date. Retirement eligibility similarly uses pension cutoff dates published by employers or social security boards.</p>

<h2>Time zones and birth time</h2>

<p>Plain date-of-birth age ignores clock time. If born <strong>11:55 pm on 31 Dec</strong> in one timezone and reference is UTC midnight, edge cases appear only when systems convert timezones. For standard date-only fields, use civil date at place of birth without UTC shifting unless the application explicitly stores timestamps.</p>

<h2>Decimal age (research and pediatrics)</h2>

<p>Researchers sometimes express age as decimal years:</p>

<p><strong>Decimal age ≈ (reference − birth) in days ÷ 365.25</strong></p>

<p>Example: exactly 3,652 days ≈ 10.00 years using 365.25-day average. Clinical growth software uses precise day counts rather than rough decimals.</p>

<h2>Common mistakes</h2>

<ul>
<li><strong>Subtracting years only:</strong> Ignoring whether birthday passed this year.</li>
<li><strong>Using 365 days always:</strong> Accumulates error over decades; use calendar logic.</li>
<li><strong>Wrong date format:</strong> DD/MM/YYYY vs MM/DD/YYYY swaps day and month.</li>
<li><strong>Inclusive counting:</strong> Some traditions count birth day as day 1; most Western age systems do not add an extra day.</li>
<li><strong>Timezone on ISO timestamps:</strong> Shifts birth date backward/forward.</li>
<li><strong>Excel DATEDIF bugs:</strong> Legacy functions need consistent “ymd” units; verify against known cases.</li>
</ul>

<h2>Verification checklist</h2>

<ol>
<li>Confirm reference date required by the authority.</li>
<li>Enter DOB in unambiguous ISO form (YYYY-MM-DD) internally.</li>
<li>Check if completed or running age is requested.</li>
<li>For Feb 29 births, read leap-year policy footnotes.</li>
<li>Cross-check with a second tool or hand subtraction of years/months/days.</li>
</ol>

<h2>Age in legal contexts: majority and consent</h2>

<p>Most jurisdictions fix legal majority at 18 completed years, but voting, marriage, contract, and alcohol ages differ by country and sometimes by province. Nepal’s Muluki Civil Code and comparable statutes define ages for marriage and inheritance—not always identical to “age for school admission.” Always cite the statute or circular, not a generic calculator footnote.</p>

<h2>Programming age correctly (for developers)</h2>

<p>Developers should use calendar-aware date libraries (PHP <code>DateTime::diff</code>, JavaScript Temporal or mature libraries) rather than dividing millisecond timestamps by (365 × 24 × 3600 × 1000). Store dates in UTC only when time-of-day matters; date-of-birth fields should remain date-only without timezone conversion. Unit tests must cover Feb 28/29, month-end births, and “as on” reference dates in the past.</p>

<h2>Age bands for sports and competitions</h2>

<p>Youth sports categories (U-15, U-17, U-19) use registration cut-off dates published each season. A player born one day after the cutoff plays in the younger category for the entire season. Always read the governing body’s circular—football associations and school leagues publish Nepali and English notices with the exact “as on” date and whether completed years or year-of-birth rules apply.</p>

<h2>References &amp; further reading</h2>

<ul>
<li>International Organization for Standardization. ISO 8601 — Date and time format standards for unambiguous exchange.</li>
<li>United States Department of State — Visa reciprocity and age computation guidance (country-specific “age as on” rules).</li>
<li>World Health Organization. Child growth standards: age expressed in days/weeks for infants under 24 months.</li>
<li>HM Passport Office / UK GOV.UK — Examples of date-of-birth evidence and legal age of majority (18 years).</li>
<li>Nepal Government school admission circulars — Illustrative “completed age as on” cutoff dates (consult current academic year notice).</li>
</ul>

<h2>Frequently asked questions</h2>

<h3>How do I calculate age in Excel or Google Sheets?</h3>
<p>Use DATEDIF(birth, reference, "Y") for years plus additional DATEDIF units for months/days, or DATE_DIFF functions in modern spreadsheets—but validate against known examples because DATEDIF has quirks.</p>

<h3>Does age change at midnight on my birthday?</h3>
<p>Yes for date-only civil age: you complete another year at the start of your birth date each year (local civil calendar).</p>

<h3>How is gestational age different?</h3>
<p>Gestational age in obstetrics counts from the first day of the last menstrual period, not birth date—do not mix with chronological age calculators.</p>

<h3>Can age calculators handle BC dates or historical calendars?</h3>
<p>Most consumer tools use Gregorian calendar only. Historical Julian/Gregorian transitions require specialized libraries.</p>

<h3>Why do two websites differ by one day?</h3>
<p>Leap-day observation, timezone normalization, or inclusive vs exclusive day counting. Align with the official source for legal submissions.</p>

<h2>Calculate exact age instantly</h2>

<p>Enter date of birth and reference date in the <a href="/calculator/age-calculator"><strong>CalchubNepal age calculator</strong></a> for years, months, and days in one click—ideal for forms, eligibility checks, and planning milestone birthdays.</p>
HTML;
    }
}
