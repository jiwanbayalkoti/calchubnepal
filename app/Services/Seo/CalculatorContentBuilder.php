<?php

namespace App\Services\Seo;

/**
 * Builds unique, AdSense-safe SEO copy for calculators that lack hand-written meta.
 * Content is derived from the tool's title, category, formula key and input fields
 * so pages are not thin duplicate templates.
 */
class CalculatorContentBuilder
{
    /**
     * @param  array<int, array<string, mixed>>  $schema
     * @return array{
     *     title: string,
     *     short_description: string,
     *     description: string,
     *     formula_description: string,
     *     meta_title: string,
     *     meta_description: string,
     *     faqs: array<int, array{0: string, 1: string}>
     * }
     */
    public function build(string $formulaKey, string $title, ?string $categoryName = null, array $schema = []): array
    {
        $topic = $this->humanize($formulaKey);
        $category = $categoryName ?: 'general';
        $fields = $this->fieldLabels($schema);
        $fieldList = $fields !== []
            ? implode(', ', array_slice($fields, 0, 6))
            : 'your values';

        $short = "Free online {$title} for {$category}. Enter {$fieldList} to get an instant, transparent result you can trust.";

        $description = "The {$title} helps you work out accurate results for {$topic} without spreadsheets or guesswork. "
            ."It belongs to our {$category} tools and is designed for students, professionals and everyday users who need a clear answer fast. "
            ."Provide {$fieldList}, then review the primary result and any breakdown shown on the page. "
            ."Use the worked example as a starting point, adjust the inputs to match your situation, and double-check critical decisions with a qualified professional when the stakes are high. "
            ."AI Calculator Hub keeps the underlying approach transparent so you can understand how the number was produced, not just what it is.";

        $formula = "This {$title} applies a standard {$topic} approach using the inputs you enter ({$fieldList}). "
            .'Intermediate steps may be shown in the results breakdown. Always confirm that units and assumptions match your use case before relying on the output.';

        $faqs = [
            [
                "How do I use the {$title}?",
                "Enter {$fieldList} in the form above, then click Calculate. The result updates instantly. You can reset the form or try the sample example to learn the expected inputs.",
            ],
            [
                "Is the {$title} free?",
                'Yes. You can use this calculator on AI Calculator Hub without paying. Creating a free account lets you save results and favorites for later.',
            ],
            [
                "How accurate is the {$title}?",
                "Results follow commonly used {$topic} methods for {$category}. Accuracy depends on correct inputs and units. For legal, medical, structural or financial decisions, verify with a qualified professional.",
            ],
        ];

        return [
            'title' => $title,
            'short_description' => $short,
            'description' => $description,
            'formula_description' => $formula,
            'meta_title' => "{$title} — Free Online Tool | AI Calculator Hub",
            'meta_description' => mb_substr("Use the free {$title} to calculate {$topic}. Enter {$fieldList} for instant results with a clear explanation.", 0, 160),
            'faqs' => $faqs,
        ];
    }

    public function isThin(?string $description): bool
    {
        if (! filled($description)) {
            return true;
        }

        return str_contains($description, 'provides instant, accurate results based on the values you enter')
            || str_contains($description, 'Try it above with your own numbers');
    }

    protected function humanize(string $formulaKey): string
    {
        $base = str_replace(['_calculator', '_converter', '_generator'], '', $formulaKey);

        return str_replace('_', ' ', trim($base));
    }

    /**
     * @param  array<int, array<string, mixed>>  $schema
     * @return array<int, string>
     */
    protected function fieldLabels(array $schema): array
    {
        $labels = [];

        foreach ($schema as $field) {
            $label = $field['label'] ?? $field['name'] ?? null;
            if (is_string($label) && $label !== '') {
                $labels[] = $label;
            }
        }

        return $labels;
    }
}
