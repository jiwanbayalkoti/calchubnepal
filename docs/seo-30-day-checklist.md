# CalchubNepal — 30-Day SEO Execution Checklist

**Site:** https://calchubnepal.com  
**Period:** Day 1–30 (repeat monthly with a lighter maintenance cycle)  
**Owner columns:** `DEV` = code/deploy · `ADMIN` = CMS/admin panel · `MANUAL` = research / off-site / accounts

Use this as the operating checklist for the SEO package (keywords, on-page, technical, content, local/links).

---

## Week 0 — Baseline (before Day 1)

| # | Task | Owner | Done |
|---|------|-------|------|
| 0.1 | Confirm live URL, HTTPS lock, preferred host (www vs non-www) | DEV | ☐ |
| 0.2 | Google Search Console property verified for `calchubnepal.com` | MANUAL | ☐ |
| 0.3 | Google Analytics (GA4) live — ID already in site (`G-ZG8HCJW6ET`); confirm Realtime hits | MANUAL + ADMIN | ☐ |
| 0.4 | Submit sitemap: `https://calchubnepal.com/sitemap.xml` in GSC | MANUAL | ☐ |
| 0.5 | Export GSC: Coverage, Pages, Queries (last 28 days) → baseline sheet | MANUAL | ☐ |
| 0.6 | PageSpeed Insights: Home + 1 calculator + 1 blog (mobile) — note LCP/INP/CLS | MANUAL | ☐ |
| 0.7 | Mobile-Friendly / Chrome mobile check on Home, SIP, Pension pages | MANUAL | ☐ |
| 0.8 | Deploy latest: stub engines + SIP SEO + `php artisan calculators:activate-stubs` on live | DEV | ☐ |

**Baseline sheet tabs to create:** Keywords · Page map · Content calendar · Technical issues · Citations/NAP

---

## Days 1–7 — Keyword Research & Mapping

### Target: 5–10 primary keywords this month

| # | Task | Owner | Done |
|---|------|-------|------|
| 1.1 | Pick **8 seed topics** from top calculators (usage/views) + Nepal intent | MANUAL | ☐ |
| 1.2 | For each seed: list 1 short-tail + 2–3 long-tail | MANUAL | ☐ |
| 1.3 | Tag search intent: Informational / Navigational / Transactional / Commercial | MANUAL | ☐ |
| 1.4 | Note KD / competition (Low / Med / High) — free tools OK (GSC, autocomplete, related) | MANUAL | ☐ |
| 1.5 | Map each keyword → **one URL** (no two pages fighting same primary) | MANUAL | ☐ |
| 1.6 | Flag seasonal opportunities (tax season Nepal, Dashain spend, school year, SIP year-end) | MANUAL | ☐ |
| 1.7 | Choose **1 money page** to refresh this month + **5 blog topics** | MANUAL | ☐ |

### Suggested Month-1 keyword map (edit as needed)

| Primary keyword | Intent | Difficulty | Target page |
|-----------------|--------|------------|-------------|
| sip calculator | Commercial | High | `/calculator/sip-calculator` |
| how to use sip calculator | Informational | Med | `/blog/sip-and-compound-interest-for-beginners` |
| pension lump sum vs annuity | Commercial | Med | `/calculator/pension-lump-sum-vs-annuity-calculator` |
| nepal income tax calculator | Transactional | Med | `/calculator/nepal-income-tax-calculator` (or current slug) |
| emi calculator | Commercial | High | `/calculator/emi-calculator` |
| bmi calculator | Commercial | High | `/calculator/bmi-calculator` |
| qr code generator | Commercial | High | `/qr-code-generator` |
| free age calculator | Commercial | Med | `/calculator/age-calculator` |
| compound interest calculator | Commercial | Med | `/calculator/compound-interest-calculator` |
| nepali date converter | Navigational / Utility | Med | date converter calculator URL |

**Rule:** Long-tail blogs support the money calculator; they do not steal the exact primary from the tool page.

---

## Days 5–12 — On-Page Optimization (mapped pages)

For **each mapped money page** (start with SIP + Pension + 1 Nepal tool):

| # | Task | Owner | Done |
|---|------|-------|------|
| 2.1 | Meta title ≤ ~60 chars, primary keyword near front | ADMIN | ☐ |
| 2.2 | Meta description ≤ ~155 chars, benefit + keyword + soft CTA | ADMIN | ☐ |
| 2.3 | Meta keywords (optional CMS field) — 5–8 related terms | ADMIN | ☐ |
| 2.4 | Single H1 = tool name; H2/H3 = How to use, Formula, Example, FAQ | ADMIN / DEV | ☐ |
| 2.5 | Slug check: short, hyphenated, keyword if natural (avoid changing high-traffic URLs without 301) | DEV | ☐ |
| 2.6 | Intro paragraph includes primary keyword once naturally | ADMIN | ☐ |
| 2.7 | Keyword density: readable prose, not stuffing; secondary terms in H2/FAQ | ADMIN | ☐ |
| 2.8 | Internal links: ≥3 relevant (related calculators + 1 blog + category) | ADMIN / DEV | ☐ |
| 2.9 | CTA: Calculate / Try example / Related tool / Blog guide — above fold + after content | ADMIN / DEV | ☐ |
| 2.10 | Images: descriptive filename, alt text, WebP/compression where possible | DEV / ADMIN | ☐ |
| 2.11 | Duplicate check: same topic on blog vs calculator — differentiate titles/intent | MANUAL | ☐ |
| 2.12 | FAQ 8–10 questions (FAQ schema already supported on calculator pages) | ADMIN | ☐ |

### Page refresh this month (package: 1 existing page)

| Page | Action | Owner | Done |
|------|--------|-------|------|
| Prefer: SIP or Pension (if not already refreshed on live) | Expand content, examples, FAQs, meta, internal links | ADMIN + DEV | ☐ |

---

## Days 8–15 — Technical SEO

| # | Task | Owner | Done |
|---|------|-------|------|
| 3.1 | Crawl site (Screaming Frog free / Sitebulb trial / GSC Pages) | MANUAL | ☐ |
| 3.2 | Fix 404s: restore page, or 301 to best match, or soft-404 cleanup | DEV | ☐ |
| 3.3 | Fix redirect chains / loops (especially www ↔ non-www, http → https) | DEV | ☐ |
| 3.4 | Review `robots.txt` — allow public tools; keep `/admin`, `/account`, `/api` disallowed | DEV | ☐ |
| 3.5 | Confirm sitemap includes active calculators + published posts; re-submit in GSC | DEV + MANUAL | ☐ |
| 3.6 | Broken links: footer, related tools, blog CTAs | DEV | ☐ |
| 3.7 | HTTPS/SSL valid; mixed content check | DEV | ☐ |
| 3.8 | Mobile: forms usable, no horizontal scroll on calculator pages | DEV | ☐ |
| 3.9 | Page speed basics: cache headers, image weight, avoid blocking scripts on LCP | DEV | ☐ |
| 3.10 | GSC: set preferred domain; monitor Coverage / Experience weekly | MANUAL | ☐ |
| 3.11 | GA4: key events — `calculate`, `blog_read` (if not already), check acquisition | MANUAL + DEV | ☐ |
| 3.12 | Noindex on login/register/account/search if not already | DEV | ☐ |

**Already in product (verify on live):** dynamic sitemap, robots, canonical host middleware, calculator FAQ JSON-LD, page-view analytics for admin.

---

## Days 10–28 — Content Creation (5 posts)

**Target:** 5 posts × 1,500–2,000 words, each mapped to a long-tail keyword and linking to 1 calculator.

| # | Post working title | Primary long-tail | Links to | Status |
|---|--------------------|-------------------|----------|--------|
| B1 | How to use SIP calculator (examples) | how to use sip calculator | SIP calculator | ☐ outline · ☐ draft · ☐ meta · ☐ publish |
| B2 | Pension lump sum vs monthly annuity | pension lump sum vs annuity explained | Pension calculator | ☐ outline · ☐ draft · ☐ meta · ☐ publish |
| B3 | Nepal income tax basics + calculator guide | nepal income tax calculator guide | Nepal tax calculator | ☐ outline · ☐ draft · ☐ meta · ☐ publish |
| B4 | EMI calculator: how banks compute EMI | how emi is calculated | EMI calculator | ☐ outline · ☐ draft · ☐ meta · ☐ publish |
| B5 | Free QR code for business / Nepal use cases | free qr code generator nepal | QR generator | ☐ outline · ☐ draft · ☐ meta · ☐ publish |

### Per-post checklist (repeat ×5)

| Step | Owner | Done |
|------|-------|------|
| Topic ideation locked to keyword map | MANUAL | ☐ |
| Outline: H2/H3, example, FAQ, CTA | MANUAL | ☐ |
| Draft 1,500–2,000 words (AI draft OK + human edit) | ADMIN / MANUAL | ☐ |
| Meta title + meta description | ADMIN | ☐ |
| Internal links to calculator + 1–2 related posts | ADMIN | ☐ |
| Featured image + alt text | ADMIN | ☐ |
| Publish + request indexing in GSC | MANUAL | ☐ |

**Tools already in admin:** AI blog generator / prompts — use then human-edit for E-E-A-T and Nepal accuracy.

---

## Days 15–30 — Local SEO & Link Building (light package)

| # | Task | Owner | Done |
|---|------|-------|------|
| 4.1 | Google Business Profile — create/claim (category: Software / Internet website; service area Nepal) | MANUAL | ☐ |
| 4.2 | GBP: description with brand + top tools; website link; photos; hours if applicable | MANUAL | ☐ |
| 4.3 | NAP consistency sheet: Name, Address (if any), Phone, Website — identical everywhere | MANUAL | ☐ |
| 4.4 | Directory 1: Google Business (done in 4.1) | MANUAL | ☐ |
| 4.5 | Directory 2: relevant Nepal/tech directory or startup listing | MANUAL | ☐ |
| 4.6 | Directory 3: Yelp / Bing Places / Apple Business (pick 1 that fits) | MANUAL | ☐ |
| 4.7 | Profile links: LinkedIn company, Facebook page, GitHub/org if public — bio + site URL | MANUAL | ☐ |
| 4.8 | 2–3 contextual mentions (guest tip, Quora/Reddit value answer, partner blog) — no spam | MANUAL | ☐ |
| 4.9 | Log every citation URL + date in the Citations sheet | MANUAL | ☐ |

**NAP example (fill real details):**

```
Name: CalchubNepal (or legal brand)
Address: [Office / “Nepal – Online only”]
Phone: [One number only]
Website: https://calchubnepal.com
```

---

## Day 30 — Monthly report & next cycle

| # | Task | Owner | Done |
|---|------|-------|------|
| 5.1 | GSC: clicks, impressions, top queries, average position — vs Week 0 | MANUAL | ☐ |
| 5.2 | GA4: users, sessions, top landing pages, calculate events | MANUAL | ☐ |
| 5.3 | Rank snapshot for the 8–10 target keywords (manual or tool) | MANUAL | ☐ |
| 5.4 | List wins / blockers / pages still thin | MANUAL | ☐ |
| 5.5 | Pick next month’s 5–10 keywords + 5 blog topics + 1 page refresh | MANUAL | ☐ |
| 5.6 | Keep SIP/Pension-style enrichments rolling for next money pages | DEV + ADMIN | ☐ |

---

## Quick owner matrix (package coverage)

| Package item | Feasible? | Primary owner |
|--------------|-----------|---------------|
| 5–10 keywords / month | Yes | MANUAL |
| Short + long-tail + intent + KD | Yes | MANUAL |
| Keyword → page mapping | Yes | MANUAL |
| Seasonal opportunities | Yes | MANUAL |
| Meta title/description | Yes | ADMIN |
| H1–H3 structure | Yes | ADMIN / DEV |
| URL/slug recommendations | Yes | DEV |
| Image SEO | Yes | DEV / ADMIN |
| Internal linking | Yes | ADMIN / DEV |
| Keyword density / CTA | Yes | ADMIN |
| Duplicate content | Yes | MANUAL + DEV |
| Crawl / 404 / redirects | Yes | MANUAL + DEV |
| Sitemap / robots | Yes (built-in) | DEV + MANUAL submit |
| Mobile + basic speed | Yes | DEV + MANUAL test |
| HTTPS | Yes | DEV / host |
| Broken links | Yes | DEV |
| GSC + GA | Yes | MANUAL (+ DEV events) |
| 5 blogs × 1500–2000w | Yes | ADMIN / MANUAL |
| 1 page refresh / month | Yes | ADMIN / DEV |
| GBP + 2–3 directories + NAP | Yes | MANUAL |
| Profile / citation links | Yes | MANUAL |

---

## Live deploy commands (DEV)

```bash
# After pulling SEO/stub work to production
php artisan migrate --force
php artisan calculators:activate-stubs
php artisan calculators:enrich-content   # optional: thin pages only
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Verify:

- https://calchubnepal.com/sitemap.xml  
- https://calchubnepal.com/robots.txt  
- https://calchubnepal.com/calculator/sip-calculator  
- https://calchubnepal.com/calculator/pension-lump-sum-vs-annuity-calculator  

---

## Working rules (avoid wasting effort)

1. **One primary keyword per URL.**  
2. **Long-tail first** for new domain authority; exact “sip calculator” is a long game.  
3. **Never change a ranking URL** without a 301.  
4. **AI drafts must be edited** for Nepal accuracy and disclaimer (finance/health).  
5. **No PBN / spam directories** — package is quality citations, not link farms.  
6. **Measure monthly** — if a page gets impressions but no clicks, rewrite title/description first.

---

## Optional: Month-2 lighter cycle

- Refresh keyword sheet (5–10 new or upgraded)  
- 5 new blogs + 1 page refresh  
- Fix new GSC errors only  
- 1–2 new citations / partnerships  
- Continue enriching next 3 stub/money calculators with SIP-level content  

---

*Document version: 2026-07-30 · Project: calculator_hub / CalchubNepal*
