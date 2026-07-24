<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use Illuminate\Database\Seeder;

/**
 * Seeds the reusable AI prompt templates used by the AI service layer
 * (App\Services\AI). Templates are kept separate from business logic so
 * prompt wording can be tuned by non-developers through the admin panel
 * without a code deploy. Placeholders use the `{{variable}}` syntax
 * consumed by AiPrompt::render().
 *
 * Safe to re-run: prompts are upserted by slug.
 */
class AiPromptSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected const PROMPTS = [
        [
            'name' => 'Explain Result',
            'slug' => 'explain-result',
            'purpose' => 'explain_result',
            'prompt_template' => "You are a helpful assistant embedded in an online calculator tool called {{calculator_title}}.\nA user entered the following inputs: {{inputs}}\nThe calculator produced this result: {{outputs}}\n\nExplain the result in plain, friendly language in 3-4 short sentences. Mention what the numbers mean practically, without repeating the raw formula. Avoid financial, medical or legal advice disclaimers unless relevant.",
            'model' => 'gpt-4o-mini',
            'provider' => 'openai',
            'temperature' => 0.60,
            'max_tokens' => 300,
        ],
        [
            'name' => 'Explain Formula',
            'slug' => 'explain-formula',
            'purpose' => 'explain_formula',
            'prompt_template' => "You are a subject-matter expert writing for a calculator website. Explain, for a non-technical audience, how the \"{{calculator_title}}\" calculator works.\nFormula description: {{formula_description}}\n\nWrite 2-3 short paragraphs covering: (1) what the calculator estimates and why it's useful, (2) the core formula in plain words, (3) one practical tip for getting an accurate result. Keep the tone clear and approachable.",
            'model' => 'gpt-4o-mini',
            'provider' => 'openai',
            'temperature' => 0.50,
            'max_tokens' => 400,
        ],
        [
            'name' => 'Generate FAQ',
            'slug' => 'generate-faq',
            'purpose' => 'generate_faq',
            'prompt_template' => "Generate {{count}} frequently asked questions with concise, accurate answers for the \"{{calculator_title}}\" calculator.\nCalculator description: {{description}}\n\nReturn the result strictly as a JSON array of objects with \"question\" and \"answer\" keys. Answers should be 1-3 sentences, factual, and free of marketing language.",
            'model' => 'gpt-4o-mini',
            'provider' => 'openai',
            'temperature' => 0.70,
            'max_tokens' => 700,
        ],
        [
            'name' => 'Generate Blog Post',
            'slug' => 'generate-blog',
            'purpose' => 'generate_blog',
            'prompt_template' => "You are an SEO content writer for {{site_name}} (calculators + QR tools for Nepal).\n\nAdmin instructions (follow these carefully):\n{{instructions}}\n\nOptional title hint: {{title_hint}}\nPrimary keyword: {{keyword}}\nLanguage: {{language}}\nTone: {{tone}}\nHARD REQUIREMENT — write AT LEAST {{min_words}} words and aim for about {{word_count}} words in the HTML body (count words inside content only).\nAudience: {{audience}}\nCategory: {{category_name}}\nRelated tool / calculator: {{calculator_title}}\n\nRules:\n- Obey admin instructions and write a complete original article.\n- Do NOT wrap sentences or paragraphs in curly braces {}.\n- Do NOT invent illegal, medical, or financial guarantees.\n- Prefer practical Nepal examples when relevant.\n- Content must include: intro, multiple H2/H3 sections with real paragraphs (not one-liners), and a conclusion with a soft CTA to {{site_name}} tools when it fits.\n- Category is only topical context; it must NOT shorten the article.\n\nReturn ONLY valid JSON (no markdown fences, no commentary) with keys in this order:\n1) \"title\"\n2) \"excerpt\" (max 220 chars)\n3) \"tags\" (array of 3-6 short strings)\n4) \"meta_title\" (max 60 chars)\n5) \"meta_description\" (max 160 chars)\n6) \"meta_keywords\" (comma-separated, max 10)\n7) \"content\" LAST — full HTML body using only <p>, <h2>, <h3>, <ul>, <li>, <strong>, <em>, <a>. No <html>/<body>/scripts. Escape quotes inside JSON properly.\n\nThe content value must be long enough to meet the word-count requirement before you stop.",
            'model' => 'gemini-flash-latest',
            'provider' => 'gemini',
            'temperature' => 0.70,
            'max_tokens' => 8192,
        ],
        [
            'name' => 'Generate SEO Meta',
            'slug' => 'generate-seo-meta',
            'purpose' => 'generate_seo_meta',
            'prompt_template' => "Generate SEO metadata for the page \"{{title}}\" ({{url}}).\nPage summary: {{summary}}\n\nReturn strictly as JSON with keys \"meta_title\" (max 60 characters), \"meta_description\" (max 160 characters) and \"meta_keywords\" (comma-separated, max 10 keywords). Titles and descriptions must be compelling, accurate and free of keyword stuffing.",
            'model' => 'gpt-4o-mini',
            'provider' => 'openai',
            'temperature' => 0.40,
            'max_tokens' => 250,
        ],
        [
            'name' => 'Suggest Related Calculators',
            'slug' => 'suggest-related',
            'purpose' => 'suggest_related',
            'prompt_template' => "Given the calculator \"{{calculator_title}}\" in the \"{{category_name}}\" category, and this list of all available calculators: {{available_calculators}}\n\nSuggest the {{count}} most relevant related calculators a user might want to use next. Return strictly as a JSON array of calculator slugs, ordered by relevance, with no additional commentary.",
            'model' => 'gpt-4o-mini',
            'provider' => 'openai',
            'temperature' => 0.30,
            'max_tokens' => 200,
        ],
    ];

    public function run(): void
    {
        foreach (self::PROMPTS as $prompt) {
            AiPrompt::query()->updateOrCreate(
                ['slug' => $prompt['slug']],
                [
                    'name' => $prompt['name'],
                    'purpose' => $prompt['purpose'],
                    'prompt_template' => $prompt['prompt_template'],
                    'model' => $prompt['model'],
                    'provider' => $prompt['provider'],
                    'temperature' => $prompt['temperature'],
                    'max_tokens' => $prompt['max_tokens'],
                    'is_active' => true,
                ]
            );
        }
    }
}
